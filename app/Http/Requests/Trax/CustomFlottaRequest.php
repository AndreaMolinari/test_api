<?php

namespace App\Http\Requests\Trax;

use App\Models\TT_ServizioModel;
use Illuminate\Foundation\Http\FormRequest;

class CustomFlottaRequest extends FormRequest
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
            'idServizio' => 'required|exists:'.TT_ServizioModel::class.',id',
            'nickname'   => 'nullable|string|max:255',
            'icona'      => 'present|string|max:255',
        ];
    }
}
