<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Models\{TT_MezzoModel};
use App\Http\Requests\{MezzoRequest};
use App\Repositories\iHelpU;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class MezzoController extends Controller
{
    public static function make_list($list)
    {
        $brands = (new iHelpU())->groupBy(DB::select('SELECT `id`, `marca` FROM `TT_Brand` WHERE 1 ORDER BY id DESC', [1]), 'id');
        $modelli = (new iHelpU())->groupBy(DB::select('SELECT `id`, `idBrand`, `idTipologia`, `modello` FROM `TT_Modello` WHERE 1 ORDER BY id DESC', [1]), 'id');

        $mezzi = [];
        foreach($list as $row)
        {
            $tmp = (object) clone($row);
            $tmp->modello = ( array_key_exists($tmp->idModello, $modelli) ) ? $modelli[$tmp->idModello][0] : null;
            if( !is_null($tmp->modello) )
            {
                $tmp->modello->brand = ( array_key_exists($tmp->modello->idBrand, $brands) ) ? $brands[$tmp->modello->idBrand][0] : null;
            }
            unset($tmp->idModello);
            $mezzi[] = $tmp;
        }

        return $mezzi;
    }

    public static function get_all()
    {
        return static::make_list(DB::select('SELECT `id`, `idModello`, `targa`, `telaio`, `colore`, `anno`, `info`, `km_totali`, `ore_totali` FROM `TT_Mezzo` WHERE 1 ORDER BY id DESC', [1]));
    }

    public static function get_id(int $id)
    {
        return TT_MezzoModel::findOrFail($id)->load('note');
    }

    public static function non_associato(int $idServizio = null)
    {
        $coda = (!is_null($idServizio)) ? ' AND `TT_Servizio`.`id` !=  '.$idServizio : '';

        $lista = DB::select('SELECT `TT_Mezzo`.`id`, `idModello`, `targa`, `telaio`, `colore`, `anno`, `info`, `km_totali`, `ore_totali`
        FROM `TT_Mezzo`
        WHERE 1
        AND `id` NOT IN (
            SELECT `TC_ServizioComponente`.`idMezzo`
            FROM `TT_Servizio`
            RIGHT OUTER JOIN `TC_ServizioComponente` ON `TC_ServizioComponente`.`idServizio` = `TT_Servizio`.`id`
            WHERE `TT_Servizio`.`dataInizio` <= now()
            AND (`TT_Servizio`.`dataFine` IS NULL OR `TT_Servizio`.`dataFine` >= now())
            AND `TC_ServizioComponente`.`idMezzo` IS NOT NULL
            '.$coda.'
        )
        ORDER BY `TT_Mezzo`.`id` DESC'
        , [1]);
        return static::make_list($lista);
    }

    public function create(MezzoRequest $request, int $id = null)
    {
        $request->validated();
        $req = $request->except('nota');
        $req['idOperatore'] = Auth::id();

        $nota = (!is_array($request->only('nota')['nota']) || count($request->only('nota')['nota']) < 1) ? [] : $request->only('nota')['nota'];

        if( !is_null($id) )
        {
            $mezzo = TT_MezzoModel::updateOrCreate(['id' => $id], $req);
        }else{
            $mezzo = TT_MezzoModel::create($req);
        }

        AnnotazioneController::sync('TT_Mezzo', $mezzo->id, $nota);

        return static::get_id($mezzo->id);
    }

    public function delete(int $id)
    {
        $mezzo = TT_MezzoModel::findOrFail($id)->loadCount('servizi');
        if( $mezzo->servizi_count == 0 )
        {
            return $mezzo->delete();
        }else{
            throw new UnprocessableEntityHttpException("Il mezzo è associato ad uno o più servizi");
        }
    }

    public function sanitize() {
        $count = 0;
        foreach( TT_MezzoModel::doesntHave('servizi')->get() as $mezzo )
        {
            $mezzo->delete();
            $count+= 1;
        }
        return $count;
    }
}
