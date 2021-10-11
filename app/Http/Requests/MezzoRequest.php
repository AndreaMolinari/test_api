<?php

namespace App\Http\Requests;

use App\Models\{TT_MezzoModel};
use App\Repositories\iHelpU;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MezzoRequest extends FormRequest
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
            'id'         => 'nullable|exists:' . TT_MezzoModel::class . ',id',
            'idModello'  => ['required', Rule::exists('TT_Modello', 'id')->where(function ($query) {
                $parents = array_keys((new iHelpU)->groupBy(DB::select('SELECT `id` FROM `TT_Tipologia` WHERE idParent = 64', [1]), 'id'));
                $query->whereIn('idTipologia', $parents);
            })],
            'targa'      => 'required_without:telaio|nullable|unique:TT_Mezzo,targa,'.$this->route('id'),
            'telaio'     => 'required_without:targa|nullable|unique:TT_Mezzo,telaio,'.$this->route('id'),
            'colore'     => 'nullable',
            'anno'       => 'nullable',
            'km_totali'  => 'nullable',
            'ore_totali' => 'nullable',
            'nota'       => 'nullable',
            'nota.*.id'    => 'nullable',
            'nota.*.testo' => 'required_with:nota',
        ];

        return $return;
    }
}
