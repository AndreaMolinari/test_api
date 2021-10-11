<?php

namespace App\Http\Requests\Strange;

use Illuminate\Foundation\Http\FormRequest;

class StrangeInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // TODO solo se sei il tipo a cui serve sta roba o admin
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
            'colore' => 'nullable|string|max:255',
            'targa'  => 'nullable|string|max:255',
            'anno'   => 'integer|filled|min:1970|max:400000',
            'telaio' => 'nullable|string|max:255',
        ];
    }
}
