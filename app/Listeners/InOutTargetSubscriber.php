<?php

namespace App\Listeners;

use App\Events\InTargetEvent;
use App\Events\OutTargetEvent;
use App\Events\TargetEvent;
use App\Http\Controllers\v4\ModulazioneUsciteController;
use App\Mail\NotificaTargetMailableNew;
use App\Models\Targets\TT_AreaModel;
use App\Models\Targets\TT_NotificaModel;
use App\Models\Targets\TT_SogliaModel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InOutTargetSubscriber
{

    /**
     * Handle the IN event.
     *
     * @param  InTargetEvent  $event
     * @return void
     */
    public function handleInTarget(InTargetEvent $event)
    {
        if (!App::environment('produzione'))
            Log::channel('dev')->debug('[IN]: ' . json_encode($event));

        $this->sendNotifica($event);

        if ($event->triggerEvento->cambiaUscita == true) {
            try {
                (new ModulazioneUsciteController)->dispatchWithProxy($event->servizio->id, 'setSingleStatus', 0, 1);
            } catch (\Throwable $th) {
                Log::error("[ENTRATA][No. {$th->getCode()}][file: {$th->getFile()} at line: {$th->getLine()}] {$th->getMessage()}");
            }
        }
    }

    /**
     * Handle the OUT event.
     *
     * @param  InTargetEvent  $event
     * @return void
     */
    public function handleOutTarget(OutTargetEvent $event)
    {

        if (!App::environment('produzione'))
            Log::channel('dev')->debug('[OUT]: ' . json_encode($event));

        $this->sendNotifica($event);

        if ($event->triggerEvento->cambiaUscita == true) {
            try {
                (new ModulazioneUsciteController)->dispatchWithProxy($event->servizio->id, 'setSingleStatus', 0, 0);
            } catch (\Throwable $th) {
                Log::error("[USCITA][No. {$th->getCode()}][file: {$th->getFile()} at line: {$th->getLine()}] {$th->getMessage()}");
            }
        }
    }

    private function sendNotifica(TargetEvent $event)
    {
        try{
            // Controllo che l'azione sia effettivamente una notifica
            if ($event->triggerEvento->action_type !== TT_NotificaModel::TABLE_NAME) {
                Log::warning("L'evento legato al servizio {$event->servizio->id} non è di tipo Notifica");
                return;
            }
                
            // Recupero la notifica dell'evento triggerato
            /** @var TT_NotificaModel */            
            $notifica = $event->triggerEvento->action;

            // Ciclo tutti i contatti da notificare per l'evento
            foreach ($notifica->contatti as $contatto) {
                // Controllo che la tipologia di contatto sia una mail
                if ($contatto->idTipologia === 24) { //? MAIL
                    $mail = $contatto->contatto;
                    
                    // Controllo che il tipo di trigger sia di tipo Area e in caso mando la mail di notifica
                    if ($event->triggerEvento->trigger_type === TT_AreaModel::TABLE_NAME || $event->triggerEvento->trigger_type === TT_SogliaModel::TABLE_NAME ) {
                        Log::info("Invio la mail notifica a ".$mail);
                        if(!App::environment('produzione')){
                            // $mail = "areasoftware@recorditalia.net";
                            return;
                        }
                        Mail::to($mail)->send(new NotificaTargetMailableNew($event->servizio, $event->triggerEvento));                    
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("L'invio della mail di notifica evento non è andato a buon fine - ".$event->servizio->id);
            Log::error("ERROR: ".$e->getMessage());
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            InTargetEvent::class,
            [InOutTargetSubscriber::class, 'handleInTarget']
        );

        $events->listen(
            OutTargetEvent::class,
            [InOutTargetSubscriber::class, 'handleOutTarget']
        );
    }
}
