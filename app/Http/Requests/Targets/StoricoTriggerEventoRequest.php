<?php

namespace App\Http\Requests\Targets;

use App\Models\TT_ServizioModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class StoricoTriggerEventoRequest extends FormRequest {
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
        try {
            return [
                'servizi' => ['nullable', 'array'],
                'servizi.*.id'        => ['required_with:servizi', 'integer', 'exists:' . TT_ServizioModel::class . ',id',],
                'tipologia.id'       => [
                    'nullable',
                    'integer',
                    Rule::exists('TT_Tipologia', 'id')->where('idParent', 120),
                ],
                'FromDate'           => ['nullable', 'date'],
                'ToDate'             => [
                    'nullable',
                    'date',
                    'after:FromDate',
                    'before_or_equal:' . (new \Carbon\Carbon($this->input('FromDate') ?? 0))->addDays(30)->addHours(23)->addMinutes(59)->addSeconds(59)->isoFormat('YYYY-MM-DD HH:mm:ss')
                ],
                // 'TimeZoneAdjustment' => 'nullable',
            ];
        } catch (\Throwable $th) {
            throw new UnprocessableEntityHttpException(null, $th);
        }
    }
}
