<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contatto extends Model
{
    protected $connection = 'mysql';

    protected $table = 'TT_Contatto';

    protected $fillable = [
        'nome',
        'contatto',
        'predefinito',
        'bloccato',
    ];

    protected $visible = [
        'id',
        'nome',
        'contatto',
        'predefinito',
    ];

    public function rubrica(): BelongsTo {
        return $this->belongsTo(Rubrica::class, 'idParent')->whereNull('idParent');
    }
}
