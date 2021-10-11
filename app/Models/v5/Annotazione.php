<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Annotazione extends Model {
    protected $connection = 'mysql';
    public $table = 'TT_Annotazione';

    protected $fillable = [
        'id',
        'tabella',
        'idRiferimento',
        'testo',
        'bloccato',
    ];

    protected $hidden = [
        'idOperatore',
        'tabella',
        'idRiferimento',
    ];

    public function annotati(): MorphTo {
        return $this->morphTo('annotabili', 'tabella', 'idRiferimento');
    }
}
