<?php

namespace App\Http\Requests\Trax;

use App\Models\TT_ServizioModel;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ParzialeRequest extends FormRequest
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
            'idServizio'         => 'nullable|exists:' . TT_ServizioModel::class . ',id',
            'FromDate'           => 'nullable|date',
            'ToDate'   => 'nullable|date|after:FromDate|before_or_equal:' . (new Carbon($this->input('FromDate') ?? 0))->addDays(30)->addHours(23)->addMinutes(59)->addSeconds(59)->isoFormat('YYYY-MM-DD HH:mm:ss'),
            'TimeZoneAdjustment' => 'nullable',
            'ExcludeData'        => 'nullable|boolean',
            'StartCondition'     => 'nullable',
            'EndCondition'       => 'nullable',
        ];
    }
}
