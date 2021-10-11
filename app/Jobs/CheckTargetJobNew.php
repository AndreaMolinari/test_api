<?php

namespace App\Jobs;

use App\Common\Helpers\TargetHelper;
use App\Common\Managers\Redis\TargetManager;
use App\Events\InOutTargetEvent;
use App\Events\InTargetEvent;
use App\Events\OutTargetEvent;
use App\Models\Targets\TT_AreaModel;
use App\Models\Targets\TT_StoricoEventoModel;
use App\Models\TT_ServizioModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CheckTargetJobNew implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;

    /**
     * Redis key to latest positions
     * @var string
     */
    protected $latestKey;

    /**
     * Create a new job instance.
     *
     * @param string $latestKey // Stringa che corrisponde alla key di Redis usata per recuperare le ultime posizioni salvate. Es.: 1631025997latest_pos
     * 
     * @return void
     */
    public function __construct(string $latestKey)
    {
        $this->latestKey = $latestKey;
        $this->onQueue('targets');
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addMinutes(1);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('*** INIZIO JOB TARGET *** - '.$this->latestKey);
        $toSave = [];
        try {
            // Recuper tutti i servizi con un trigger evento
            $servizi = TT_ServizioModel::whereHas('triggers_evento', function ($has) {
                return $has
                    ->where('idTipologiaEvento', 121)   // In
                    ->orWhere('idTipologiaEvento', 122) // Out
                    ->orWhere('idTipologiaEvento', 123) // In/Out
                    ->whereHasMorph(
                        'trigger',
                        [TT_AreaModel::class]
                    );
            })
                // Carico la periferica, le azioni solo per target
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
                if(!$latestPos){
                    continue;
                }
                

                if (!isset($latestPos) || empty($latestPos)) continue;
                if (!isset($latestPos->fixGps)) continue;

                // Tengo per il salvataggio la posizione appena arrivata per il giro dopo
                $toSave[$unitcode] = json_encode($latestPos);
                // Da Redis recupero la vecchia posizione per questo componente
                $oldPos = TargetManager::getLatestUnitcode($unitcode);
                if (!isset($oldPos) || empty($oldPos)) continue;

                // Controllo se che il punto arrivato non sia più vecchio dell'ultima posizione salvata
                if ((new \Carbon\Carbon($latestPos->fixGps) <= new \Carbon\Carbon($oldPos->fixGps))) continue;

                // Ciclo tutti i trigger eventi del servizio
                foreach ($servizio->triggers_evento as $trigger_evento) {

                    if(!isset($trigger_evento) || !isset($trigger_evento->trigger)){ continue; }

                    /** @var object GeoJson dell'area */
                    $geo_json = $trigger_evento->trigger->geo_json;

                    if(!isset($geo_json) || !is_object($geo_json) || !$geo_json->features || count($geo_json->features) < 1){
                        continue;
                    }

                    // Calcolo se la periferica è entrata o uscita dal bersaglio in base alle cordinate
                    $isLatestInside = TargetHelper::isInsideFeature($geo_json->features[0], [
                        $latestPos->longitude,
                        $latestPos->latitude,
                    ]);

                    $isOldInside = TargetHelper::isInsideFeature($geo_json->features[0], [
                        $oldPos->longitude,
                        $oldPos->latitude,
                    ]);

                    $triggered = false;
                    // Se la periferica è entrata nel bersaglio lancio un evento di tipo IN
                    if ($isLatestInside === true && $isOldInside === false) {
                        if ($trigger_evento->event_class === InTargetEvent::class || $trigger_evento->event_class === InOutTargetEvent::class) { // !! || InOutTargetEvent
                            Log::info("Lancio l'evento Target IN per il componente ".$unitcode. " per l'area chiamata: ". $trigger_evento->trigger->nome);
                            InTargetEvent::dispatch($servizio, $trigger_evento);                            
                            $triggered = true;
                        }
                    // Se la periferica è entrata nel bersaglio lancio un evento di tipo OUT
                    } else if ($isLatestInside === false && $isOldInside === true) {
                        if ($trigger_evento->event_class === OutTargetEvent::class  || $trigger_evento->event_class === InOutTargetEvent::class) { // !! || InOutTargetEvent
                            Log::info("Lancio l'evento Target OUT per il componente ".$unitcode. " per l'area chiamata: ". $trigger_evento->trigger->nome);
                            OutTargetEvent::dispatch($servizio, $trigger_evento);
                            $triggered = true;
                        }
                    }

                    // Salvo l'evento all'interno dello storico eventi
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
        } catch (\Throwable $th) {
            Log::critical("[No. {$th->getCode()}][file: {$th->getFile()} at line: {$th->getLine()}] {$th->getMessage()}");            
        } finally {
            //? Salvo in ogni caso la latest notificata
            TargetManager::setLatests($toSave);
        }
        Log::info('*** FINE JOB TARGET *** - '.$this->latestKey);
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
