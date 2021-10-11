<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CheckMailMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $object;
    protected $body;
    protected $nota;
    protected $copy;

    public function __construct(object $msg)
    {
        $this->onQueue('mails');
        $this->object   = (property_exists($msg, "object")) ? $msg->object  : '';
        $this->body     = (property_exists($msg, "body"))   ? $msg->body    : '';
        $this->nota     = (property_exists($msg, "nota"))   ? $msg->nota    : '';
        $this->copy     = (property_exists($msg, "cc"))     ? $msg->cc      : null;
    }

    public function build()
    {
        $messaggio = $this->view('emails.CheckMail');
        $messaggio->subject('[RecordItalia s.r.l.]'.$this->object);
        if( !is_null($this->copy) )
        {
            $messaggio->cc($this->copy);
        }
        $messaggio->with([
                'body' => $this->body,
                'nota' => $this->nota
            ]);

        return $messaggio;
    }
}
