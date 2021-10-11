<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class AnagraficaTipologia extends Model {
    use AsPivot;

    protected $connection = 'mysql';

    public $table = 'TC_AnagraficaTipologia';

    protected $fillable = [
        'idAnagrafica',
        'idTipologia',
        'bloccato',
        'idOperatore',
    ];
}
