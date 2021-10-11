<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class AutistaComponente extends Model {
    use AsPivot;

    protected $connection = 'mysql';

    protected $table = 'TC_AutistaComponente';

    protected $guarded = [];

    public function operatore(): BelongsTo {
        return $this->belongsTo(UtenteModel::class, 'idOperatore');
    }
}
