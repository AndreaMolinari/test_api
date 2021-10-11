<?php

namespace App\Http\Requests\Trax;

use App\Models\TT_TipologiaModel;
use Illuminate\Foundation\Http\FormRequest;

class ContattoRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'nome' => 'nullable|string',
            'contatto' => 'required|string',
            'tipologia.id' => 'required|exists:' . TT_TipologiaModel::class .  ',id',
            'predefinito' => 'required|boolean',
        ];
    }
}
