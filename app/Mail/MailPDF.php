<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailPDF extends Mailable
{
    use Queueable, SerializesModels;

    private $template;
    private $data;
    private $files;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( String $template, Array $data, Array $files = null )
    {
        $this->chooseTemplate($template);
        $this->castData($data);
        $this->castFiles($files);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        exit("ho spaccato tutto fratelli");
        return $this->view('emails.mailpdf');//->attach('/home/thomas/Pictures/avatar.jpg');
    }

    private function chooseTemplate($template)
    {
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }finally{

        }
    }
    private function castData($data)
    {
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }finally{

        }
    }
    private function castFiles($files)
    {
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }finally{

        }
    }
}
