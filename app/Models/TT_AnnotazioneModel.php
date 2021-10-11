<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TT_AnnotazioneModel extends Model {
    protected $connection = 'mysql';
    public $table = 'TT_Annotazione';

    protected $fillable = [
        'tabella',
        'idRiferimento',
        'testo',
        'bloccato',
        'idOperatore',
    ];

    protected $visible = [
        'id',
        'testo',
    ];
}
