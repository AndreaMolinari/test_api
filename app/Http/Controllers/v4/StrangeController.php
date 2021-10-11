<?php
namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Http\Requests\Strange\StrangeInfoRequest;
use App\Models\TT_ServizioModel;
use Facades\App\Repositories\iHelpU;

class StrangeController extends Controller
{
    public function get_info_periferica(int $idServizio) {
        // return (new TorinoController)->
    }

    public function set_info_periferica(StrangeInfoRequest $request, int $idServizio) {
        $servizio = TT_ServizioModel::findOrFail($idServizio);
        $val_data = $request->validated();

        (new TorinoController)->vehicle(iHelpU::mkRequest([
            'plate' => $val_data['targa'] ?? null,
            'chassis' => $val_data['telaio'] ?? null,
            'color' => $val_data['colore'] ?? null,
            'year' => $val_data['anno'] ?? null,
        ], Auth::id()), $idServizio);

        return response()->noContent(); // 204 success but no response body
    }
}
