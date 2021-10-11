<?php

namespace App\Jobs;

use App\Common\Helpers\TargetHelper;
use App\Common\Managers\Redis\TargetManager;
use App\Http\Controllers\v4\ModulazioneUsciteController;
use App\Mail\NotificaTargetMailable;
use App\Models\Targets\TT_NotificaTargetModel;
use App\Models\Targets\TT_StoricoTargetModel;
use App\Models\TT_ComponenteModel;
use App\Models\TT_FlottaModel;
use App\Models\TT_ServizioModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class CheckTargetJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Redis key to latest positions
     * @var string
     */
    protected $latest_key;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $latest_key) {
        $this->latest_key = $latest_key;
        $this->onQueue('targets');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        try {

            //? Check quali delle latest da notificare

            /**
             * @var Collection
             */
            $temp_pos = collect(json_decode(Redis::get($this->latest_key)) ?? []);

            /**
             * Partition keys estratte
             * @var string[]
             */
            $temp_units = $temp_pos->pluck('PartitionKey');

            // Prendo i servizi attivi dei componenti
            $servizi = TT_ComponenteModel::whereIn('unitcode', $temp_units)
                ->with(['servizio_gps' => function ($with) {
                    $with->attivi();
                }])
                ->get()
                ->pluck('servizio_gps')
                ->flatten();

            // Prendo le flotte dei servizi attivi
            $flotte = TT_FlottaModel::whereHas('servizi', function ($has) use ($servizi) {
                $has->whereIn('TT_Servizio.id', $servizi->pluck('id'));
            })->get();

            /** Funzione per prendere il gps principale da una query */
            $orderGPSFunc = function (Builder $gps) {
                return $gps->orderBy('principale', 'desc');
            };

            // Prendo tutte le notifiche che hanno avuto una pos e che hanno aree
            /**@var Builder|TT_NotificaTargetModel Commento per intellisense */
            $notifiche = TT_NotificaTargetModel::whereHasMorph('trigger', 'TT_Area')
                // Per i servizi che hanno avuto una posizione
                ->whereHasMorph('observable', 'TT_Servizio', function (Builder $builder) use ($servizi) {
                    $builder->whereIn('TT_Servizio.id', $servizi->pluck('id'));
                })
                // Per le flotte che hanno avuto una posizione
                ->orWhereHasMorph('observable', 'TT_Flotta', function (Builder $builder) use ($flotte) {
                    $builder->whereIn('TT_Flotta.id', $flotte->pluck('id'));
                })
                // Eager load
                ->with([
                    'trigger.utente',
                    // L'osservabile ti tipo flotta o servizio lo carico con i gps ordinato per principale
                    'observable' => function (MorphTo $morphTo) use ($orderGPSFunc) {
                        $morphTo->morphWith([
                            // Carico l'osservabile di tipo flotta insieme ai servizi che hanno un mezzo e un gps
                            'TT_Flotta' => [
                                'servizi' => function ($with) {
                                    $with->whereHas('gps');
                                },
                                'servizi.gps' => $orderGPSFunc,
                            ],
                            'TT_Servizio' => ['gps' => $orderGPSFunc]
                        ]);
                    },
                    'tipologia', // In, Out, In/Out
                    'campo_notifica' // Campo email da notificare
                ])->get();

            // Array di ['unitcode' => 'posizione_json'] da salvare sul redis per il giro dopo
            $to_save = [];

            //? Verifico la old latest di quello unitcode
            foreach ($notifiche as $notifica) {

                // Tratto i servizi in ogni caso come array (flotta->servizi o servizio in se e per se)
                /** @var TT_ServizioModel[] */
                $serviziNotificabili = $notifica->observable->servizi ?? [$notifica->observable];

                foreach ($serviziNotificabili as $servizio) {
                    try {
                        /** @var string Unitcode notifica*/
                        $noti_unitcode = $servizio->gps[0]->unitcode;
                        // Posizione corrente al momento del job
                        $latest_pos = (object) $temp_pos->firstWhere('PartitionKey', $noti_unitcode);

                        if (!isset($latest_pos) || empty($latest_pos)) continue;

                        // //!! TEST ONLY
                        // $latest_pos->fixGps = (new \Carbon\Carbon())->toISOString();
                        // $latest_pos->latitude = 16;

                        // Lo imposto per il salvataggio
                        $to_save[$noti_unitcode] = json_encode($latest_pos);

                        $old_pos = TargetManager::getLatestUnitcode($noti_unitcode);

                        // dump($latest_pos, $old_pos);

                        // Non esiste il vecchio valore della temperatura

                        if (!isset($old_pos) || empty($old_pos)) continue;

                        if (isset($latest_pos->fixGps) && isset($old_pos->fixGps) && (new \Carbon\Carbon($latest_pos->fixGps) <= new \Carbon\Carbon($old_pos->fixGps))) continue;

                        /** @var object GeoJson dell'area */
                        $geo_json = $notifica->trigger->geo_json;

                        $isLatestInside = TargetHelper::isInsideFeature($geo_json->features[0], [
                            $latest_pos->longitude,
                            $latest_pos->latitude,
                        ]);

                        $isOldInside = TargetHelper::isInsideFeature($geo_json->features[0], [
                            $old_pos->longitude,
                            $old_pos->latitude,
                        ]);

                        if ($isLatestInside !== $isOldInside) {
                            $mail_to = $notifica->campo_notifica->nome ?? $notifica->trigger->utente->email;
                            if (!App::environment('produzione')) $mail_to = 'areasoftware@recorditalia.net';

                            // Se è dentro e prima era fuori e se ha tipo IN o IN/OUT o Industria 4.0 a.k.a. è entrato
                            if (
                                $isLatestInside === true && $isOldInside === false
                                && ($notifica->tipologia->id === 123 || $notifica->tipologia->id === 121 || $notifica->tipologia->id === 124)
                            ) {

                                Mail::to($mail_to)->send(new NotificaTargetMailable($notifica, $servizio, true));

                                // Modulazione Uscita
                                if ($notifica->tipologia->id === 124) {
                                    try {
                                        (new ModulazioneUsciteController)->dispatchWithProxy($servizio->id, 'setSingleStatus', 0, 1);
                                    } catch (\Throwable $th) {
                                        Log::channel('dev')->error("[ENTRATA][No. {$th->getCode()}][file: {$th->getFile()} at line: {$th->getLine()}] {$th->getMessage()}");
                                    }
                                }

                                /** @var TT_StoricoTargetModel */
                                $entryStorico = TT_StoricoTargetModel::make([
                                    'trigger_type' => 'TT_Area',
                                    'trigger_id' => $notifica->trigger->id,
                                    'posizione' => $latest_pos
                                ]);

                                $entryStorico->servizio()->associate($servizio->id);
                                $entryStorico->tipologia()->associate(121); //! Tipologia IN

                                $entryStorico->save();
                            }

                            // Se è fuori e prima era dentro e se ha tipo OUT o IN/OUT o Industria 4.0 a.k.a. è uscito
                            else if (
                                $isLatestInside === false && $isOldInside === true
                                && ($notifica->tipologia->id === 123 || $notifica->tipologia->id === 122 || $notifica->tipologia->id === 124)
                            ) {
                                Mail::to($mail_to)->send(new NotificaTargetMailable($notifica, $servizio, false));

                                // Modulazione Uscita
                                if ($notifica->tipologia->id === 124) {
                                    try {
                                        (new ModulazioneUsciteController)->dispatchWithProxy($servizio->id, 'setSingleStatus', 0, 0);
                                    } catch (\Throwable $th) {
                                        Log::channel('dev')->error("[USCITA][No. {$th->getCode()}][file: {$th->getFile()} at line: {$th->getLine()}] {$th->getMessage()}");
                                    }
                                }

                                /** @var TT_StoricoTargetModel */
                                $entryStorico = TT_StoricoTargetModel::make([
                                    'trigger_type' => 'TT_Area',
                                    'trigger_id' => $notifica->trigger->id,
                                    'posizione' => $latest_pos
                                ]);

                                $entryStorico->servizio()->associate($servizio->id);
                                $entryStorico->tipologia()->associate(122); //! Tipologia OUT

                                $entryStorico->save();
                            }
                        }
                    } catch (\Throwable $th) {
                        Log::channel('dev')->critical("[Servizio: $servizio->id][No. {$th->getCode()}][file: {$th->getFile()} at line: {$th->getLine()}] {$th->getMessage()}");
                        // throw $th;
                    }
                }
            }
        } catch (\Throwable $th) {
            Log::channel('dev')->critical("[No. {$th->getCode()}][file: {$th->getFile()} at line: {$th->getLine()}] {$th->getMessage()}");
            // throw $th;
        } finally {
            //? Salvo in ogni caso la latest notificata
            TargetManager::setLatests($to_save);
        }
    }
}
