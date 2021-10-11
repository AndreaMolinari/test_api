<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreavvisoManutenzione extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */


    protected $customTipologia;
    protected $mezzo;
    protected $value;
    protected $unit;

    public function __construct($customTipologia, $mezzo, $value, $unit)
    {
        $this->onQueue('mails');
        //
        $this->customTipologia = $customTipologia;
        $this->mezzo = $mezzo;
        $this->value = $value;
        $this->unit = $unit;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->view('emails.manutenzione_preavviso')
            ->cc('areasoftware@recorditalia.net', 'Area Software')
            ->subject('Record s.r.l. - Preavviso scadenza')
            ->with([
                'customTipologia' => $this->customTipologia,
                'mezzo' => $this->mezzo,
                'value' => $this->value,
                'unit' => $this->unit
            ]);
    }
}
