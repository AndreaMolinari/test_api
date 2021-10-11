<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class FlottaServizio extends Model {
    use AsPivot;

    protected $connection = 'mysql';

    public $table = 'TC_FlottaServizio';

    protected $fillable = [
        'idGruppo',
        'idFlotta',
        'idServizio',
        'nickname',
        'icona',
        'bloccato',
        'idOperatore',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idOperatore',
    ];
}
