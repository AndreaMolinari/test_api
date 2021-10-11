<?php

namespace App\Http\Controllers\v4\Targets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Targets\NotificaSogliaRequest;
use App\Models\Targets\TT_NotificaTargetModel;
use App\Models\Targets\TT_SogliaModel;
use App\Models\TT_CampoAnagraficaModel;
use App\Models\TT_TipologiaModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** @deprecated */
class NotificaSogliaController extends Controller {

    private function manage_campo_notifica(TT_NotificaTargetModel $notifica, array $data) {
        $data['anagrafica'] = ['id' => Auth::user()->idAnagrafica];
        /**@var TT_CampoAnagraficaModel */
        $campo_notifica = TT_CampoAnagraficaModel::firstOrNew([
            'idAnagrafica' => $data['anagrafica']['id'],
            'nome'         => $data['contatto'],
            'idTipologia'  => $data['tipologia']['id'],
        ], $data);

        $campo_notifica->tipologia()->associate($data['tipologia']['id']);
        $campo_notifica->anagrafica()->associate($data['anagrafica']['id']);

        $campo_notifica->save();

        $notifica->campo_notifica()->associate($campo_notifica->id);
    }

    private function manage_soglia(array $data) {
        /**@var TT_SogliaModel */
        $soglia = TT_SogliaModel::firstOrNew([
            'id' => $data['id'] ?? null,
            'inizio' => $data['inizio'],
            'fine' => $data['fine'],
            'idTipologia' => $data['tipologia']['id'],
        ], $data);

        $soglia->tipologia()->associate($data['tipologia']['id']);
        $soglia->utente()->associate($data['utente']['id']);

        $soglia->save();

        return $soglia;
    }

    public function get_all(int $idUtente = null) {
        return TT_NotificaTargetModel::whereHasMorph('trigger', TT_SogliaModel::class, function (Builder $has) use($idUtente) {
            return $has->where('idUtente', $idUtente ?? Auth::id());
        })->with([
            'campo_notifica.tipologia',
            'tipologia',
            'trigger.tipologia',
            'observable',
        ])->get();
    }

    public function create(NotificaSogliaRequest $request, int $idUtente = null) {
        $data = $request->validated();

        $data['soglia']['utente'] = ['id' => $idUtente ?? Auth::id()];

        $soglia = $this->manage_soglia($data['soglia']);

        /**@var TT_NotificaTargetModel */
        $notifica = TT_NotificaTargetModel::make($data);
        $notifica->trigger_id = $soglia->id;
        $notifica->trigger_type = 'TT_Soglia';
        $notifica->idOperatore = Auth::id();

        if ($data['flotta']) {
            $notifica->observable_type = 'TT_Flotta';
            $notifica->observable_id = $data['flotta']['id'];
        } else {
            $notifica->observable_type = 'TT_Servizio';
            $notifica->observable_id = $data['servizio']['id'];
        }

        if ($data['usaEmailUtente'] === false)
            $this->manage_campo_notifica($notifica, $data['campo_notifica']);
        else
            $notifica->campo_notifica()->dissociate();

        $notifica->tipologia()->associate($data['tipologia']['id']);

        $notifica->save();

        return $notifica;
    }

    public function get(int $idNotifica) {
        return TT_NotificaTargetModel::where('id', $idNotifica)->with([
            'campo_notifica.tipologia',
            'tipologia',
            'trigger.tipologia',
            'observable',
        ])->get();
        // return TT_NotificaTargetModel::findOrFail($idNotifica);
    }

    public function update(Request $request, int $idNotifica) {
        $data = $request->validate([
            'tipologia.id'               => 'required_with:tipologia|integer|exists:' . TT_TipologiaModel::class . ',id|in:' . join(",", TT_TipologiaModel::where('idParent', 120)->get()->pluck('id')->toArray()),
            'messaggioCustom'            => 'nullable|string|max:191',
            'usaEmailUtente'             => 'required|boolean',
            'campo_notifica'              => 'required_if:useEmailUtente,false',
            'campo_notifica.contatto'     => 'required_with:campo_notifica|string|max:191',
            'campo_notifica.tipologia'    => 'required_with:campo_notifica|array',
            'campo_notifica.tipologia.id' => 'required_with:campo_notifica.tipologia|integer|exists:' . TT_TipologiaModel::class . ',id',
            'soglia'                     => 'required|array',
            'soglia.id'                  => 'required_with:soglia|integer|exists:' . TT_SogliaModel::class . ',id',
            'soglia.inizio'              => 'required_with:soglia|number',
            'soglia.fine'                => 'required_with:soglia|number',
            'soglia.tipologia'           => 'required_with:soglia|array',
            'soglia.tipologia.id'        => 'required_with:soglia.tipologia|integer|exists:' . TT_TipologiaModel::class . ',id|in:' . join(",", TT_TipologiaModel::where('idParent', 115)->get()->pluck('id')->toArray()),
        ]);

        $soglia = $this->manage_soglia($data['soglia']);

        /**@var TT_NotificaTargetModel */
        $notifica = TT_NotificaTargetModel::findOrFail($idNotifica);
        $notifica->trigger_id = $soglia->id;
        $notifica->trigger_type = 'TT_Soglia';

        if ($data['usaEmailUtente'] === false)
            $this->manage_campo_notifica($notifica, $data['campo_notifica']);
        else
            $notifica->campo_notifica()->dissociate();

        $notifica->tipologia()->associate($data['tipologia']['id']);

        $notifica->update($data);

        return response()->noContent();
    }

    public function delete(int $idNotifica) {
        /**@var TT_NotificaTargetModel */
        $notifica = TT_NotificaTargetModel::findOrFail($idNotifica);
        $notifica->delete();
        return response()->noContent();
    }
}
