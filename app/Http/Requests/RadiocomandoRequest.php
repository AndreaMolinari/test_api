<?php

namespace App\Http\Requests;

use App\Models\TT_SimModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RadiocomandoRequest extends FormRequest
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
            "unitcode"  => 'required|unique:TT_Componente,unitcode,'.$this->route('id'),
            "imei"      => 'nullable|unique:TT_Componente,imei,'.$this->route('id'),
            "idModello" => Rule::exists('TT_Modello', 'id')->where(function ($query) {
                                    $query->where('idTipologia', 93);
                                }),
            'nota'         => 'present',
            'nota.*.testo' => 'exclude_if:nota,null|required',

        ];
        return $return;
    }
}
