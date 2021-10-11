<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class TT_DDTModel extends Model {
    protected $table = 'TT_DDT';

    protected $fillable = [
        // 'numero',    // ! Sono calcolati
        // 'anno',      // ! in inserimento
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
        'dataSpedizione' => 'date',
        'dataOraRitiro' => 'datetime',
    ];

    protected $appends = [
        'costoTotale',
    ];

    public function getCostoTotaleAttribute() {
        return $this->costoSpedizione + $this->ddt_componenti()->sum('prezzo');
    }

    //* RELASHIONSHIP FUNCTIONS

    public function operatore(): BelongsTo {
        return $this->belongsTo(TT_UtenteModel::class, 'idOperatore');
    }

    public function cliente(): BelongsTo {
        return $this->belongsTo(TT_AnagraficaModel::class, 'idCliente');
    }

    public function destinazione(): BelongsTo {
        return $this->belongsTo(TT_IndirizzoModel::class, 'idIndirizzoDestinazione');
    }

    public function trasportatore(): BelongsTo {
        return $this->belongsTo(TT_AnagraficaModel::class, 'idTrasportatore');
    }

    public function trasporto(): BelongsTo {
        return $this->belongsTo(TT_TipologiaModel::class, 'idTrasporto');
    }

    public function causale(): BelongsTo {
        return $this->belongsTo(TT_TipologiaModel::class, 'idCausale');
    }

    public function aspetto(): BelongsTo {
        return $this->belongsTo(TT_TipologiaModel::class, 'idAspetto');
    }

    public function componenti(): MorphToMany {
        return $this->morphedByMany(TT_ComponenteModel::class, 'shippable', 'TC_DDTComponente', 'idDDT', 'shippable_id', 'id', 'id')
            ->withPivot([
                'prezzo'
            ])
            ->as('ddt_componente');
    }

    public function sims(): MorphToMany {
        return $this->morphedByMany(TT_SimModel::class, 'shippable', 'TC_DDTComponente', 'idDDT', 'shippable_id', 'id', 'id')
            ->withPivot([
                'prezzo'
            ])
            ->as('ddt_compontente');
    }

    public function note(): MorphMany {
        return $this->morphMany(TT_AnnotazioneModel::class, 'annotable', 'tabella', 'idRiferimento');
    }

    // Per il costo totale
    public function ddt_componenti() {
        return $this->hasMany(TC_DDTComponenteModel::class, 'idDDT');
    }
}
