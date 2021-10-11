<?php

namespace App\Http\Requests;

use App\Models\TT_ManutenzioneModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ManutenzioneChiusuraRequest extends FormRequest
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
            'id' => 'required|exists:'.TT_ManutenzioneModel::class.',id',
            'data_ritiro' => 'required',
            'prezzo' => 'present|nullable|numeric',
            'ore_lavoro' => 'present|nullable|numeric',
            'custom_officina' => 'present|nullable',
        ];
    }
}
