<?php

namespace App\Http\Requests\Trax;

use Illuminate\Foundation\Http\FormRequest;

class InOutRequest extends FormRequest {
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
            'label' => 'required|string',
            'newStatus' => 'required|boolean'
        ];
    }
}
