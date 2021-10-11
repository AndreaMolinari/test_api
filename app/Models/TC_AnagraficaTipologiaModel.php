<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TC_AnagraficaTipologiaModel extends Model {
    protected $connection = 'mysql';
    public $table = 'TC_AnagraficaTipologia';
    protected $fillable = [
        'idAnagrafica',
        'idTipologia',
        'bloccato',
        'idOperatore',

    ];
}
