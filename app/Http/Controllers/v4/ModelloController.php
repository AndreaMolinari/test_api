<?php
namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Models\{TT_BrandModel, TT_ModelloModel, TT_TipologiaModel};
use App\Http\Requests\ModelloRequest;
use Facades\App\Repositories\iHelpU;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ModelloController extends Controller
{
    public function get_all(string $param = null, int $id = null)
    {
        switch($param)
        {
            case "tipologia":
                $tipologie = array_keys(iHelpU::groupBy(TT_TipologiaModel::findOrFail($id)->children, 'id'));
                $tipologie[] = $id;
                return TT_ModelloModel::whereIn('idTipologia', $tipologie)->get()->load('tipologia')->makeHidden(['idTipologia', 'idBrand']);
            break;
            default:
                return TT_ModelloModel::all()->load('tipologia')->makeHidden(['idTipologia', 'idBrand']);
            break;
        }
    }

    public function get_id(int $id)
    {
        return TT_ModelloModel::findOrFail($id);
    }

    public function create(ModelloRequest $request, int $id = null)
    {
        $new = $request->validated();
        $new['idOperatore'] = Auth::user()->id;
        if( !is_null($id) )
        {
            TT_ModelloModel::findOrFail($id)->update($new);
            return TT_ModelloModel::find($id);
        }else{
            return TT_ModelloModel::create($new);
        }
    }

    public function delete(int $id)
    {
        $modello = TT_ModelloModel::findOrFail($id)->load('componente', 'mezzo', 'sim');
        if( count($modello->componente) == 0 && count($modello->mezzo) == 0 && count($modello->sim) == 0 )
        {
            return $modello->delete();
        }else{
            throw new UnprocessableEntityHttpException('Non lo elimino perchè qualcosa lo usa!');
        }
    }

    public function sanitize() {
        $count = 0;
        foreach( TT_ModelloModel::doesntHave('componente')->doesntHave('sim')->doesntHave('mezzo')->get() as $modello )
        {
            $modello->delete();
            $count+= 1;
        }
        return $count;
    }
}
