<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class ServizioInstallatore extends Model {
    use AsPivot;

    protected $connection = 'mysql';

    public $table = 'TC_ServizioInstallatore';

    protected $fillable = [
        'idAnagrafica',
        'idServizio',
        'dataInstallazione',
        'descrizione',
        'idOperatore',
    ];
}
