<?php

namespace App\Http\Requests\Trax;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RigeneraIndirizziRequest extends FormRequest
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
        return [
            'FromDate' => 'required|date|before:ToDate',
            'ToDate'   => 'nullable|date|after:FromDate|before_or_equal:' . (new Carbon($this->input('FromDate') ?? 0))->addDays(30)->addHours(23)->addMinutes(59)->addSeconds(59)->isoFormat('YYYY-MM-DD HH:mm:ss'),
        ];
    }
}
