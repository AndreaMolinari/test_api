<?php

namespace App\Http\Requests\v5;

use App\Models\v5\Anagrafica;
use App\Models\v5\Annotazione;
use App\Models\v5\Componente;
use App\Models\v5\Indirizzo;
use App\Models\v5\Sim;
use App\Models\v5\Tipologia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DDTRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /**@var Utente */
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
            'dataSpedizione'                     => ['required', 'date'],
            'colli'                              => ['required', 'integer'],
            'pesoTotale'                         => ['required', 'numeric'],
            'costoSpedizione'                    => ['required', 'numeric'],
            'dataOraRitiro'                      => ['nullable', 'date'],
            'destinazione.id'                    => ['required', 'integer', 'exists:' . Indirizzo::class    . ',id'],
            'trasportatore.id'                   => ['required', 'integer', 'exists:' . Anagrafica::class   . ',id'],
            'trasporto.id'                       => ['required', 'integer', 'exists:' . Tipologia::class    . ',id'],
            'causale.id'                         => ['required', 'integer', 'exists:' . Tipologia::class    . ',id'],
            'aspetto.id'                         => ['required', 'integer', 'exists:' . Tipologia::class    . ',id'],
            'componenti.*.id'                    => ['required', 'integer', 'exists:' . Componente::class    . ',id'],
            'componenti.*.ddt_componente.prezzo' => ['nullable', 'numeric'],
            'sims.*.id'                          => ['required', 'integer', 'exists:' . Sim::class    . ',id'],
            'sims.*.ddt_componente.prezzo'       => ['nullable', 'numeric'],
            'note.*.id'                          => ['required_without:note.*.testo', 'integer', 'exists:' . Annotazione::class    . ',id'],   //! Usare il find con l'id del ddt se modifica e il tipo 'DDT
            'note.*.testo'                       => ['required_without:note.*.id', 'string'],
        ];
    }
}
