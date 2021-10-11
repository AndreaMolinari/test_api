<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TT_MezzoModel extends Model {
    protected $connection = 'mysql';

    public $table = 'TT_Mezzo';

    protected $fillable = [
        'idModello',
        'targa',
        'telaio',
        'colore',
        'anno',
        'info',
        'bloccato',
        'idOperatore',
        'ore_totali',
        'km_totali'
    ];

    protected $casts = [
        'idModello' => 'integer',
        'targa' => 'string',
        'telaio' => 'string',
        'colore' => 'string',
        'anno' => 'string',
        'info' => 'string',
        'km_totali' => 'integer',
        'ore_totali' => 'float',
        'bloccato' => 'boolean'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idOperatore'
    ];

    protected $with = ['modello'];

    public function modello(): BelongsTo {
        return $this->belongsTo(TT_ModelloModel::class, 'idModello');
    }

    public function servizi(): BelongsToMany {
        return $this->belongsToMany(TT_ServizioModel::class, 'TC_ServizioComponente', 'idMezzo', 'idServizio')
            ->using(TC_ServizioComponenteModel::class)
            ->wherePivotNotNull('idMezzo');
    }

    public function note(): HasMany {
        return $this->hasMany(TT_AnnotazioneModel::class, 'idRiferimento', 'id')->where('tabella', $this->table);
    }
}
