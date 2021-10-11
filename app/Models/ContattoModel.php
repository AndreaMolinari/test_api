<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo};

class ContattoModel extends Model
{
    protected $connection = 'mysql';

    protected $table = 'TT_Contatto';

    protected $fillable = [
        'nome',
        'contatto',
        'predefinito',
        'bloccato',
    ];

    protected $hidden = [
        'idAnagrafica',
        'idTipologia',
        'idParent',
        'idOperatore',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function rubrica(): BelongsTo
    {
        return $this->belongsTo(ContattoModel::class, 'idParent')->whereNull('idParent');
    }

    public function tipologia(): BelongsTo
    {
        return $this->belongsTo(TT_TipologiaModel::class, 'idTipologia');
    }
}
