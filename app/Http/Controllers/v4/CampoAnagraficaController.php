<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Http\Requests\DDTRequest;
use App\Models\TT_CampoAnagraficaModel;
use App\Models\TT_DDTModel;
use App\Models\TT_UtenteModel;
use Illuminate\Support\Facades\Auth;

class CampoAnagraficaController extends Controller {

    public function getByTipologiaUtente(int $idTipologia, int $idUtente) {
        return TT_CampoAnagraficaModel::where('idTipologia', $idTipologia)
            ->where(
                'idAnagrafica',
                TT_UtenteModel::findOrFail($idUtente)->idAnagrafica ?? 0
            )
            ->get();
    }

    // public function store() {
    // }

    public function destroy(int $idCampoAnagrafica) {

        TT_CampoAnagraficaModel::findOrFail($idCampoAnagrafica)->delete();

        return response()->noContent();
    }
}
