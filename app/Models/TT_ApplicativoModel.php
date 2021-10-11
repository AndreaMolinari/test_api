<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TT_ApplicativoModel extends Model {
    protected $connection = 'mysql';
    public $table = 'TT_Applicativo';
    protected $fillable = [
        'nome',
        'descrizione',
        'link',
        'bloccato',
        'idOperatore',

    ];
}
