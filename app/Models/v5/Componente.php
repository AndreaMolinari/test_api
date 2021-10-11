<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Componente extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    public $table = 'TT_Componente';

    protected $fillable = [
        'unitcode',
        'imei',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idOperatore',
        'idModello',
        'idSim',
        'bloccato',
        'deleted_at',
    ];

    /** @param Brand|int $brand */
    public function scopeByBrand(Builder $builder, $brand)
    {
        return $builder->whereHas('modello', fn (Builder $b) => $b->where('idBrand', $brand instanceof Brand ? $brand->id : $brand));
    }

    public function scopeRadiocomandi($query)
    {
        return $query->whereRaw("`idModello` in (SELECT `id` FROM `TT_Modello` WHERE `idTipologia` = 93)");
    }

    public function servizi_per_gps(): BelongsToMany
    {
        return $this->belongsToMany(Servizio::class, 'TC_ServizioComponente', 'idComponente', 'idServizio')
            ->using(ServizioComponente::class)
            ->wherePivotNotNull('idComponente');
    }

    public function servizi_per_tacho(): BelongsToMany
    {
        return $this->belongsToMany(Servizio::class, 'TC_ServizioComponente', 'idTacho', 'idServizio')
            ->using(ServizioComponente::class)
            ->wherePivotNotNull('idTacho');
    }

    public function servizi_per_radiocomando(): BelongsToMany
    {
        return $this->belongsToMany(Servizio::class, 'TC_ServizioComponente', 'idRadiocomando', 'idServizio')
            ->using(ServizioComponente::class)
            ->wherePivotNotNull('idRadiocomando');
    }

    public function servizi(): BelongsToMany
    {
        $comps = $this->belongsToMany(Servizio::class, 'TC_ServizioComponente', 'idComponente', 'idServizio')->wherePivotNotNull('idComponente');
        $tacho = $this->belongsToMany(Servizio::class, 'TC_ServizioComponente', 'idTacho', 'idServizio')->wherePivotNotNull('idTacho');
        $radio = $this->belongsToMany(Servizio::class, 'TC_ServizioComponente', 'idRadiocomando', 'idServizio')->wherePivotNotNull('idRadiocomando');

        return new BelongsToMany($comps->getQuery()
            ->orWhere($tacho->getQuery())
            ->orWhere($radio->getQuery()), $this, 'TC_ServizioComponente', '', '', '', '');
    }

    public function modello(): BelongsTo
    {
        return $this->belongsTo(Modello::class, 'idModello');
    }

    public function sim(): BelongsTo
    {
        return $this->belongsTo(Sim::class, 'idSim');
    }

    public function note(): HasMany
    {
        return $this->hasMany(TT_AnnotazioneModel::class, 'idRiferimento', 'id')->where('tabella', $this->table);
    }

    // !! METTERE BENE COME DA DOCS
    // public function annotazioni(): MorphMany {
    //     return $this->morphMany(Annotazione::class, 'TROVA NOME GIUSTO');
    // }

    public function autisti(): BelongsToMany
    {
        return $this->belongsToMany(Autista::class, 'TC_AutistaComponente', 'idComponente', 'idAutista');
    }
}
