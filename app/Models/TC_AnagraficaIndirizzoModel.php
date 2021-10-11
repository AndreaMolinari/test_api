<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TC_AnagraficaIndirizzoModel extends Model {
    protected $connection = 'mysql';
    public $table = 'TC_AnagraficaIndirizzo';
    protected $fillable = [
        'idAnagrafica',
        'idIndirizzo',
        'idTipologia',
        'descrizione',
        'predefinito',
        'bloccato',
        'idOperatore',
    ];
}
