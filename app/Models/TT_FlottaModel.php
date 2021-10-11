<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TT_FlottaModel extends Model {
    protected $connection = 'mysql';

    public $table = 'TT_Flotta';

    protected $fillable = [
        'nome',
        'descrizione',
        'gruppo',
        'idOperatore',
        'defaultIcon'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idOperatore',
    ];

    public function servizi(): BelongsToMany {
        return $this->belongsToMany(TT_ServizioModel::class, 'TC_FlottaServizio', 'idFlotta', 'idServizio', 'id', 'id')
            // ->whereRaw(' (dataFine IS NULL or dataFine >= now() ) AND dataInizio <= now()  ') // ma cosi mi fa cagare
            ->where('dataInizio', '<=', now())->where(function ($where) {
                $where->where('dataFine', '>=', now())->orWhereNull('dataFine');
            })
            ->withPivot('nickname', 'icona');
    }

    public function servizi_pivot(): HasMany {
        return $this->hasMany(TC_FlottaServizioModel::class, 'idFlotta', 'id');
    }

    // public function servizi(): BelongsToMany
    // {
    //     return $this ->belongsToMany(TT_ServizioModel::class, 'TC_FlottaServizio', 'idFlotta', 'idServizio', 'id', 'id')
    //     ->whereRaw(' (dataFine IS NULL or dataFine >= now() )  ') // ma cosi mi fa cagare
    //     ->where('dataInizio', '<=', now())
    //     ->withPivot('nickname', 'icona');
    // }

    public function utenti(): HasMany {
        return $this->hasMany(TC_UtenteFlottaModel::class, 'idRiferimento');
    }
}
