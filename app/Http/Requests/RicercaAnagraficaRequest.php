<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RicercaAnagraficaRequest extends FormRequest
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
        $return = [
            'id'          => 'nullable|string',
            'nome'        => 'nullable|string',
            'pIva'        => 'nullable|string',
            'utente'      => 'nullable|string',
            'tipologia'   => 'nullable|string',
            'genere'      => 'nullable|string',
            'iban'        => 'nullable|string',
            'contatto'    => 'nullable|string',
            'rivenditore' => 'nullable|string'
        ];

        return $return;
    }
}
