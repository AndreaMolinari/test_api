<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class AnagraficaIndirizzo extends Model {
    use AsPivot;

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
