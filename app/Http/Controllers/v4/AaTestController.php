<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Models\{TC_FlottaServizioModel, TC_ServizioComponenteModel, TT_AnagraficaModel, TT_ServizioModel};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// use Illuminate\Validation\ValidationException;

class AaTestController extends Controller
{
    public static function testGiffi()
    {
        $to_update = [];
        $servizi = TT_AnagraficaModel::find(906)->servizi;

        foreach($servizi as $servizio)
        {
            try {
                if( count($servizio->mezzo) >= 1 )
                {
                    if( isset($servizio->mezzo[0]->targa) || $servizio->mezzo[0]->telaio )
                    {
                        $to_update[$servizio->id] = (!empty($servizio->mezzo[0]->targa)) ? $servizio->mezzo[0]->targa : $servizio->mezzo[0]->telaio;
                    }
                }else{
                    $to_update[$servizio->id] = ($servizio->gps[0]->unitcode);
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        foreach($to_update as $idServizio => $nickname)
        {
            foreach( TC_FlottaServizioModel::where('idServizio', $idServizio)->get() as $tc_f_s )
            {
                $tc_f_s->nickname = $nickname;
                $tc_f_s->save();

                foreach( TC_ServizioComponenteModel::where('idServizio', $idServizio)->whereNotNull('idMezzo')->get() as $tc_mezzo )
                {
                    $tc_mezzo->delete();
                }
            }
        }

        return "Fine";
    }

    public function mls_scad(Request $request)
    {
        $request->validate([
            'idTipologia'    => 'required',
            'idRivenditore'  => 'required',
            'created_after'  => 'present|nullable|date',
            'created_before' => 'present|nullable|date|afterOrEqual:created_after',
        ]);

        $created_at = "";
        if( is_null($request['created_after'])  && is_null($request['created_before']) ){
            $from = (new Carbon('first day of last month'))->subDay()->format('Y-m-d');
            $to   = (new Carbon('last day of last month'))->addDay()->format('Y-m-d');
            $created_at = "AND `TT_Utente`.`created_at` BETWEEN '{$from}' AND '{$to}'";
            unset($from, $to);
        }elseif( is_null($request['created_after'])  && !is_null($request['created_before']) ){
            $created_at = "AND `TT_Utente`.`created_at` <= '{$request['created_before']}'";
        }elseif( !is_null($request['created_after'])  && is_null($request['created_before']) ){
            $created_at = "AND `TT_Utente`.`created_at` >= '{$request['created_after']}'";
        }elseif( !is_null($request['created_after'])  && !is_null($request['created_before']) ){
            $from = (new Carbon($request['created_after']))->subDay()->format('Y-m-d');
            $to   = (new Carbon($request['created_before']))->addDay()->format('Y-m-d');
            $created_at = "AND `TT_Utente`.`created_at` BETWEEN '{$from}' AND '{$to}'";
            unset($from, $to);
        }


        return DB::select(DB::raw("
            SELECT `TT_Anagrafica`.`id` as `idAnagrafica`,
                concat( COALESCE(`TT_Anagrafica`.`ragSoc`, ''), COALESCE(`TT_Anagrafica`.`nome`, ''), ' ', COALESCE(`TT_Anagrafica`.`cognome`, '') ) as denominazione,
                `TT_Utente`.`username`,
                `TT_Utente`.`created_at`
            FROM `TT_Utente`
            INNER JOIN `TC_AnagraficaAnagrafica` ON `TC_AnagraficaAnagrafica`.`idChild` = `TT_Utente`.`idAnagrafica`
            INNER JOIN `TT_Anagrafica` ON `TT_Anagrafica`.`id` = `TT_Utente`.`idAnagrafica`
            WHERE 1 AND `TC_AnagraficaAnagrafica`.`idParent` = {$request['idRivenditore']}
            AND `TT_Utente`.`idTipologia` = {$request['idTipologia']}
            {$created_at}
            AND `idAnagrafica` IN (
                SELECT `idAnagrafica`
                FROM `TT_Servizio`
                WHERE `dataInizio` <= now()
                AND (`dataFine` >= now() OR `dataFine` IS NULL)
            );
        "));
        return $request;
    }
}


