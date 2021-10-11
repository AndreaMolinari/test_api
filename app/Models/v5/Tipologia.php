<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tipologia extends Model {
    protected $connection = 'mysql';

    public $table = 'TT_Tipologia';

    protected $fillable = [
        'nome',
        'descrizione',
        'bloccato',
    ];

    protected $hidden = [
        'tipologia',
        'bloccato',
        'deleted',
        'idUpdated',
        'updated_at',
        'created_at',
        'idOperatore'
    ];

    protected $appends = [
        'nome',
    ];

    protected $casts = [
        'data' => 'json',
    ];

    public function getNomeAttribute(): string {
        return $this->tipologia;
    }

    public function setNomeAttribute($value): void {
        $this->attributes['tipologia'] = $value;
    }

    public function anagrafiche(): BelongsToMany {
        return $this->belongsToMany(Anagrafica::class, 'TC_AnagraficaTipologia', 'idTipologia', 'idAnagrafica');
    }

    public function servizi(): BelongsToMany {
        return $this->belongsToMany(TT_ServizioModel::class, 'TC_ServizioApplicativo', 'idTipologia', 'idServizio')
            ->as('servizio_applicativo');
    }

    public function children(): HasMany {
        return $this->hasMany(Tipologia::class, 'idParent');
    }

    public function descendants(): HasMany {
        return $this->children()->with('descendants');
    }

    public function parent(): BelongsTo {
        return $this->belongsTo(Tipologia::class, 'idParent');
    }

    public function ancestors(): BelongsTo {
        return $this->parent()->with('ancestors');
    }

    public function ruolo(): HasOne {
        return $this->hasOne(RolesTipologia::class, 'idTipologia');
    }

    public function modelli(): HasOne {
        return $this->hasOne(Modello::class, 'idTipologia');
    }
}
