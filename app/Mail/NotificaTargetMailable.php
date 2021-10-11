<?php

namespace App\Mail;

use App\Models\Targets\TT_NotificaTargetModel;
use App\Models\TT_ServizioModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificaTargetMailable extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /**
     * @var TT_NotificaTargetModel
     */
    protected $notifica;

    /**
     * @var TT_ServizioModel
     */
    protected $servizio;

    /**
     * @var bool
     */
    protected $isEntering;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(TT_NotificaTargetModel $notifica, TT_ServizioModel $servizio, bool $isEntering = false) {
        $this->onQueue('mails');
        $this->notifica = $notifica;
        $this->servizio = $servizio;
        $this->isEntering = $isEntering;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {

        //? Is MLS
        $this->servizio->load('anagrafica.parent');
        $isMLS = $this->servizio->anagrafica->parent->id === 40;

        $idMezzo = $this->servizio->mezzo[0]->targa ?? $this->servizio->mezzo[0]->telaio ?? $this->servizio->gps[0]->unitcode ?? 'non assegnato';
        // Log::channel('dev')->debug("Sto inviando la mail di $idMezzo per $this->isEntering::::" . json_encode($this->notifica));
        // Storage::put($this->servizio->id . '.servizio_mail_soglia.json', json_encode($this->servizio ?? 'NON ESISTO', JSON_PRETTY_PRINT));

        if ($isMLS) {
            return $this
                ->view('emails.notificatargetmls')
                ->subject("[MechanicalLinesSolutions S.R.L.] Il mezzo $idMezzo è " . ($this->isEntering ? 'entrato nel' : 'uscito dal') . ' bersaglio ' . $this->notifica->trigger->nome)
                ->with([
                    'notifica' => $this->notifica,
                    'servizio' => $this->servizio,
                ]);
        }

        // Non è MLS
        return $this
            ->view('emails.notificatarget')
            ->subject("[RecordItalia S.R.L.] Il mezzo $idMezzo è " . ($this->isEntering ? 'entrato nel' : 'uscito dal') . ' bersaglio ' . $this->notifica->trigger->nome)
            ->with([
                'notifica' => $this->notifica,
                'servizio' => $this->servizio,
            ]);
    }
}
