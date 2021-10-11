<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsToMany};

class TT_TipologiaModel extends Model {
    protected $connection = 'mysql';

    public $table = 'TT_Tipologia';

    protected $fillable = [
        'tipologia',
        'descrizione',
        'idParent',
        'bloccato',
        'idOperatore'
    ];

    protected $hidden = [
        'bloccato',
        'deleted',
        'idUpdated',
        'updated_at',
        'created_at',
        'idOperatore'
    ];

    public function anagrafica(): BelongsToMany {
        return $this->belongsToMany(TT_AnagraficaModel::class, 'TC_AnagraficaTipologia', 'idTipologia', 'idAnagrafica');
    }

    public function servizi(): BelongsToMany {
        return $this->belongsToMany(TT_ServizioModel::class, 'TC_ServizioApplicativo', 'idTipologia', 'idServizio')
            ->as('servizio_applicativo');
    }

    public function children(): HasMany {
        return $this->hasMany(TT_TipologiaModel::class, 'idParent');
    }

    public function allChildren() {
        return $this->children()->with('allChildren');
    }
}
