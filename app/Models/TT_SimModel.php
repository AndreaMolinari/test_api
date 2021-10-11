<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TT_SimModel extends Model {
    protected $connection = 'mysql';
    public $table = 'TT_Sim';
    protected $fillable = [
        'idModello',
        'serial',
        'apn',
        'dataAttivazione',
        'dataDisattivazione',
        'bloccato',
        'idOperatore',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idOperatore',
        'bloccato',
    ];

    public function componente(): BelongsTo {
        return $this->belongsTo(TT_ComponenteModel::class, 'id', 'idSim');
    }
}
