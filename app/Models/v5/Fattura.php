<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fattura extends Model
{
    use SoftDeletes;

    private const SPESE_INCASSO = 3;

    protected $connection = "mysql";

    protected $table = "TT_Fattura";

    protected $fillable = [
        'manuale'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'imponibile',
        'imposta',
        'totale',
        'speseIncasso',
        'scadenze',
    ];

    protected $casts = [
        'manuale' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function (Fattura $fattura) {
            // if (!($fattura->anno ?? false)) {
            $fattura->anno = now()->year;
            // Prende l'ultimo numero o zero e lo incrementa di uno
            $fattura->numero = (Fattura::orderBy('numero', 'desc')->firstWhere('anno', $fattura->anno)->numero ?? 0) + 1;
            // }
        });
    }

    public function getImponibileAttribute()
    {
        return round($this->voci()->get()->sum('prezzoScontato') + $this->speseIncasso, 2);
    }

    public function getScadenzeAttribute()
    {
        $periodo = $this->cliente()->first()->fatturazione()->with('periodo')->first()->periodo->data;
        $scadenze = [];
        foreach($periodo['delayMonths'] ?? [] as $delayMonth){
            $tmp = (object)[
                'data' =>  clone($this->created_at)->addMonthsNoOverflow($delayMonth + 1)->startOfMonth()->setDay($periodo['day']),
                'importo' => $this->totale / count($periodo['delayMonths']),
            ];
            $scadenze[] = $tmp;
        }
        return collect($scadenze);
    }

    public function getImpostaAttribute()
    {
        return round($this->voci()->get()->sum('imposta') + ($this->speseIncasso * 0.22), 2);
    }

    public function getSpeseIncassoAttribute()
    {
        return round($this->cliente()->first()->fatturazione()->first()->speseIncasso * static::SPESE_INCASSO, 2);
    }

    public function getTotaleAttribute()
    {
        return round($this->imponibile + $this->imposta, 2);
    }

    public function scopeNoteCredito(Builder $b): Builder
    {
        return $b->whereNotNull('idNotaCredito');
    }

    public function scopeFatture(Builder $b): Builder
    {
        return $b->whereNull('idNotaCredito');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Anagrafica::class, 'idAnagrafica');
    }

    public function voci(): HasMany
    {
        return $this->hasMany(FatturaPart::class, 'idFattura');
    }

    /**
     * Se sei una fattura prendi la tua nota di credito
     */
    public function notaCredito(): HasOne
    {
        return $this->hasOne(Fattura::class, 'idNotaCredito');
    }

    /**
     * Se sei una nota di credito prendi la tua fattura
     */
    public function fattura(): BelongsTo
    {
        return $this->belongsTo(Fattura::class, 'idNotaCredito');
    }

    public function servizi(): MorphToMany
    {
        return $this->morphedByMany(Servizio::class, 'billable', 'TC_FatturaPart', 'idFattura', 'billable_id', 'id', 'id')
            ->as('fattura_part')->withPivot([
                'prezzoUnitario',
                'iva',
                'sconto'
            ])->using(FatturaPart::class);
    }

    public function ddts(): MorphToMany
    {
        return $this->morphedByMany(DDT::class, 'billable', 'TC_FatturaPart', 'idFattura', 'billable_id', 'id', 'id')
            ->as('fattura_part')->withPivot([
                'prezzoUnitario',
                'iva',
                'sconto'
            ])->using(FatturaPart::class);
    }

    public function addebiti(): MorphToMany
    {
        return $this->morphedByMany(Addebito::class, 'billable', 'TC_FatturaPart', 'idFattura', 'billable_id', 'id', 'id')
            ->as('fattura_part')->withPivot([
                'prezzoUnitario',
                'iva',
                'sconto'
            ])->using(FatturaPart::class);
    }
}
