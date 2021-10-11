<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TT_EventoModel extends Model {
    protected $connection = 'mysql';

    public $table = 'TT_Evento';

    protected $fillable = [
        'code',
        'message',
        'idOperatore'
    ];

    protected $hidden = [
        'idOperatore',
        'created_at',
        'updated_at',
    ];
}
