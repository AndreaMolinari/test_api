<?php

namespace App\Http\Requests\v5;

use App\Models\v5\{Anagrafica, Tipologia};
use Illuminate\Foundation\Http\FormRequest;

class StoreAnagraficaRequest extends FormRequest
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
        $return = [
            'nome'            => 'nullable|required_without:pIva',
            'cognome'         => 'nullable|required_without:pIva',
            'ragSoc'          => 'nullable|required_with:pIva',
            'codFisc'         => 'nullable|required_without:pIva|unique:TT_Anagrafica,codFisc,'.$this->route('anagrafica.id').'|nullable',
            'pIva'            => 'nullable|required_without:codFisc|unique:TT_Anagrafica,pIva,'.$this->route('anagrafica.id').'|nullable',
            'genere.id'       => 'required|exists:' . Tipologia::class . ',id|in:' . join(",", Tipologia::where('idParent', 19)->get()->pluck('id')->toArray()),
            'idCommerciale'   => 'nullable|exists:' . Anagrafica::class . ',id|nullable',
            'referenteLegale' => 'nullable',
        ];

        return $return;
    }
}
