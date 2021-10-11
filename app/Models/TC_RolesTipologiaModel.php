<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TC_RolesTipologiaModel extends Model {
    protected $connection = 'mysql';
    public $table = 'TC_RolesTipologia';
    protected $fillable = [
        'idTipologia',
        'roles',
        'bloccato',
        'idOperatore',

    ];
}
