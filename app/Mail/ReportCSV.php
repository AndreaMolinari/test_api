<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Carbon\Carbon;

class ReportCSV extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $fileName;
    protected $now;
    protected $path;

    public function __construct($filePath, $fileName = null)
    {
        if( file_exists( $filePath ) )
        {
            $this->path = $filePath;
        }

        $this->fileName = ( !empty($fileName) ) ? $fileName : 'export';

        $this->fileName.= strpos($this->fileName, ".csv") ? '' : '.csv';

        $this->now = Carbon::now();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $date = $this->now->month . "/" . $this->now->year;
        return $this
            // ->from('devh.testmail@gmail.com')
            ->view('emails.reportcsv')
            ->cc('areasoftware@recorditalia.net', 'Area Software')
            ->subject('Record s.r.l. - Report Mensile ' . $date)
            ->with(['data' => $date])
            ->attach($this->path, ['as' => $this->fileName]);
    }
}
