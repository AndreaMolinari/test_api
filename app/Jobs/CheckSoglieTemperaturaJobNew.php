<?php

namespace App\Jobs;

use App\Common\Managers\Redis\SogliaTemperaturaManager;
use App\Events\InOutTargetEvent;
use App\Events\InTargetEvent;
use App\Events\OutTargetEvent;
use App\Models\Targets\TT_SogliaModel;
use App\Models\Targets\TT_StoricoEventoModel;
use App\Models\TT_ServizioModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CheckSoglieTemperaturaJobNew implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;

    /**
     * Redis key to latest positions
     * @var string
     */
    protected $latestKey;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $latestKey) {
        $this->latestKey = $latestKey;
        $this->onQueue('soglie');
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil() {
        return now()->addMinutes(1);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() 
    {
        Log::info('*** INIZIO JOB SOGLIE *** - '.$this->latestKey);
        $toSave = [];
        try {

            //? Check quali delle latest da notificare

            // Prendo i servizi attivi dei componenti
            $servizi = TT_ServizioModel::whereHas('triggers_evento', function ($has) {
                return $has
                    ->where('idTipologiaEvento', 121)   // In (soglia)
                    ->orWhere('idTipologiaEvento', 122) // Out (soglia)
                    ->orWhere('idTipologiaEvento', 123) // In/Out
                    ->whereHasMorph(
                        'trigger',
                        [TT_SogliaModel::class]
                    );
            })
                // Carico la periferica, le azioni solo per soglie
                ->with([
                    'gps' => function ($with) {
                        return $with->orderBy('principale', 'desc');
                    },
                    'triggers_evento.trigger',
                    'triggers_evento.action',
                    'triggers_evento.evento',
                ])
                ->get();

            foreach ($servizi as $servizio) {
                // Controllo che nel servizio ci sia un GPS valido con unitcode associato
                if(!$servizio->gps || count($servizio->gps) < 1 || !$servizio->gps[0]->unitcode){
                    Log::warning("Per il servizio {$servizio->id} non è stato trovato un GPS con unitcode valido ".$this->latestKey);
                    continue;
                }
                $unitcode = $servizio->gps[0]->unitcode;

                // Controllo se nelle ultime posizioni caricate esiste lo unitcode del servizio
                $latestPos = $this->getPositionFromRedis($unitcode);

                if(!$latestPos || !$latestPos->event || $latestPos->event !== 281){ continue; }

                if (!isset($latestPos) || empty($latestPos)) continue;
                if( !isset($latestPos->fixGps) ) continue;

                // Tengo per il salvataggio la posizione appena arrivata per il giro dopo
                $toSave[$unitcode] = json_encode($latestPos);
                // Da Redis recupero la vecchia posizione per questo componente
                $oldPos = SogliaTemperaturaManager::getLatestUnitcode($unitcode);

                if (!isset($oldPos) || empty($oldPos)) continue;
                // Controllo se che il punto arrivato non sia più vecchio dell'ultima posizione salvata
                if ((new \Carbon\Carbon($latestPos->fixGps) <= new \Carbon\Carbon($oldPos->fixGps))) continue;

                // Ciclo tutti i trigger eventi del servizio
                foreach ($servizio->triggers_evento as $trigger_evento) {

                    if(!isset($trigger_evento) || !isset($trigger_evento->trigger)){ continue; }

                    // ? Ciclo per ogni sensore temperatura
                    foreach ([1, 2] as $analogIndex) {
                        $triggered = false;

                        $isLatestInside = (float) $latestPos->{'analog' . $analogIndex} >= (float) $trigger_evento->trigger->inizio && (float) $latestPos->{'analog' . $analogIndex} <= (float) $trigger_evento->trigger->fine;

                        $isOldInside = (float) $oldPos->{'analog' . $analogIndex} >= (float) $trigger_evento->trigger->inizio && (float) $oldPos->{'analog' . $analogIndex} <= (float) $trigger_evento->trigger->fine;

                        if ($isLatestInside === true && $isOldInside === false) {
                            if ($trigger_evento->event_class === InTargetEvent::class || $trigger_evento->event_class === InOutTargetEvent::class) { //!! || InOutTargetEvent
                                Log::info("Lancio l'evento Soglia IN per il componente ".$unitcode. " per l'area chiamata: ". $trigger_evento->trigger->nome);
                                InTargetEvent::dispatch($servizio, $trigger_evento);
                                $triggered = true;
                            }
                            //!! ?cambia uscita
                        } else if ($isLatestInside === false && $isOldInside === true) {
                            if ($trigger_evento->event_class === OutTargetEvent::class || $trigger_evento->event_class === InOutTargetEvent::class) { //!! || InOutTargetEvent
                                Log::info("Lancio l'evento Soglia OUT per il componente ".$unitcode. " per l'area chiamata: ". $trigger_evento->trigger->nome);
                                OutTargetEvent::dispatch($servizio, $trigger_evento);
                                $triggered = true;
                            }
                            //!! ?cambia uscita
                        }

                        if ($triggered) {
                            /** @var TT_StoricoEventoModel */
                            $storico = TT_StoricoEventoModel::make(['posizione' => $latestPos]);
                            $storico->servizio()->associate($servizio);
                            $storico->evento()->associate($trigger_evento->evento);
                            $storico->trigger()->associate($trigger_evento->trigger);
                            $storico->save();
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            Log::critical("[No. {$th->getCode()}][file: {$th->getFile()} at line: {$th->getLine()}] {$th->getMessage()}");
        } finally {
            // ? Salvo in ogni casto la latest notificata
            SogliaTemperaturaManager::setLatests($toSave);
        }
        Log::info('*** FINE JOB SOGLIE *** - '.$this->latestKey);
        return;
    }

    protected function getPositionFromRedis($unitcode = null){

        if(!$unitcode){
            Log::error('Non posso cercare su redis perché lo unitcode è vuoto: ');
            return;
        }

        $posizione = Redis::hmget($this->latestKey, [$unitcode]);
        if (count($posizione) > 0) {
            return json_decode($posizione[0]);
        } 
        Log::error('Non ho trovato una posizione su redis per questo componente: '.$unitcode. ' chiave di redis: '. $this->latestKey);
        return;
    }
}
