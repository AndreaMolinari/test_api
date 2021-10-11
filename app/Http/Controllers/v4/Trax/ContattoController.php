<?php

namespace App\Http\Controllers\v4\Trax;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trax\ContattoRequest;
use App\Models\ContattoModel;
use App\Models\TT_UtenteModel;
use Illuminate\Support\Facades\Auth;

class ContattoController extends Controller {
    public function index(int $idUtente = null) {
        /** @var TT_UtenteModel */
        $authUser = Auth::user();
        $idUtente = $authUser->getRoleLevel() <= 4 ? ($idUtente ?? $authUser->id) : $authUser->id;
        $utente = TT_UtenteModel::findOrFail($idUtente);

        return $utente->contatti()->with('tipologia')->get();
    }

    public function store(ContattoRequest $request, int $idUtente = null) {
        /** @var TT_UtenteModel */
        $authUser = Auth::user();
        $idUtente = $authUser->getRoleLevel() <= 4 ? ($idUtente ?? $authUser->id) : $authUser->id;
        $utente = TT_UtenteModel::findOrFail($idUtente);

        $data = $request->validated();
        $contatto = ContattoModel::make($data);
        $contatto->idOperatore = $authUser->id;
        $contatto->idUtente = $utente->id;
        $contatto->idAnagrafica = $utente->idAnagrafica;
        $contatto->tipologia()->associate($data['tipologia']['id']);

        $contatto->save();

        return $contatto;
    }

    public function update(ContattoRequest $request, int $idContatto) {
        $contatto = ContattoModel::findOrFail($idContatto);
        $data = $request->validated();

        $contatto->idTipologia = $data['tipologia']['id'];
        $contatto->update($data);

        return response()->noContent();
    }

    public function destroy(int $idContatto) {
        $contatto = ContattoModel::findOrFail($idContatto);
        $contatto->delete();
        return response()->noContent();
    }
}
