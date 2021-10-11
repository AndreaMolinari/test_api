<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class DDTComponente extends Model {

    use AsPivot;

    protected $connection = 'mysql';

    protected $table = 'TC_DDTComponente';

    protected $fillable = [
        'shippable_id',
        'shippable_type',
        'prezzo',
        // 'idDDT',
    ];

    public function operatore(): BelongsTo {
        return $this->belongsTo(Utente::class, 'idOperatore');
    }

    public function ddt(): BelongsTo {
        return $this->belongsTo(DDT::class, 'idDDT');
    }
}
