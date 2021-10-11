<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubrica extends Model
{
    protected $connection = 'mysql';

    public $table = 'TT_Contatto';

    protected $fillable = [
        'nome',
        'descrizione',
    ];

    protected $hidden = [
        'idOperatore',
        'updated_at',
        'created_at',
        'contatto',
        'predefinito',
        'idAnagrafica',
        'idTipologia',
        'bloccato',
        'idParent',
    ];

    protected $with = ['contatti'];

    public function contatti(): HasMany {
        return $this->hasMany(Contatto::class, 'idParent');
    }

    public function anagrafica(): BelongsTo {
        return $this->belongsTo(Anagrafica::class, 'idAnagrafica');
    }
}
