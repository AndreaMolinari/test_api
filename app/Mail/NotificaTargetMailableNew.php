<?php

namespace App\Mail;

use App\Events\InOutTargetEvent;
use App\Events\InTargetEvent;
use App\Models\Targets\TT_TriggerEventoModel;
use App\Models\TT_ServizioModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class NotificaTargetMailableNew extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /**
     * @property TT_ServizioModel $servizio
     */
    public $servizio;

    /**
     * @property TT_TriggerEventoModel $trigger
     */
    public $triggerEvento;

    /**
     * Create a new event instance.
     *
     * @param TT_ServizioModel $servizio
     * @param TT_TriggerEventoModel $trigger
     * @return void
     */
    public function __construct(TT_ServizioModel $servizio, TT_TriggerEventoModel $triggerEvento) 
    {
        $this->onQueue('mails');
        $this->servizio = $servizio;
        $this->triggerEvento = $triggerEvento;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        //? Is MLS
        $this->servizio->load('anagrafica.parent');
        $isMLS = false;
        
        // Controllo se il servizio è MLS oppure no
        if($this->servizio->anagrafica->parent && $this->servizio->anagrafica->parent->id === 40){
            $isMLS = true;
        }

        // Recupero il nome del mezzo da usare nella mail
        $idMezzo = $this->servizio->mezzo[0]->targa ?? $this->servizio->mezzo[0]->telaio ?? $this->servizio->gps[0]->unitcode ?? 'non assegnato';
        Log::channel('dev')->debug("Sto inviando la mail di $idMezzo");

        if ($isMLS) {
            $view = 'emails.notificatargetmlsnew';
            $azienda = '';
            if($this->servizio->anagrafica->parent && $this->servizio->anagrafica->parent->ragSoc){
                $azienda = $this->servizio->anagrafica->parent->ragSoc;
            }
        } else {
            $view = 'emails.notificatargetnew';
            $azienda = 'RecordItalia S.R.L.';
        }

        $nomeBersaglio = '';

        if(isset($this->triggerEvento->trigger->inizio)){

            $nomeBersaglio = $this->triggerEvento->trigger->inizio.'° / '.$this->triggerEvento->trigger->fine.'°';

            if($this->triggerEvento->event_class === InOutTargetEvent::class){
                $mailSubject = "[$azienda] Il temperatura del mezzo $idMezzo è variata rispetto la soglia " . $nomeBersaglio;
            }else{
                $mailSubject = "[$azienda] La temperatura del mezzo $idMezzo è " . ($this->triggerEvento->event_class === InTargetEvent::class ? 'entrata nella ' : 'uscita dalla') . ' soglia ' . $nomeBersaglio;
            }
        }else{
            $nomeBersaglio = $this->triggerEvento->trigger->nome;

            if($this->triggerEvento->event_class === InOutTargetEvent::class){
                $mailSubject = "[$azienda] Il mezzo $idMezzo si è mosso nel bersaglio " . $nomeBersaglio;
            }else{
                $mailSubject = "[$azienda] Il mezzo $idMezzo è " . ($this->triggerEvento->event_class === InTargetEvent::class ? 'entrato nel' : 'uscito dal') . ' bersaglio ' . $nomeBersaglio;
            }
        }

        if(!App::environment('produzione')){
            $mailSubject = '[DEV]'.$mailSubject;
        }

        // Non è MLS
        $messaggio = $this->view($view);
        $messaggio->subject($mailSubject);
        $messaggio->with([
                'servizio' => $this->servizio,
                'messaggioCustom' => $this->triggerEvento->action->messaggioCustom,
                'nomeBersaglio' => $nomeBersaglio
            ]);
        return $messaggio;
    }
}
