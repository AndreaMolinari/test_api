<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class IndirizzoRequest extends FormRequest
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
            'istat'     => 'nullable',
            'provincia' => 'required|min:2|max:2',
            'nazione'   => 'required|min:2|max:2',
            'comune'    => 'required',
            'cap'       => 'required|min:5|max:5',
            'via'       => 'required',
            'civico'    => 'required',
            'bloccato'  => 'nullable|boolean',
            'nota'      => 'present',
            'nota.*.testo' => 'exclude_if:nota,null|required',
        ];
    }
}
