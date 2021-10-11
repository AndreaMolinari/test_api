<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class TT_ComponenteModel extends Model {
    protected $connection = 'mysql';

    public $table = 'TT_Componente';

    protected $fillable = [
        'idModello',
        'unitcode',
        'idSim',
        'idOperatore',
        'imei'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idOperatore',
        'bloccato',
    ];

    public function servizio_gps(): BelongsToMany {
        return $this->belongsToMany(TT_ServizioModel::class, 'TC_ServizioComponente', 'idComponente', 'idServizio')
            ->using(TC_ServizioComponenteModel::class)->wherePivotNotNull('idComponente');
    }

    public function servizio_tacho(): BelongsToMany {
        return $this->belongsToMany(TT_ServizioModel::class, 'TC_ServizioComponente', 'idTacho', 'idServizio')
            ->using(TC_ServizioComponenteModel::class)->wherePivotNotNull('idTacho');
    }

    public function servizio_radiocomando(): BelongsToMany {
        return $this->belongsToMany(TT_ServizioModel::class, 'TC_ServizioComponente', 'idRadiocomando', 'idServizio')
            ->using(TC_ServizioComponenteModel::class)->wherePivotNotNull('idRadiocomando');
    }

    public function servizi() {
        $this->servizi = array_merge(
            $this->servizio_gps()->get()->toArray(),
            $this->servizio_tacho()->get()->toArray(),
            $this->servizio_radiocomando()->get()->toArray(),
        );

        return $this;
    }

    public function countServizi() {
        $this->countServizi = count($this->servizi()->servizi);
        unset($this->servizi);
        return $this;
    }

    public function modello(): BelongsTo {
        return $this->belongsTo(TT_ModelloModel::class, 'idModello');
    }

    public function sim(): BelongsTo {
        return $this->belongsTo(TT_SimModel::class, 'idSim');
    }

    public function note(): HasMany {
        return $this->hasMany(TT_AnnotazioneModel::class, 'idRiferimento', 'id')->where('tabella', $this->table);
    }

    public function scopeRadiocomandi($query) {
        return $query->whereRaw("`idModello` in (SELECT `id` FROM `TT_Modello` WHERE `idTipologia` = 93)");
    }

    public function autisti(): BelongsToMany {
        return $this->belongsToMany(TT_AutistaModel::class, TC_AutistaComponenteModel::table(), 'idComponente', 'idAutista');
    }

    // public function get_idServizio()
}
