<?php

namespace App\Http\Requests\Targets;

use App\Models\Targets\TT_AreaModel;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TargetRequest extends FormRequest {
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
            'nome' => [
                'required',
                'string',
                'max:191',
                // Rule::unique('TT_Area', 'nome')->where(function (Builder $query) {
                //     $query
                //         ->where('idUtente', '=', $this->route('idUtente') ?? Auth::id())
                //         ->where('id', '<>', $this->route('idArea'));
                // }),
                Rule::unique(TT_AreaModel::class, 'nome')->ignore($this->route('idArea'))->where(function ($query) {
                    $query
                        ->where(
                            'idUtente',
                            '=',
                            $this->route('idArea')
                                ? TT_AreaModel::find($this->route('idArea'))->idUtente
                                : $this->route('idUtente')
                        );
                    // if ($this->input('idArea'))
                    //     $query->where('id', '<>', $this->input('idArea'));
                }),
            ],
            'geo_json' => 'required|array',
            'parent' => 'nullable|array',
            'parent.id' => 'required_with:parent|integer|exists:' . TT_AreaModel::class . ',id',
        ];
    }
}
