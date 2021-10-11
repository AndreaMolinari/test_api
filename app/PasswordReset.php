<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $connection = 'mysql';
    
    protected $fillable = [
        'email', 'token'
    ];
}