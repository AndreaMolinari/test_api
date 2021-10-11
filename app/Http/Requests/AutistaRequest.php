<?php

namespace App\Http\Requests;

use App\Models\{TT_AutistaModel, TT_ComponenteModel, TT_TipologiaModel};
use Illuminate\Foundation\Http\FormRequest;

class AutistaRequest extends FormRequest
{
    private function import_rules(array $rules, array $import)
    {
        foreach ($import as $resource => $properties) {
            $rules[$resource] = 'array';
            $rules["$resource.*"] = "required_with:$resource|array";

            // Inject the id
            if (isset($properties['only_existing']) && $properties['only_existing']) {
                // $rules["$resource.*.id"] = "required|{$rules["$resource.*.id"]}";
                $rules["$resource.*.id"] = "required_with:$resource|integer";
                if (isset($properties['exists_rule']))
                    $rules["$resource.*.id"] .= "|{$properties['exists_rule']}";
            } else {
                $import_rules = (new $properties['request']())->rules();
                foreach ($import_rules as $name => $rule) {
                    $rules["$resource.*.$name"] = preg_replace('/(?=\|?)(required)(?=\|?)(?!_)/', "required_with:$resource", $rule);
                }
            }

            //Inject the pivot
            if (isset($properties['pivot']))
                foreach ($properties['pivot'] as $pivot_name => $pivot_rules)
                    foreach ($pivot_rules as $pivot_rule_field => $pivot_rule)
                        $rules["$resource.*.$pivot_name.$pivot_rule_field"]
                            = preg_replace('/(?=\|?)(required)(?=\|?)(?!_)/', "required_with:$resource", $pivot_rule);
        }

        return $rules;
    }

    private function fix_required_with(array $rules)
    {
        foreach ($rules as $name => $rule) {
            if (!is_string($rule)) continue;
            if (!str_contains($rule, 'required_with:')) continue;
            $xpl = explode('.', $name);
            $xpl = $xpl[count($xpl) - 1];
            $newName = str_replace(".$xpl", '', $name);
            //? $OLD_REGEX = '/(?=\|?)(required_with:\w*)(?=\|?)/'
            $rules[$name] = preg_replace('/(?=\|?)(required_with:[\w\*\.]*)(?=\|?)/', "required_with:$newName", $rule);
        }

        return $rules;
    }

    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'           => 'integer|exists:' . TT_AutistaModel::class . ',id',              // Only for UPDATING, ignored by the fillable array
            'autista'      => 'required|string|max:255',
        ];
    }
}
