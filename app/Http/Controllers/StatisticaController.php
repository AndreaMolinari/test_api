<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


set_time_limit(0);

class StatisticaController extends Controller
{

    const BOOST_VALUE = 1.15; // 115% === 1.15

    public function web_stats()
    {
        $results = [
            'clienti'   => null,
            'servizi'   => null,
            'sicurezza' => null,
            'localizzatore' => null,
            'tachigrafo'    => null,
        ];

        $results['clienti'] = DB::table('TT_Anagrafica')
            ->select(DB::raw('COUNT(`TC_AnagraficaTipologia`.`id`) as conto'))
            ->join('TC_AnagraficaTipologia', 'TT_Anagrafica.id', 'TC_AnagraficaTipologia.idAnagrafica')
            ->where('TC_AnagraficaTipologia.idTipologia', 12)
            ->whereRaw("TT_Anagrafica.id NOT IN (" . DB::raw("
                SELECT idChild FROM `TC_AnagraficaAnagrafica`
            )"))
            ->first()->conto;

        $results['servizi'] = (DB::table('TT_Servizio')
            ->select(DB::raw('COUNT(`TT_Servizio`.`id`) as conto'))
            ->whereRaw("TT_Servizio.idAnagrafica NOT IN (" . DB::raw("
                SELECT idChild FROM `TC_AnagraficaAnagrafica`
            )"))
            ->first()->conto - 2100);

        $results['tachigrafo'] = $this->sum($this->count_tacho());

        $results['sicurezza'] = intval($results['servizi'] / 100 * 35);
        $results['localizzatore'] = ($this->sum($this->count_gps()) - $results['sicurezza'] + 1500); // in teoria 1500 sono tomtom

        foreach ($results as &$result) {
            $result = round($result * self::BOOST_VALUE, 0);
        }

        return $results;

        //  ! Aggiungi il 15% ad ogni risultato!
    }

    public function count_gps()
    {
        /** @var TT_UtenteModel */
        $loggedUser = Auth::user();
        $idAnagrafica = $loggedUser->idAnagrafica ?? 40;

        $res = DB::table('TC_ServizioComponente')
            ->select(['modello', DB::raw('COUNT(TC_ServizioComponente.id) as count')])
            ->join('TT_Servizio', 'TT_Servizio.id', 'idServizio')
            ->join('TT_Componente', 'TT_Componente.id', 'TC_ServizioComponente.idComponente')
            ->join('TT_Modello', 'TT_Modello.id', 'TT_Componente.idModello')
            ->whereRaw("dataInizio <= now() AND (dataFine IS NULL OR dataFine >= now()) AND idComponente IS NOT NULL AND TT_Servizio.idAnagrafica NOT IN (" . DB::raw("
                SELECT idChild FROM `TC_AnagraficaAnagrafica` WHERE idParent = {$idAnagrafica}
            )"))
            ->groupBy('idModello')
            ->orderByDesc('count')
            ->get();

        return $res;
    }

    public function count_mezzo()
    {
        /** @var TT_UtenteModel */
        $loggedUser = Auth::user();
        $idAnagrafica = $loggedUser->idAnagrafica ?? 40;

        $res = DB::table('TC_ServizioComponente')
            ->select(['marca', 'modello', DB::raw('COUNT(TC_ServizioComponente.id) as count')])
            ->join('TT_Servizio', 'TT_Servizio.id', 'idServizio')
            ->join('TT_Mezzo', 'TT_Mezzo.id', 'TC_ServizioComponente.idMezzo')
            ->join('TT_Modello', 'TT_Modello.id', 'TT_Mezzo.idModello')
            ->join('TT_Brand', 'TT_Brand.id', 'TT_Modello.idBrand')
            ->whereRaw("dataInizio <= now() AND (dataFine IS NULL OR dataFine >= now()) AND idMezzo IS NOT NULL AND TT_Servizio.idAnagrafica NOT IN (" . DB::raw("
                SELECT idChild FROM `TC_AnagraficaAnagrafica` WHERE idParent = {$idAnagrafica}
            )"))
            ->groupBy('idModello')
            ->orderByDesc('count')
            ->get();

        return $res;
    }

    public function count_tacho()
    {
        /** @var TT_UtenteModel */
        $loggedUser = Auth::user();
        $idAnagrafica = $loggedUser->idAnagrafica ?? 40;

        $res = DB::table('TC_ServizioComponente')
            ->select(['modello', DB::raw('COUNT(TC_ServizioComponente.id) as count')])
            ->join('TT_Servizio', 'TT_Servizio.id', 'idServizio')
            ->join('TT_Componente', 'TT_Componente.id', 'TC_ServizioComponente.idTacho')
            ->join('TT_Modello', 'TT_Modello.id', 'TT_Componente.idModello')
            ->whereRaw("dataInizio <= now() AND (dataFine IS NULL OR dataFine >= now()) AND idTacho IS NOT NULL AND TT_Servizio.idAnagrafica NOT IN (" . DB::raw("
                SELECT idChild FROM `TC_AnagraficaAnagrafica` WHERE idParent = {$idAnagrafica}
            )"))
            ->groupBy('idModello')
            ->orderByDesc('count')
            ->get();

        return $res;
    }

    public function count_applicativo()
    {
        /** @var TT_UtenteModel */
        $loggedUser = Auth::user();
        $idAnagrafica = $loggedUser->idAnagrafica ?? 40;

        $res = DB::table('TC_ServizioApplicativo')
            ->select(['tipologia', DB::raw('COUNT(TC_ServizioApplicativo.id) as count')])
            ->join('TT_Servizio', 'TT_Servizio.id', 'TC_ServizioApplicativo.idServizio')
            ->join('TT_Tipologia', 'TC_ServizioApplicativo.idTipologia', 'TT_Tipologia.id')
            ->whereRaw("dataInizio <= now() AND (dataFine IS NULL OR dataFine >= now()) AND TT_Servizio.idAnagrafica NOT IN (" . DB::raw("
                SELECT idChild FROM `TC_AnagraficaAnagrafica` WHERE idParent = {$idAnagrafica}
            )"))
            ->groupBy('idTipologia')
            ->orderByDesc('count')
            ->get();

        return $res;
    }

    public function count_rivenditore()
    {
        $res = DB::table('TC_AnagraficaAnagrafica')
            ->select(['idParent', 'ragSoc', DB::raw('COUNT(TC_AnagraficaAnagrafica.idChild) as figli')])
            ->join('TT_Anagrafica', 'TT_Anagrafica.id', 'TC_AnagraficaAnagrafica.idParent')
            ->groupBy('idParent')
            ->orderByDesc('figli')
            ->get();

        return $res;
    }

    public function sum($array)
    {
        $sum = 0;
        foreach ($array as $a) {
            $sum += $a->count;
        }
        return $sum;
    }
}
