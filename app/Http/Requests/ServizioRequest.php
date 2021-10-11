<?php

namespace App\Http\Requests;

use App\Models\{TT_AnagraficaModel, TT_ComponenteModel, TT_MezzoModel, TT_TipologiaModel};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ServizioRequest extends FormRequest
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
            "idAnagrafica"   => 'required|exists:' . TT_AnagraficaModel::class . ',id',
            "idCausale"      => 'required|exists:' . TT_TipologiaModel::class . ',id',
            "idPeriodo"      => 'required|exists:' . TT_TipologiaModel::class . ',id',
            "prezzo"         => 'required|regex:/^\d+(\.\d{1,2})?$/',
            "dataInizio"     => 'required|date',
            "dataFine"       => 'present|date|after:dataInizio|nullable',
            "dataSospInizio" => 'present|date|after_or_equal:dataInizio|nullable',
            "dataSospFine"   => 'present|date|after:dataSospInizio|nullable',

            "applicativo"                 => 'required',
            "applicativo.*.idApplicativo" => 'required|exists:' . TT_TipologiaModel::class . ',id',

            'mezzo'           => 'present',
            'mezzo.*.idMezzo' => 'required|exists:' . TT_MezzoModel::class . ',id',

            'componente'                    => 'present',
            'componente.*.idComponente'     => 'required|exists:' . TT_ComponenteModel::class . ',id',
            "prezzo"                        => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'componente.*.parziale'         => 'present|boolean',
            'componente.*.principale'       => 'present|nullable|boolean',
            'componente.*.dataRestituzione' => 'present|date|nullable',

            'tacho'                    => 'present',
            'tacho.*.idComponente'     => 'required|exists:' . TT_ComponenteModel::class . ',id',
            'tacho.*.principale'       => 'present|boolean',
            'tacho.*.parziale'         => 'present|boolean',
            'tacho.*.dataRestituzione' => 'present|date|nullable',

            'sim'         => 'present',
            'sim.*.idSim' => 'required|exists:' . TT_ComponenteModel::class . ',id',

            'radiocomando'             => 'present',
            'radiocomando.*.idRadiocomando'   => 'required|exists:' . TT_ComponenteModel::class . ',id',

            'servizioInstallatore'                     => 'required_with:mezzo',
            'servizioInstallatore.*.idAnagrafica'      => 'exists:' . TT_AnagraficaModel::class . ',id',
            'servizioInstallatore.*.dataInstallazione' => 'date',

            'nota'         => 'present',
            'nota.*.testo' => 'required',
        ];

        return $return;
    }
}
