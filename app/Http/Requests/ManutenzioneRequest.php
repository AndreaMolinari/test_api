<?php

namespace App\Http\Requests;

use App\Models\{TT_ServizioModel, TT_TipologiaModel};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ManutenzioneRequest extends FormRequest
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
        $return = [
            'idServizio' => 'required_without:servizio|exists:'.TT_ServizioModel::class.',id',
            'servizio' => 'required_without:idServizio',
            'servizio.*.id' => 'required|exists:'.TT_ServizioModel::class.',id',
            'idTipologia' => 'required|exists:'.TT_TipologiaModel::class.',id',
            'campo_anagrafica_tipologia.nome' => 'required',

            'giorno_start' => 'required_with:giorni_intervallo|required_without_all:ore_start,km_start',
            // 'giorni_end' => 'required_with:giorno_start',
            'giorni_intervallo' => 'required_with:giorno_start',
            'giorni_preavviso' => 'presence|integer',

            'ore_start' => 'required_with:ore_intervallo|required_without_all:giorno_start,km_start',
            'ore_intervallo' => 'required_with:ore_start',
            'ore_preavviso' => 'presence|integer',

            'km_start' => 'required_with:km_intervallo|required_without_all:ore_start,giorno_start',
            'km_intervallo' => 'required_with:km_start',
            'km_preavviso' => 'presence|integer',

            'campo_anagrafica_email.nome' => 'required_with:campo_anagrafica_email|email:rfc',
            'nota.*.testo' => 'required',

            // 'data_ritiro' => 'required|date:Y-m-d',
            // 'campo_anagrafica_officina.nome' => 'required',
        ];

        // if( ! $this->isMethod('post') )
        // {
        //     $return['id'] = 'required|exists:'.TT_ManutenzioneModel::class.',id';
        // }

        return $return;
    }

    public function messages()
    {
        return [
            'required' => 'Il campo `:attribute` è obbligatorio.',
            'required_with' => 'Il campo `:attribute` è obbligatorio se `:values` è presente',
            'servizio.required' => 'è richiesto almeno un servizio',
            'id.required' => 'Per modificare una manutenzione il campo `:attribute` è richiesto',
            // 'idTipologia.required' => 'La tipologia di manutenzione è obbligatoria',
            // 'email_notifica.required' => 'Un indirizzo email è necessaria per ottenere le comunicazioni',
            // 'data_ritiro.required' => 'La data ritiro è necessaria per confermare una manutenzione',
        ];
    }
}
