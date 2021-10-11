<?php

namespace App\Http\Requests\Trax;

use App\Models\TT_ServizioModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MesaroliRequest extends FormRequest
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
        // Se sei mesaroli o un operatore+
        if ( $user->id == 538 || $user->getRoleLevel() <= 4 )
        {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nickname'   => 'nullable|string|max:255',
            'disponente' => 'nullable|string|max:255',
            'icona'      => 'nullable|string|max:255',
        ];
    }
}
