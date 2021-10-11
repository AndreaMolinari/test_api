<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TC_FlottaServizioModel extends Model {
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
