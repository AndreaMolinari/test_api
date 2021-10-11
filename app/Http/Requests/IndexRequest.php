<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest {
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
        $rules = [
            'operator' => [
                Rule::requiredIf(function () {
                    return Str::contains($this->path(), 'search');
                }),
                'string',
            ],
            'per_page' => 'integer',
            'page'     => 'integer',
        ];

        return $rules;
    }
}
