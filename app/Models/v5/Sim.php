<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sim extends Model {
    use HasFactory;
    
    protected $connection = 'mysql';

    public $table = 'TT_Sim';

    protected $fillable = [
        'serial',
        'apn',
        'dataAttivazione',
        'dataDisattivazione',
        'bloccato',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idModello',
        'idOperatore',
        'bloccato',
    ];

    public function componente(): HasOne {
        return $this->hasOne(Componente::class, 'idSim');
    }

    public function modello(): BelongsTo {
        return $this->belongsTo(Modello::class, 'idModello');
    }
}
