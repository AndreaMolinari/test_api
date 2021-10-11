<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Autista extends Model {
    use SoftDeletes;

    protected $connection = 'mysql';

    protected $table = 'TT_Autista';

    protected $fillable = [
        'autista'
    ];

    protected $hidden = [
        'idOperatore',
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    public function operatore(): BelongsTo {
        return $this->belongsTo(Utente::class, 'idOperatore');
    }

    public function anagrafica(): BelongsTo {
        return $this->belongsTo(Anagrafica::class, 'idAnagrafica');
    }

    public function componenti(): BelongsToMany {
        return $this->belongsToMany(Componente::class, 'TC_AutistaComponente', 'idAutista', 'idComponente')
            ->using(AutistaComponente::class)
            ->withPivot([
                'id',
                'idOperatore'
            ])
            ->as('autista_componente');
    }
}
