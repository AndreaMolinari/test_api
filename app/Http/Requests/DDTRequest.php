<?php

namespace App\Http\Requests;

use App\Models\TT_AnagraficaModel;
use App\Models\TT_AnnotazioneModel;
use App\Models\TT_ComponenteModel;
use App\Models\TT_DDTModel;
use App\Models\TT_IndirizzoModel;
use App\Models\TT_SimModel;
use App\Models\TT_TipologiaModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DDTRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
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
    public function rules() {
        return [
            'dataSpedizione'                     => 'required|date',
            'colli'                              => 'required|integer',
            'pesoTotale'                         => 'required|numeric',
            'costoSpedizione'                    => 'required|numeric',
            'dataOraRitiro'                      => 'nullable|date',
            'cliente.id'                         => 'required|integer|exists:' . TT_AnagraficaModel::class   . ',id',
            'destinazione.id'                    => 'required|integer|exists:' . TT_IndirizzoModel::class    . ',id',
            'trasportatore.id'                   => 'required|integer|exists:' . TT_AnagraficaModel::class   . ',id',
            'trasporto.id'                       => 'required|integer|exists:' . TT_TipologiaModel::class    . ',id',
            'causale.id'                         => 'required|integer|exists:' . TT_TipologiaModel::class    . ',id',
            'aspetto.id'                         => 'required|integer|exists:' . TT_TipologiaModel::class    . ',id',
            'componenti.*.id'                    => 'required|integer|exists:' . TT_ComponenteModel::class    . ',id',
            'componenti.*.ddt_componente.prezzo' => 'nullable|numeric',
            'sims.*.id'                          => 'required|integer|exists:' . TT_SimModel::class    . ',id',
            'sims.*.ddt_componente.prezzo'       => 'nullable|numeric',
            'note.*.id'                          => 'required_without:note.*.testo|integer|exists:' . TT_AnnotazioneModel::class    . ',id',   //! Usare il find con l'id del ddt se modifica e il tipo 'TT_DDT'
            'note.*.testo'                       => 'required_without:note.*.id|string',
        ];
    }
}
