<?php

namespace App\Models;

use App\Models\Targets\TT_TriggerEventoModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TT_ServizioModel extends Model {

    protected $connection = 'mysql';

    public $table = 'TT_Servizio';

    protected $fillable = [
        'idAnagrafica',
        'idPeriodo',
        'dataInizio',
        'dataFine',
        'dataSospInizio',
        'dataSospFine',
        'prezzo',
        'idCausale',
        'bloccato',
        'idOperatore',
    ];

    protected $hidden = [
        'updated_at',
        'created_at',
        'idOperatore',
    ];

    protected $with = ['gps', 'mezzo'];


    public function scopeAttivi(Builder $query): Builder {
        return $query->where('dataInizio', '<=', now())->where(function ($where) {
            $where->where('dataFine', '>=', now())->orWhereNull('dataFine');
        });
    }

    public function mezzo(): BelongsToMany {
        return $this->belongsToMany(TT_MezzoModel::class, 'TC_ServizioComponente', 'idServizio', 'idMezzo')
            ->using(TC_ServizioComponenteModel::class)
            // ->withPivot(['principale', 'parziale', 'prezzo', 'dataRestituzione'])
            ->as('servizioComponente')->wherePivotNotNull('idMezzo');
    }

    public function gps(): BelongsToMany {
        return $this->belongsToMany(TT_ComponenteModel::class, 'TC_ServizioComponente', 'idServizio', 'idComponente')
            ->using(TC_ServizioComponenteModel::class)
            ->withPivot(['principale', 'parziale', 'prezzo', 'dataRestituzione'])
            ->as('servizioComponente')->wherePivotNotNull('idComponente');
    }

    public function radiocomandi(): BelongsToMany {
        return $this->belongsToMany(TT_ComponenteModel::class, 'TC_ServizioComponente', 'idServizio', 'idRadiocomando')
            ->using(TC_ServizioComponenteModel::class)
            ->as('servizioComponente')->wherePivotNotNull('idRadiocomando');
    }

    public function tacho(): BelongsToMany {
        return $this->belongsToMany(TT_ComponenteModel::class, 'TC_ServizioComponente', 'idServizio', 'idTacho')
            ->using(TC_ServizioComponenteModel::class)
            ->withPivot(['principale', 'parziale'])
            ->as('servizioComponente')->wherePivotNotNull('idTacho');
    }

    public function manutenzioni(): HasMany {
        return $this->hasMany(TT_ManutenzioneModel::class, 'idServizio');
    }

    public function anagrafica(): BelongsTo {
        return $this->belongsTo(TT_AnagraficaModel::class, 'idAnagrafica');
    }

    public function flotte(): BelongsToMany {
        return $this->belongsToMany(TT_FlottaModel::class, 'TC_FlottaServizio', 'idServizio', 'idFlotta');
    }

    public function applicativi(): BelongsToMany {
        return $this->belongsToMany(TT_TipologiaModel::class, 'TC_ServizioApplicativo', 'idServizio', 'idTipologia')
            ->as('servizio_applicativo');
    }

    public function triggers_evento(): BelongsToMany {
        return $this->belongsToMany(TT_TriggerEventoModel::class, 'TC_TriggerEventoServizio', 'idServizio', 'idTriggerEvento')->as('trigger_evento_servizio');
    }

    /**
     * @return string|bool
     */
    public function get_unitcode() {
        return optional($this->gps()->orderBy('principale', 'DESC')->first())->unitcode ?? false;
    }
    /**
     * @return BelongsToMany
     */
    public function get_principale() {
        return $this->gps()->orderBy('principale', 'DESC');
    }
}
