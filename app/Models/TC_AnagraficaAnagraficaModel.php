<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TC_AnagraficaAnagraficaModel extends Model {
    protected $connection = 'mysql';

    public $table = 'TC_AnagraficaAnagrafica';

    protected $fillable = [
        'idParent',
        'idChild',
        'idTipologia',
        'idOperatore'
    ];

    protected $hidden = [
        'idOperatore',
        'created_at',
        'updated_at'
    ];
}
