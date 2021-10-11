<?php

namespace App\Http\Controllers\v4\Targets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Targets\NotificaTargetRequest;
use App\Models\Targets\TT_AreaModel;
use App\Models\Targets\TT_NotificaTargetModel;
use App\Models\TT_CampoAnagraficaModel;
use Illuminate\Support\Facades\Auth;

/** @deprecated */
class NotificaAreaController extends Controller {

    private function manage_campo_notifica(TT_NotificaTargetModel $notifica, array $data) {
        $data['anagrafica'] = ['id' => Auth::user()->idAnagrafica];
        /**@var TT_CampoAnagraficaModel */
        $campo_notifica = TT_CampoAnagraficaModel::firstOrNew([
            'idAnagrafica' => $data['anagrafica']['id'],
            'nome' => $data['contatto'],
            'idTipologia' => $data['tipologia']['id'],
        ], $data);

        $campo_notifica->tipologia()->associate($data['tipologia']['id']);
        $campo_notifica->anagrafica()->associate($data['anagrafica']['id']);

        $campo_notifica->save();

        $notifica->campo_notifica()->associate($campo_notifica->id);
    }

    public function get_all(int $idArea) {
        /**@var TT_AreaModel */
        $area = TT_AreaModel::findOrFail($idArea);
        return $area->notifiche()->with([
            'campo_notifica.tipologia',
            'tipologia',
            'trigger',
            'observable',
        ])->orderBy('updated_at', 'DESC')->get();
    }

    public function create(NotificaTargetRequest $request, int $idArea) {
        $data = $request->validated();

        /**@var TT_AreaModel */
        $area = TT_AreaModel::findOrFail($idArea);

        /**@var TT_NotificaTargetModel */
        $notifica = TT_NotificaTargetModel::make($data);
        $notifica->trigger_id = $area->id;
        $notifica->trigger_type = 'TT_Area';
        $notifica->idOperatore = Auth::id();

        if (isset($data['flotta'])) {
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

    public function get(int $idArea, int $idNotifica) {
        return TT_NotificaTargetModel::findOrFail($idNotifica)->load([
            'campo_notifica.tipologia',
            'tipologia',
            'trigger',
            'observable',
        ]);
    }

    public function update(NotificaTargetRequest $request, int $idArea, int $idNotifica) {
        $data = $request->validated();

        /**@var TT_AreaModel */
        $area = TT_AreaModel::findOrFail($idArea);

        /**@var TT_NotificaTargetModel */
        $notifica = TT_NotificaTargetModel::findOrFail($idNotifica);

        if (isset($data['flotta'])) {
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

        $notifica->update($data);

        return response()->noContent();
    }

    public function delete(int $idArea, int $idNotifica) {
        /**@var TT_NotificaTargetModel */
        $notifica = TT_NotificaTargetModel::findOrFail($idNotifica);
        $notifica->delete();
        return response()->noContent();
    }
}
