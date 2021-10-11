<?php

namespace App\Http\Requests\Trax;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoricoRequest extends FormRequest
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
            'FromDate' => 'nullable|date',
            'ToDate'   => 'nullable|date|after:FromDate|before_or_equal:' . (new Carbon($this->input('FromDate') ?? 0))->addDays(30)->addHours(23)->addMinutes(59)->addSeconds(59)->isoFormat('YYYY-MM-DD HH:mm:ss'),
            'TimeZoneAdjustment' => 'nullable',
        ];
    }
}
