<?php

namespace App\Http\Requests\v5;

use App\Models\v5\{Anagrafica, Tipologia};
use Illuminate\Foundation\Http\FormRequest;
use Utente;

class StoreUtenteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $return = [
            'id'                    => 'nullable|exists:' . Utente::class . ',id',
            'tipologia.id'          => 'required|exists:' . Tipologia::class . ',id|in:' . join(",", Tipologia::where('idParent', 2)->get()->pluck('id')->toArray()),
            'email'                 => 'nullable|email',
            'username'              => 'required|min:3|unique:TT_Utente,username,'.$this->route('utente.id').'',
            'password'              => 'required|min:6',
            'password_confirmation' => 'required|same:password',
            'actiaUser'             => 'required_with:actiaPassword',
            'actiaMail'             => 'required_with:actiaUser',
            'actiaPassword'         => 'nullable|required_with:actiaUser',
            'dataStart'             => 'nullable|date',
            'dataEnd'               => 'nullable|date|after:dataStart',
            'bloccato'              => 'nullable|boolean',
        ];
        if( !$this->anagrafica ){
            $return['anagrafica']    = 'present';
            $return['anagrafica.id'] = 'required_with:anagrafica|exists:' . Anagrafica::class . ',id';
        }

        return $return;
    }
}
