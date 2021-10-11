<?php
namespace App\Http\Requests;

use App\Models\{TT_BrandModel, TT_TipologiaModel};
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ModelloRequest extends FormRequest
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
            "idBrand" => 'required|exists:' . TT_BrandModel::class . ',id',
            "idTipologia" => 'required|exists:' . TT_TipologiaModel::class . ',id',
            "modello" => 'required',
        ];

        return $return;
    }
}
