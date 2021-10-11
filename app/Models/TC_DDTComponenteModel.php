<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TC_DDTComponenteModel extends Model {
    protected $table = 'TC_DDTComponente';

    protected $fillable = [
        'shippable_id',
        'shippable_type',
        'prezzo',
        // 'idDDT',
    ];

    public function operatore(): BelongsTo {
        return $this->belongsTo(TT_UtenteModel::class, 'idOperatore');
    }

    public function ddt(): BelongsTo {
        return $this->belongsTo(TT_DDTModel::class, 'idDDT');
    }
}
