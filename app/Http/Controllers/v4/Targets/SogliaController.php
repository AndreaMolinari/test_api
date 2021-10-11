<?php

namespace App\Http\Controllers\v4\Targets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Targets\SogliaRequest;
use App\Models\Targets\TT_SogliaModel;
use Illuminate\Support\Facades\Auth;

class SogliaController extends Controller {

    public function get_all(int $idUtente = null) {
        //? Prendo di default una flotta o la lascio required
        $idUtente = Auth::user()->getRoleLevel() > 4 ? Auth::id() : $idUtente ?? Auth::id();

        $builder = TT_SogliaModel::where('idUtente', $idUtente)->with(['triggers_evento', 'tipologia']);

        return $builder->orderBy('updated_at', 'DESC')->get();
    }

    public function get(int $idSoglia) {
        return TT_SogliaModel::findOrFail($idSoglia)->load(['triggers_evento', 'tipologia']);
    }

    public function create(SogliaRequest $request, int $idUtente = null) {
        $idUtente = Auth::user()->getRoleLevel() > 4 ? Auth::id() : $idUtente ?? Auth::id();

        $data = $request->validated();

        /**@var TT_SogliaModel */
        $soglia = TT_SogliaModel::make($data);
        $soglia->idUtente = $idUtente;
        $soglia->idOperatore = Auth::id();
        $soglia->idTipologia = $data['tipologia']['id'];

        $soglia->save();

        return $soglia->load('tipologia');
    }

    public function update(SogliaRequest $request, int $idSoglia) {
        /**@var TT_SogliaModel */
        $soglia = TT_SogliaModel::findOrFail($idSoglia);

        $data = $request->validated();

        $soglia->update($data);

        return response()->noContent();
    }

    public function delete(int $idSoglia) {
        /**@var TT_SogliaModel */
        $soglia = TT_SogliaModel::findOrFail($idSoglia);

        $soglia->delete();

        return response()->noContent();
    }
}
