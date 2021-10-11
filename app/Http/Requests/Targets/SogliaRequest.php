<?php

namespace App\Http\Requests\Targets;

use App\Models\TT_TipologiaModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SogliaRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        /**@var TT_UtenteModel */
        $user = Auth::user();
        return $user->getRoleLevel() <= 6;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'inizio'            => 'required|string',
            'fine'              => 'required|string',
            'tipologia.id'      => 'required|integer|exists:' . TT_TipologiaModel::class . ',id',
        ];
    }
}
