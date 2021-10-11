<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TC_ServizioApplicativoModel extends Model {
    protected $connection = 'mysql';
    public $table = 'TC_ServizioApplicativo';
    protected $fillable = [
        'idTipologia',
        'idServizio',
        'idOperatore',
    ];
}
