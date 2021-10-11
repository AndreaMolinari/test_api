<?php
namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Models\{TT_TipologiaModel};
use App\Http\Requests\TipologiaRequest;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class TipologiaController extends Controller
{
    public function get_all(string $param = null)
    {
        return TT_TipologiaModel::whereNull('idParent')->get()->load('allChildren');
    }

    public function get_id(int $id)
    {
        return TT_TipologiaModel::find($id)->load('allChildren');
    }

    public function create(TipologiaRequest $request, int $id = null)
    {
        $new = $request->validated();

        if( is_null($id) )
        {
            $new_tipologia = TT_TipologiaModel::firstOrCreate($new);
        }else{
            $new_tipologia = TT_TipologiaModel::updateOrCreate(['id' => $id], $new);
        }

        return $this->get_id($new_tipologia->id);
    }

    public function delete(int $id)
    {
        $tipologia = TT_TipologiaModel::findOrFail($id)->load('allChildren');

        if( count($tipologia->allChildren) == 0 )
        {
            $tipologia->delete();
        }else{
            return new UnprocessableEntityHttpException("Questa tipologia contiene sottotipologie, non può essere eliminata");
        }
    }
}
?>
