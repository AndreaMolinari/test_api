<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TC_LogModel extends Model {
    protected $connection = 'mysql';
    public $table = 'TC_Log';

    protected $fillable = [
        'idUtente',
        'idWebService',
        'dataLogout',
        'remoteIP',
        'bloccato',
        'idOperatore',

    ];
}
