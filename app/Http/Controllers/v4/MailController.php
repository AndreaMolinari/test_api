<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;

use App\Mail\{ReportCSV, CheckMailMailable, PreavvisoManutenzione, ScadutaManutenzione};
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

// use Illuminate\Validation\ValidationException;

class MailController extends Controller {
    public function __construct() {
    }

    //TODO amaracmand, adesso il file si chiama per forza _new.csv, impostare il modo di modificarlo
    public function sendReportMensile($json, $to = 'areasoftware@recorditalia.net', $fileName = null) {

        $temp_pointer = tmpfile();
        fputcsv($temp_pointer, array_keys((array) $json[0]));
        foreach ($json as $row) {
            fputcsv($temp_pointer, array_values((array) $row));
        }

        if (!App::environment('produzione')) $to = 'areasoftware@recorditalia.net';
        Mail::to($to)->send(new ReportCSV(stream_get_meta_data($temp_pointer)['uri'], 'asdasd.csv'));
        // Mail::to($to)->send(new ReportCSV( stream_get_meta_data($temp_pointer)['uri'] ));
        fclose($temp_pointer);
    }

    public function sendMailPreavvisoManutenzione($man) {
        try {
            $mezzo = (!is_null($man->servizio->mezzo[0]->targa)) ? $man->servizio->mezzo[0]->targa : $man->servizio->mezzo[0]->telaio;

            $tipologia = $man->custom_tipologia->nome;

            if (isset($man->preavviso->km_fine)) {
                $value = $man->km_fine - $man->servizio->mezzo[0]->km_totali;
                $unit = 'KM';
            }
            if (isset($man->preavviso->ore_fine)) {
                $value = $man->ore_fine - $man->servizio->mezzo[0]->ore_totali;
                $unit = 'ore';
            }
            if (isset($man->preavviso->giorno_end)) {
                $value = Carbon::parse($man->giorno_end)->diffInDays(new Carbon());
                $unit = 'giorni';
            }

            if (isset($man->note[0]->testo) && $man->note[0]->testo != '') {
                $nota = $man->note[0]->testo;
            }

            $emailTo = $man->custom_email->nome;

            // ! Manca la nota
            if( $man->scadenze->km || $man->scadenze->ore || $man->scadenze->giorno ){
                if (!App::environment('produzione')) $emailTo = 'areasoftware@recorditalia.net';
                Mail::to($emailTo)->send(new PreavvisoManutenzione($tipologia, $mezzo, $value, $unit));
                return true;
            }else{
                return false;
            }
        } catch (\Throwable $th) {
            logger()->debug($th);
            return false;
        }
    }

    public function sendMailScadutaManutenzione($man) {
        try {
            $mezzo = (!is_null($man->servizio->mezzo[0]->targa)) ? $man->servizio->mezzo[0]->targa : $man->servizio->mezzo[0]->telaio;

            $tipologia = $man->custom_tipologia->nome;

            $emailTo = $man->custom_email->nome;

            $nota = "";
            if (isset($man->note[0]->testo) && $man->note[0]->testo != '') {
                $nota = $man->note[0]->testo;
            }
            if( $man->scadenze->km || $man->scadenze->ore || $man->scadenze->giorno ){
                if (!App::environment('produzione')) $emailTo = 'areasoftware@recorditalia.net';
                Mail::to($emailTo)->send(new ScadutaManutenzione($tipologia, $mezzo, $nota));
                return true;
            }else{
                return false;
            }
        } catch (\Throwable $th) {
            logger()->debug($th);
            return false;
        }
    }

    public static function job_info(object $mail) {
        if (App::environment('produzione'))
            return Mail::to('areasoftware@recorditalia.net')->send(new CheckMailMailable($mail));
    }
}
