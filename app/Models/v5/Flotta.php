<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flotta extends Model {
    protected $connection = 'mysql';

    public $table = 'TT_Flotta';

    protected $fillable = [
        'nome',
        'descrizione',
        'gruppo',
        'defaultIcon',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idOperatore',
    ];


    /**
     * @param Utente|int $utente
     */
    public function scopeByUtente(Builder $builder, $utente): Builder{
        /** @var int */
        $utente = $utente instanceof Utente ? $utente->id : $utente;
        return $builder->whereHas('flotta_utenti', function($b) use ($utente) {
            return $b->where('idUtente', $utente);
        });
    }

    public function servizi(): BelongsToMany {
        return $this->belongsToMany(Servizio::class, 'TC_FlottaServizio', 'idFlotta', 'idServizio', 'id', 'id')
            //? Attivi
            ->where('dataInizio', '<=', now())->where(function ($where) {
                $where->where('dataFine', '>=', now())->orWhereNull('dataFine');
            })
            ->withPivot(['nickname', 'icona'])
            ->as('flotta_servizio');
    }

    public function flotta_servizi(): HasMany {
        return $this->hasMany(FlottaServizio::class, 'idFlotta', 'id');
    }

    public function flotta_utenti(): HasMany {
        return $this->hasMany(UtenteFlotta::class, 'idRiferimento');
    }
}
