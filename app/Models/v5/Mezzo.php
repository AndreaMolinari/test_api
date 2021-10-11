<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mezzo extends Model {
    use HasFactory;
    
    protected $connection = 'mysql';

    public $table = 'TT_Mezzo';

    protected $fillable = [
        'targa',
        'telaio',
        'colore',
        'anno',
        'info',
        'ore_totali',
        'km_totali',
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
        'bloccato' => 'boolean',
    ];

    protected $hidden = [
        'idModello',
        'idOperatore',
        'bloccato',
        'created_at',
        'updated_at',
    ];

    // protected $with = ['modello'];

    public function modello(): BelongsTo {
        return $this->belongsTo(Modello::class, 'idModello');
    }

    public function servizi(): BelongsToMany {
        return $this->belongsToMany(Servizio::class, 'TC_ServizioComponente', 'idMezzo', 'idServizio')
            ->using(ServizioComponente::class)
            ->wherePivotNotNull('idMezzo');
    }

    public function note(): HasMany {
        return $this->hasMany(TT_AnnotazioneModel::class, 'idRiferimento', 'id')->where('tabella', $this->table);
    }

    // !! METTERE BENE COME DA DOCS
    // public function annotazioni(): MorphMany {
    //     return $this->morphMany(Annotazione::class, 'TROVA NOME GIUSTO');
    // }
}
