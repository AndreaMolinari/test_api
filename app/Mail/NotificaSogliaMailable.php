<?php

namespace App\Mail;

use App\Models\Targets\TT_NotificaTargetModel;
use App\Models\TT_ServizioModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NotificaSogliaMailable extends Mailable implements ShouldQueue {
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
     * Valore corrente della soglia
     * @var mixed
     */
    protected $currentValue;

    /**
     * @var bool
     */
    protected $isEntering;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(TT_NotificaTargetModel $notifica, TT_ServizioModel $servizio, $currentValue, bool $isEntering = false) {
        $this->onQueue('mails');
        $this->notifica = $notifica;
        $this->servizio = $servizio;
        $this->currentValue = $currentValue;
        $this->isEntering = $isEntering;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {

        $idMezzo = $this->servizio->mezzo[0]->targa ?? $this->servizio->mezzo[0]->telaio;
        // Log::channel('dev')->debug("Sto inviando la mail di $idMezzo per $this->isEntering::::" . json_encode($this->notifica));
        // Storage::put($this->servizio->id . '.servizio_mail_soglia.json', json_encode($this->servizio ?? 'NON ESISTO', JSON_PRETTY_PRINT));

        return $this
            ->view('emails.notificasoglia')
            ->subject("[RecordItalia S.R.L.] Il mezzo $idMezzo è " . ($this->isEntering ? 'entrato' : 'uscito') . ' dalla soglia ' . $this->notifica->trigger->tipologia->tipologia)
            ->with([
                'notifica' => $this->notifica,
                'servizio' => $this->servizio,
                'currentValue' => $this->currentValue,
            ]);
    }
}
