<?php

namespace App\Http\Requests\Trax;

use Illuminate\Foundation\Http\FormRequest;

class ResolveMezzoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'km_totali'  => 'required|numeric',
            'ore_totali' => 'required|numeric',
        ];
    }
}
