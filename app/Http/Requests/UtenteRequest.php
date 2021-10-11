<?php

namespace App\Http\Requests;

use App\Models\{TT_AnagraficaModel, TT_TipologiaModel, TT_UtenteModel};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UtenteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id'                    => 'nullable|exists:' . TT_UtenteModel::class . ',id',
            'idAnagrafica'          => 'nullable|exists:' . TT_AnagraficaModel::class . ',id',
            'idTipologia'           => 'required|exists:' . TT_TipologiaModel::class . ',id',
            'email'                 => 'nullable|email',
            'username'              => 'required|min:3|unique:TT_Utente,username,'.$this->route('id').',id',
            'password'              => 'required|min:6',
            'password_confirmation' => 'required|same:password',
            'actiaUser'             => 'required_with:actiaPassword',
            'actiaMail'             => 'required_with:actiaUser',
            'actiaPassword'         => 'nullable|email',
            'dataStart'             => 'nullable|date',
            'dataEnd'               => 'nullable|date|after:dataStart',
            'bloccato'              => 'nullable|boolean',
        ];
    }
}
