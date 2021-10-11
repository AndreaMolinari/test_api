<?php

namespace App\Http\Controllers\v4\Targets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Targets\TargetRequest;
use App\Models\Targets\TT_AreaModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TargetController extends Controller {

    public function valid(Request $request, int $idUtente = null) {
        $idUtente = Auth::user()->getRoleLevel() > 4 ? Auth::id() : $idUtente ?? Auth::id();

        // Qui id utente non è mai null
        $request->validate([
            'id' => ['nullable', Rule::exists('TT_Area', 'id')->where('idUtente', $idUtente)],
            'nome' => ['required', 'string'],
        ]);

        $builder = TT_AreaModel::withTrashed()->where('idUtente', $idUtente)->where('nome', $request->nome);

        if ($request->id ?? false)
            $builder->whereKeyNot($request->id);

        $area = $builder->first();

        return !!$area ? response()->json(['message' => 'Nome già utilizzato'], 422) : response()->noContent();
    }

    public function get_all(string $scope = null, int $idScoped = null) {
        //? Prendo di default una flotta o la lascio required
        if (!$idScoped && (!$scope || $scope === 'utente')) $idUtente = Auth::user()->getRoleLevel() > 4 ? Auth::id() : $idUtente ?? Auth::id();

        $builder = TT_AreaModel::with('triggers_evento');

        switch ($scope) {
            case 'utente':
                $builder->utente($idScoped);
                break;
            case 'flotta':
                $builder->flotta($idScoped);
                break;
            case 'servizio':
                $builder->servizio($idScoped);
                break;
        }

        return $builder->orderBy('updated_at', 'DESC')->get();
    }

    public function get(int $idArea) {
        return TT_AreaModel::findOrFail($idArea)->load('triggers_evento');
    }

    public function create(TargetRequest $request, int $idUtente = null) {
        $idUtente = Auth::user()->getRoleLevel() > 4 ? Auth::id() : $idUtente ?? Auth::id();

        $data = $request->validated();

        /**@var TT_AreaModel */
        $area = TT_AreaModel::make($data);
        $area->gruppo = 'WebTrax';
        $area->idUtente = $idUtente;
        $area->idOperatore = Auth::id();

        $area->save();

        return $area;
    }

    public function update(TargetRequest $request, int $idArea) {
        /**@var TT_AreaModel */
        $area = TT_AreaModel::findOrFail($idArea);

        $data = $request->validated();

        $area->update($data);

        return response()->noContent();
    }

    public function delete(int $idArea) {
        /**@var TT_AreaModel */
        $area = TT_AreaModel::findOrFail($idArea);

        $area->delete();

        return response()->noContent();
    }
}
