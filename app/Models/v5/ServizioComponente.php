<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class ServizioComponente extends Model {
    use AsPivot;

    protected $connection = 'mysql';

    public $table = 'TC_ServizioComponente';

    protected $fillable = [
        'idServizio',
        'idComponente',
        'idTacho',
        'idSim',
        'idMezzo',
        'idRadiocomando',
        'prezzo',
        'principale',
        'parziale',
        'dataRestituzione',
        'idOperatore',
    ];
}
