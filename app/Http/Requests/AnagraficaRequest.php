<?php

namespace App\Http\Requests;

use App\Models\{TC_AnagraficaAnagraficaModel, TT_AnagraficaModel, TT_TipologiaModel};
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AnagraficaRequest extends FormRequest
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
        $pg = 21;
        
        $return = [
            'idGenere' => 'required',
            'idGenere' => Rule::exists('TT_Tipologia', 'id')->where(function ($query) {
                $query->where('idParent', 19);
            }),
            'nome'     => 'required_without:pIva',
            'cognome'  => 'required_without:pIva',
            'ragSoc'   => 'required_with:pIva',
            'codFisc'  => 'required_unless:idGenere,'.$pg.'|unique:TT_Anagrafica,codFisc,'.$this->route('id').'|nullable',
            'pIva'     => 'required_if:idGenere,'.$pg.'|min:11|max:11|unique:TT_Anagrafica,pIva,'.$this->route('id').'|nullable',
            'idCommerciale' => 'present|exists:' . TT_AnagraficaModel::class . ',id|nullable',
            'referenteLegale' => 'present|nullable',
            'tipologia'               => 'required',
            'tipologia.*.idTipologia' => 'required|exists:' . TT_TipologiaModel::class . ',id',

            'fatturazione'              => 'present',
            'fatturazione.*.idModalita' => 'required|exists:' . TT_TipologiaModel::class . ',id',
            'fatturazione.*.idPeriodo'  => 'required|exists:' . TT_TipologiaModel::class . ',id',

            'utente'                         => 'present',
            'utente.*.idTipologia'           => 'required|exists:' . TT_TipologiaModel::class . ',id',
            'utente.*.username'              => 'required|min:3|unique:TT_Utente,username,'.$this->route('id').',idAnagrafica',
            'utente.*.password'              => 'required|min:6',
            'utente.*.password_confirmation' => 'required|same:utente.*.password',
            'utente.*.actiaUser'             => 'required_with:utente.*.actiaPassword|',
            'utente.*.actiaMail'             => 'nullable',

            'indirizzo'               => 'present',
            "indirizzo.*.nazione"     => 'required',
            "indirizzo.*.cap"         => 'required',
            "indirizzo.*.comune"      => 'required',
            "indirizzo.*.provincia"   => 'required',
            "indirizzo.*.via"         => 'required',
            "indirizzo.*.civico"      => 'required',
            "indirizzo.*.idTipologia" => 'required|exists:' . TT_TipologiaModel::class . ',id',

            'relazioni'               => 'present|nullable',
            'relazioni.*.id'          => 'nullable|exists:' . TC_AnagraficaAnagraficaModel::class . ',id',
            'relazioni.*.idParent'    => 'required|exists:' . TT_AnagraficaModel::class . ',id',
            'relazioni.*.idTipologia' => 'required|exists:' . TT_TipologiaModel::class . ',id',

            'rubrica'                          => 'present',
            'rubrica.*.nome'                   => 'required',
            'rubrica.*.recapito'               => 'required',
            'rubrica.*.recapito'               => 'present',
            'rubrica.*.recapito.*.idTipologia' => 'required|exists:' . TT_TipologiaModel::class . ',id',
            'rubrica.*.recapito.*.nome'        => 'present|nullable',
            'rubrica.*.recapito.*.contatto'    => 'required',
            'rubrica.*.recapito.*.predefinito' => 'present',

            'nota'         => 'present',
            'nota.*.testo' => 'required',
        ];

        return $return;
    }
}
