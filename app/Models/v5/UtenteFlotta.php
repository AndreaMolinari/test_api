<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class UtenteFlotta extends Model {
    use AsPivot;

    protected $connection = 'mysql';

    public $table = 'TC_UtenteFlotta';

    protected $fillable = [
        'idUtente',
        'idRiferimento',
        'nickname',
        'principale',
        'bloccato',
        'idOperatore',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idOperatore',
    ];
}
