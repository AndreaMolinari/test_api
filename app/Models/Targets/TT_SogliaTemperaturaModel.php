<?php

namespace App\Models\Targets;

use Illuminate\Database\Eloquent\Builder;

class TT_SogliaTemperaturaModel extends TT_SogliaModel {

    protected $casts = [
        'inizio' => 'float',
        'fine' => 'float',
    ];

    protected static function booted() {
        static::addGlobalScope('SogliaTemperaturaScope', function (Builder $scope) {
            return $scope->where('idTipologia', 116); //? Tipologia temperatura
        });
    }
}
