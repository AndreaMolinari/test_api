<?php

namespace App\Http\Requests\v5;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PersonalizzazioniRivenditoreRequest extends FormRequest
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
            'colorGest'   => 'nullable|string',
            'mapAvail'    => 'nullable|array',
            'logo'        => 'nullable|file',
            'platformUrl' => 'nullable|string',
        ];
    }
}
