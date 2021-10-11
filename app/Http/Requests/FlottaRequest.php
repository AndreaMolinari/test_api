<?php

namespace App\Http\Requests;

use App\Models\{TT_ServizioModel, TT_UtenteModel};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class FlottaRequest extends FormRequest
{
    public function authorize()
    {
        /**@var TT_UtenteModel */
        $user = Auth::user();
        if ($user->getRoleLevel() <= 5) {
            return true;
        } else {
            return false;
        }
    }

    public function rules()
    {
        $return = [
            'nome'        => 'required',
            'descrizione' => 'present|nullable',
            'defaultIcon' => 'present|nullable',

            'utente'            => 'present',
            'utente.*.idUtente' => 'required|exists:' . TT_UtenteModel::class . ',id',
            'utente.*.nickname' => 'present|nullable',
            'utente.*.principale' => 'present|nullable|boolean',

            'servizio'              => 'present',
            'servizio.*.idServizio' => 'required|exists:' . TT_ServizioModel::class . ',id',
            'servizio.*.nickname'   => 'present|nullable'
        ];

        return $return;
    }
}
