<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScadutaManutenzione extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $customTipologia;
    protected $mezzo;
    protected $nota;

    public function __construct($customTipologia, $mezzo, $nota)
    {
        $this->onQueue('mails');
        //
        $this->customTipologia = $customTipologia;
        $this->mezzo = $mezzo;
        if($nota != "") {
            $this->nota = "Nota: \"" . $nota . "\"";
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->view('emails.manutenzione_scaduta')
            ->cc('areasoftware@recorditalia.net', 'Area Software')
            ->subject('[Record s.r.l.] Scadenza manutenzione')
            ->with([
                'customTipologia' => $this->customTipologia,
                'mezzo' => $this->mezzo,
                "nota" => $this->nota
            ]);
    }
}
