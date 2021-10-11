<?php
namespace App\Http\Requests;

use App\Models\TT_AnagraficaModel;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BrandRequest extends FormRequest
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
            "marca"       => 'required|unique:TT_Brand,marca,'.$this->route('id').'|nullable',
            "idFornitore" => 'nullable|exists:' . TT_AnagraficaModel::class . ',id',
        ];

        return $return;
    }
}
