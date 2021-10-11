<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class PersonalizzazioniRivenditore extends Model {
    protected $connection = 'mysql';

    protected $table = 'TT_PersonalizzazioniRivenditore';

    protected $fillable = [
        'colorGest',
        'mapAvail',
        'logoData',
        'platformUrl',
    ];

    protected $hidden = [
        'updated_at',
        'created_at',
        'idOperatore',
        'idAnagrafica',
    ];

    protected $casts = [
        'mapAvail' => 'json',
    ];

    protected static function booted() {
        static::creating(function (PersonalizzazioniRivenditore $pr) {
            $pr->idOperatore = Auth::id();
        });
    }

    /**
     * @param Anagrafica|int $anagrafica
     */
    public function scopeByAnagrafica(Builder $builder, $anagrafica): Builder {
        return $builder->where('idAnagrafica', $anagrafica instanceof Anagrafica ? $anagrafica->id : $anagrafica);
    }

    public function anagrafica(): BelongsTo {
        return $this->belongsTo(Anagrafica::class, 'idAnagrafica');
    }

    public function operatore(): BelongsTo {
        return $this->belongsTo(Utente::class, 'idOperatore');
    }
}
