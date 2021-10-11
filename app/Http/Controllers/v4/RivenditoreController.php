<?php
namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Repositories\Posizione;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RivenditoreController extends Controller
{
    public static function get_utenti()
    {
        /** @var TT_UtenteModel */
        $loggedUser = Auth::user();
        return DB::select(DB::raw("
            SELECT `TT_Utente`.`id`, `TT_Utente`.`idParent`, `TT_Utente`.`username`
            FROM `TT_Utente`
            LEFT OUTER JOIN `TC_AnagraficaAnagrafica` ON `TC_AnagraficaAnagrafica`.`idChild` = `TT_Utente`.`idAnagrafica`
            WHERE `TC_AnagraficaAnagrafica`.`idParent` = {$loggedUser->idAnagrafica} OR `TT_Utente`.`idAnagrafica` = {$loggedUser->idAnagrafica}
            ORDER BY id ASC;
        "));
    }
    public static function get_flotte()
    {
        return 7;
    }
    public static function get_servizi()
    {
        return 7;
    }
    public static function get_anagrafiche()
    {
        /** @var TT_UtenteModel */
        $loggedUser = Auth::user();

        return DB::select(DB::raw("
            SELECT `TT_Anagrafica`.*
            FROM `TT_Anagrafica`
            INNER JOIN `TC_AnagraficaAnagrafica` ON `TC_AnagraficaAnagrafica`.`idChild` = `TT_Anagrafica`.`id`
            WHERE 1 AND `TC_AnagraficaAnagrafica`.`idParent` = {$loggedUser->idAnagrafica};
        "));
    }
}
