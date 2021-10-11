<?php

namespace App\Http\Requests;

use App\Models\{TT_ModelloModel, TT_ServizioModel, TT_UtenteModel};
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ComponenteBulkRequest extends FormRequest
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
            "*.unitcode"  => 'required|unique:TT_Componente,unitcode',
            "*.idModello" => Rule::exists('TT_Modello', 'id')->where(function ($query) {
                                    $query->where('idTipologia', 10);
                                }),
            "*.sim.serial"    => 'required_with:sim.idModello|unique:TT_Sim,serial|nullable',
            "*.sim.idModello" => 'required_with:sim.serial',
            "*.sim.idModello" => Rule::exists('TT_Modello', 'id')->where(function ($query) {
                                    $query->where('idTipologia', 11);
                                })
        ];

        return $return;
    }
}
