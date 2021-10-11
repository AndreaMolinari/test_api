<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TT_ContattoModel extends Model {
    protected $connection = 'mysql';
    public $table = 'TT_Contatto';

    protected $fillable = [
        'idAnagrafica',
        'descrizione',
        'nome',
        'idTipologia',
        'contatto',
        'predefinito',
        'bloccato',
        'idOperatore',
        'idParent'
    ];
}
