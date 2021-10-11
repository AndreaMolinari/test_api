<?php

namespace App\Jobs;

use App\Common\Managers\Redis\SogliaTemperaturaManager;
use App\Mail\NotificaSogliaMailable;
use App\Models\Targets\TT_NotificaTargetModel;
use App\Models\TT_ComponenteModel;
use App\Models\TT_FlottaModel;
use App\Models\TT_ServizioModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class CheckSoglieTemperaturaJob implements ShouldQueue {
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
        $this->onQueue('soglie');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        try {

            //* Check quali delle latest da notificare

            // Filtro le posizioni per aggiornamenti delle temperatura e prendo la partition key
            /**
             * @var Collection
             */
            $temp_pos = collect(json_decode(Redis::get($this->latest_key)) ?? [])->filter(function ($item) {
                return $item && $item->event && $item->event === 281;
            });
            /**
             * @var string[]
             */
            $units_temp = $temp_pos->pluck('PartitionKey');

            // Prendo i servizi attivi dei componenti
            $servizi = TT_ComponenteModel::whereIn('unitcode', $units_temp)
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


            /**Funzione per prendere il gps principale da una query */
            $orderGPSFunc = function (Builder $gps) {
                return $gps->orderBy('principale', 'desc');
            };


            // Prendo tutte le notifiche che hanno avuto un pos di lettura temp
            /**@var Builder|TT_NotificaTargetModel Commento per intellisense */
            $notifiche = TT_NotificaTargetModel::whereHasMorph('trigger', 'TT_Soglia', function (Builder $builder) {
                $builder->ofType(116); // Tipologia Temperatura -> Soglia
            })
                ->whereHasMorph('observable', 'TT_Servizio', function (Builder $builder) use ($servizi) {
                    $builder->whereIn('TT_Servizio.id', $servizi->pluck('id'));
                })
                ->orWhereHasMorph('observable', 'TT_Flotta', function (Builder $builder) use ($flotte) {
                    $builder->whereIn('TT_Flotta.id', $flotte->pluck('id'));
                })
                ->with([
                    'trigger.utente',
                    'trigger.tipologia',
                    'observable' => function (MorphTo $morphTo) use ($orderGPSFunc) {
                        $morphTo->morphWith([
                            // Carico l'osservabile di tipo flotta insieme ai servizi che hanno un mezzo e un gps
                            'TT_Flotta' => [
                                'servizi' => function ($with) {
                                    $with->whereHas('mezzo')->whereHas('gps');
                                },
                                'servizi.gps' => $orderGPSFunc,
                            ],
                            'TT_Servizio' => ['gps' => $orderGPSFunc]
                        ]);
                    },
                    'tipologia',
                    'campo_notifica'
                ]);

            // Ottengo le notifiche con la soglia temperatura castata a float
            $notifiche = $notifiche->get()->each(function ($item) {
                $item->trigger->inizio = (float) $item->trigger->inizio;
                $item->trigger->fine = (float) $item->trigger->fine;
            });

            // Array di unitcode => posizione da salvare sul redis per il giro dopo
            $to_save = [];

            //* Verifico la old latest di quello unitcode
            foreach ($notifiche as $notifica) {

                // Tratto i servizi in ogni caso come array
                /**@var TT_ServizioModel[] */
                $serviziNotificabili = $notifica->observable->servizi ?? [$notifica->observable];

                foreach ($serviziNotificabili as $servizio) {
                    /**@var string */
                    $noti_unitcode = $servizio->gps[0]->unitcode;
                    $latest_pos = $temp_pos->firstWhere('PartitionKey', $noti_unitcode);

                    if (!isset($latest_pos)) continue;

                    //!! PER TEST PER SETTARE LA TEMP A MANO
                    // $latest_pos->analog1 = ($latest_pos->analog1 >= 0 && $latest_pos->analog1 <= 10 ? 11 : 10);
                    // $latest_pos->analog1 = 11;

                    $to_save[$noti_unitcode] = json_encode($latest_pos);

                    $old_pos = SogliaTemperaturaManager::getLatestUnitcode($noti_unitcode);

                    // Non esiste il vecchio valore della temperatura
                    if (!isset($old_pos)) continue;

                    //* START Sarebbe da fare per ogni sensore
                    foreach ([1, 2] as $analogIndex) {
                        // $isLatestInside = false;
                        $isLatestInside = $latest_pos->{'analog' . $analogIndex} >= $notifica->trigger->inizio && $latest_pos->{'analog' . $analogIndex} <= $notifica->trigger->fine;

                        // $isOldInside = false;
                        $isOldInside = $old_pos->{'analog' . $analogIndex} >= $notifica->trigger->inizio && $old_pos->{'analog' . $analogIndex} <= $notifica->trigger->fine;

                        //* Accodo la notifica mail se è cambiato lo stato
                        if ($isLatestInside !== $isOldInside) {
                            $mail_to = $notifica->campo_notifica->nome ?? $notifica->trigger->utente->email;
                            if (!App::environment('produzione')) $mail_to = 'areasoftware@recorditalia.net';


                            // Se è dentro e prima era fuori e se ha tipo IN o IN/OUT
                            if (
                                $isLatestInside === true && $isOldInside === false
                                && ($notifica->tipologia->id === 123 || $notifica->tipologia->id === 121)
                            ) {
                                Mail::to($mail_to)->send(new NotificaSogliaMailable($notifica, $servizio, $latest_pos->{'analog' . $analogIndex}, true));
                            }
                            // Se è fuori e prima era dentro e se ha tipo OUT o IN/OUT
                            else if (
                                $isLatestInside === false && $isOldInside === true
                                && ($notifica->tipologia->id === 123 || $notifica->tipologia->id === 122)
                            ) {
                                Mail::to($mail_to)->send(new NotificaSogliaMailable($notifica, $servizio, $latest_pos->{'analog' . $analogIndex}, false));
                            }
                        }
                    }
                    //* END Sarebbe da fare per ogni sensore
                }
            }

            //* Salvo in ogni casto la latest notificata
            SogliaTemperaturaManager::setLatests($to_save);
        } catch (\Throwable $th) {
            Log::channel('dev')->critical("[No. {$th->getCode()}][file: {$th->getFile()} at line: {$th->getLine()}] {$th->getMessage()}");
        }
    }
}
