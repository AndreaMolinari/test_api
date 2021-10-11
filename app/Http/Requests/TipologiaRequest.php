<?php

namespace App\Http\Requests;

use App\Models\{TT_TipologiaModel};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TipologiaRequest extends FormRequest
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
            'id'          => 'nullable',
            'tipologia'   => 'required|unique:TT_Tipologia,tipologia,'.$this->route('id').'',
            'descrizione' => 'nullable',
            'idParent'    => 'nullable|exists:' . TT_TipologiaModel::class . ',id',
        ];

        return $return;
    }
}
