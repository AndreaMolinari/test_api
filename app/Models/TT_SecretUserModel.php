<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TT_SecretUserModel extends Model {
    protected $connection = 'mysql';
    public $table = 'TT_SecretUser';
    protected $fillable = [
        'secret',
        'idUtente',
        'idOperatore'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idOperatore',
    ];

    public function utente(): BelongsTo {
        return $this->belongsTo(TT_UtenteModel::class, 'idUtente', 'id');
    }
}
