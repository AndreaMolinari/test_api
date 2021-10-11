<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class DDT extends Model {
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'TT_DDT';

    protected $fillable = [
        'dataSpedizione',
        'colli',
        'pesoTotale',
        'costoSpedizione',
        'dataOraRitiro',
    ];

    protected $hidden = [
        'idCliente',
        'idIndirizzoDestinazione',
        'idTrasportatore',
        'idTrasporto',
        'idCausale',
        'idAspetto',
        'idOperatore',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'dataSpedizione'  => 'date',
        'dataOraRitiro'   => 'datetime',
        'pesoTotale'      => 'double',
        'costoSpedizione' => 'double',
    ];

    protected $appends = [
        'costoTotale',
    ];

    protected static function booted()
    {
        static::creating(function (DDT $ddt) {
            // if (!($ddt->anno ?? false)) {
            $ddt->anno = now()->year;
            // Prende l'ultimo numero o zero e lo incrementa di uno
            $ddt->numero = (DDT::orderBy('numero', 'desc')->firstWhere('anno', $ddt->anno)->numero ?? 0) + 1;
            // }
        });
    }

    public function getCostoTotaleAttribute() {
        return $this->costoSpedizione + $this->ddt_componenti()->sum('prezzo');
    }

    public function scopeFatturabili(Builder $b) {
        return $b->whereDoesntHave('fatture');
    }

    //* RELASHIONSHIP FUNCTIONS

    public function operatore(): BelongsTo {
        return $this->belongsTo(Utente::class, 'idOperatore');
    }

    public function cliente(): BelongsTo {
        return $this->belongsTo(Anagrafica::class, 'idCliente');
    }

    public function destinazione(): BelongsTo {
        return $this->belongsTo(Indirizzo::class, 'idIndirizzoDestinazione');
    }

    public function trasportatore(): BelongsTo {
        return $this->belongsTo(Anagrafica::class, 'idTrasportatore');
    }

    public function trasporto(): BelongsTo {
        return $this->belongsTo(Tipologia::class, 'idTrasporto');
    }

    public function causale(): BelongsTo {
        return $this->belongsTo(Tipologia::class, 'idCausale');
    }

    public function aspetto(): BelongsTo {
        return $this->belongsTo(Tipologia::class, 'idAspetto');
    }

    public function componenti(): MorphToMany {
        return $this->morphedByMany(Componente::class, 'shippable', 'TC_DDTComponente', 'idDDT', 'shippable_id', 'id', 'id')
            ->withPivot([
                'prezzo'
            ])
            ->as('ddt_componente');
    }

    public function sims(): MorphToMany {
        return $this->morphedByMany(Sim::class, 'shippable', 'TC_DDTComponente', 'idDDT', 'shippable_id', 'id', 'id')
            ->withPivot([
                'prezzo'
            ])
            ->as('ddt_compontente');
    }

    public function note(): MorphMany {
        return $this->morphMany(Annotazione::class, 'annotable', 'tabella', 'idRiferimento');
    }

    public function fatture(): MorphToMany
    {
        return $this->morphToMany(Fattura::class, 'billable', 'TC_FatturaPart', null, 'idFattura');
        // return $this->belongsToMany(Fattura::class, 'TC_FatturaPart', )
    }

    // Per il costo totale
    public function ddt_componenti(): HasMany {
        return $this->hasMany(DDTComponente::class, 'idDDT');
    }
}
