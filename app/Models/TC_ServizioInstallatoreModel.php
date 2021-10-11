<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TC_ServizioInstallatoreModel extends Model {
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
