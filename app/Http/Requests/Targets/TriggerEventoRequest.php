<?php

namespace App\Http\Requests\Targets;

use App\Models\Targets\TT_NotificaModel;
use App\Models\TT_ContattoModel;
use App\Models\TT_ServizioModel;
use App\Models\TT_TipologiaModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TriggerEventoRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        /**@var TT_UtenteModel */
        $user = Auth::user();
        return $user->getRoleLevel() <= 6;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            // 'area.id'             => 'required_without_all:soglia.id,manutenzione.id|integer|exists:' . TT_AreaModel::class . ',id',           // AREA DI QUELL'UTENTE OPPURE
            // 'soglia.id'           => 'required_without_all:soglia.id,manutenzione.id|integer|exists:' . TT_SogliaModel::class . ',id',         // SOGLIA DI QUELL'UTENTE OPPURE
            // 'manutenzione.id'     => 'required_without_all:soglia.id,manutenzione.id|integer|exists:' . TT_ManutenzioneModel::class . ',id',   // MANUTENZIONI DI QUELL'UTENTE
            'servizi'            => 'required|array',
            'servizi.*'          => 'required_with:servizio|integer|exists:' . TT_ServizioModel::class . ',id',                                                                                                       // ID DI SERVIZI ESISTENTI A CUI HA ACCESSO QUELL'UTENTE
            'evento.id'              => 'required|integer|exists:' . TT_TipologiaModel::class . ',id',
            'action'            => 'nullable|array',
            'action.id'         => 'nullable|integer|exists:' . TT_NotificaModel::class . ',id',
            'action.contatti'   => 'required_with:notifica|array',
            'action.contatti.*' => 'required_with:notifica.contatti|integer|exists:' . TT_ContattoModel::class . ',id',                    // TUTTI ID DI UN CONTATTO ESISTENTE DELL'UTENTE PER IL QUALE CREI STA ROBA
            'action.messaggioCustom'  => 'nullable|string',
            'cambiaUscita'       => 'required|boolean',
        ];
    }
}
