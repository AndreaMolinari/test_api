<?php
namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Models\{TT_BrandModel, TT_ModelloModel, TT_TipologiaModel};
use App\Http\Requests\BrandRequest;
use Facades\App\Repositories\iHelpU;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class BrandController extends Controller
{
    public function get_all(string $param = null, int $idTipologia = null)
    {
        switch($param)
        {
            case "tipologia":
                $tipologie = array_keys(iHelpU::groupBy(TT_TipologiaModel::find($idTipologia)->children, 'id'));
                $tipologie[] = $idTipologia;

                $brands_vergini   = TT_BrandModel::has('modelli', 0)->get()->merge(
                    TT_BrandModel::whereIn('id', array_keys(iHelpU::groupBy(TT_ModelloModel::select('idBrand')->whereIn('idTipologia', $tipologie)->get(), 'idBrand')))->get()
                )->load('modelli');
                return $brands_vergini;
            break;
            default:
                return TT_BrandModel::all();
            break;
        }
    }

    public function get_id(int $id)
    {
        return TT_BrandModel::find($id)->load('modelli');
    }

    public function create(BrandRequest $request, int $id = null)
    {
        $new = $request->validated();

        if( is_null($id) )
        {
            return TT_BrandModel::create($new);
        }else{
            TT_BrandModel::findOrFail($id)->update($new);
            return TT_BrandModel::find($id);
        }
    }

    public function delete(int $id = null)
    {
        $brand = TT_BrandModel::findOrFail($id)->load('modelli');
        if( count($brand->modelli) != 0 )
        {
            throw new UnprocessableEntityHttpException("Non me la sento, ci sono dei modelli!");
        }else{
            return $brand->delete();
        }
    }

    public function sanitize() {
        $count = 0;
        foreach( TT_BrandModel::doesntHave('modelli')->get() as $brand )
        {
            $brand->delete();
            $count+= 1;
        }
        return $count;
    }
}
