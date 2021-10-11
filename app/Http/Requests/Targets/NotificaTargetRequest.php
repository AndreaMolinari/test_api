<?php

namespace App\Http\Requests\Targets;

use App\Models\TT_FlottaModel;
use App\Models\TT_ServizioModel;
use App\Models\TT_TipologiaModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class NotificaTargetRequest extends FormRequest {
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
            // 'usaEmailUtente'              => 'required|boolean',
            'messaggioCustom'             => 'nullable|string|max:191',
            'flotta.id'                   => 'required_without:servizio|integer|exists:' . TT_FlottaModel::class . ',id',
            'servizio.id'                 => 'required_without:flotta|integer|exists:' . TT_ServizioModel::class . ',id',
            'tipologia.id'                => 'required|integer|exists:' . TT_TipologiaModel::class . ',id|in:' . join(",", TT_TipologiaModel::where('idParent', 120)->get()->pluck('id')->toArray()),
            'campo_notifica'              => 'required_if:useEmailUtente,false',
            'campo_notifica.contatto'     => 'required_with:campo_notifica|string|max:191',
            'campo_notifica.tipologia'    => 'required_with:campo_notifica|array',
            'campo_notifica.tipologia.id' => 'required_with:campo_notifica.tipologia|integer|exists:' . TT_TipologiaModel::class . ',id',
        ];
    }
}
