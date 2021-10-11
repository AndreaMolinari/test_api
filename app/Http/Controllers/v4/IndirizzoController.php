<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Models\{TT_IndirizzoModel};
use App\Http\Requests\IndirizzoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IndirizzoController extends Controller
{
    public static function get_all()
    {
        return TT_IndirizzoModel::all();
    }

    public static function get_id(int $id)
    {
        return TT_IndirizzoModel::find($id)->load('note');
    }

    public static function find_cap(string $cap)
    {
        return DB::select(DB::raw("SELECT `italy_cities`.*, 'IT' as `nazione`
        FROM `italy_cities`
        LEFT OUTER JOIN `italy_multicap` ON `italy_cities`.`istat` = `italy_multicap`.`istat`
        LEFT OUTER JOIN `italy_cap` ON `italy_cities`.`istat` = `italy_cap`.`istat`
        WHERE 1 AND `italy_cap`.`cap` LIKE '{$cap}' OR `italy_multicap`.`cap` = '{$cap}';"));
    }

    public static function create(IndirizzoRequest $request, int $id = null)
    {
        $request->validated();
        $indirizzo = $request->except('nota');
        $indirizzo['idOperatore'] = Auth::user()->id;

        if( is_null($id) )
        {
            $tt_indirizzo = TT_IndirizzoModel::firstOrCreate($indirizzo);
        }else{
            $tt_indirizzo = TT_IndirizzoModel::updateOrCreate(['id' => $id], $indirizzo);
        }

        AnnotazioneController::sync('TT_Indirizzo', $tt_indirizzo->id, $request->only('nota')['nota']);

        return self::get_id($tt_indirizzo->id);
    }
}
