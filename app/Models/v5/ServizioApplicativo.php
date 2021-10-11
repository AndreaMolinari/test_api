<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class ServizioApplicativo extends Model {
    use AsPivot;

    protected $connection = 'mysql';

    public $table = 'TC_ServizioApplicativo';

    protected $fillable = [
        'idTipologia',
        'idServizio',
        'idOperatore',
    ];
}
