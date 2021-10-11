<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TT_DocumentoModel extends Model {
    protected $connection = 'mysql';
    public $table = 'TT_Documento';

    protected $fillable = [
        'reference_table',
        'reference_id',
        'seriale',
        'dataScadenza',
        'rinnovo',
        'descrizione',
        'idTipologia',
        'idOperatore'
    ];
}
