<?php

namespace App\Http\Requests\Targets;

use App\Models\TT_FlottaModel;
use App\Models\TT_ServizioModel;
use App\Models\TT_TipologiaModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoricoTargetRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        /**@var TT_UtenteModel */
        $user = Auth::user();
        return $user->getRoleLevel() <= 6;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'target.id'          => 'nullable|integer|exists:' . TT_FlottaModel::class . ',id',
            'flotta.id'          => 'required_without:servizio|integer|exists:' . TT_FlottaModel::class . ',id',
            'servizio.id'        => 'required_without:flotta|integer|exists:' . TT_ServizioModel::class . ',id',
            'tipologia.id'       => 'nullable|integer|exists:' . TT_TipologiaModel::class . ',id|in:' . join(",", TT_TipologiaModel::where('idParent', 120)->get()->pluck('id')->toArray()),
            'FromDate'           => 'nullable|date',
            'ToDate'             => 'nullable|date|after:FromDate|before_or_equal:' . (new \Carbon\Carbon($this->input('FromDate') ?? 0))->addDays(30)->addHours(23)->addMinutes(59)->addSeconds(59)->isoFormat('YYYY-MM-DD HH:mm:ss'),
            // 'TimeZoneAdjustment' => 'nullable',
        ];
    }
}
