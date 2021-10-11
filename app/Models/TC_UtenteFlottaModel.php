<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class TC_UtenteFlottaModel extends Model {
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
