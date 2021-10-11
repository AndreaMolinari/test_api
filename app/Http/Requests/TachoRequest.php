<?php

namespace App\Http\Requests;

use App\Models\TT_SimModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TachoRequest extends FormRequest
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
                                    $query->where('idTipologia', 92);
                                }),
            "sim"           => 'present',
            "sim.id"        => 'exclude_if:sim,null|nullable|required_without:sim.serial|exists:' . TT_SimModel::class . ',id|unique:TT_Componente,idSim,'.$this->route('id').'|unique:TC_ServizioComponente,idSim,id',
            "sim.serial"    => 'exclude_if:sim,null|required_without:sim.id|unique:TT_Sim,serial|nullable',
            "sim.idModello" => 'exclude_if:sim,null|required_with:sim.serial|nullable',
            "sim.idModello" => Rule::exists('TT_Modello', 'id')->where(function ($query) {
                                    $query->where('idTipologia', 11);
                                }),
            'nota'         => 'present',
            'nota.*.testo' => 'exclude_if:nota,null|required',

        ];
        return $return;
    }
}
