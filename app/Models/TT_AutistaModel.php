<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TT_AutistaModel extends Model {
    use SoftDeletes;

    protected $connection = 'mysql';

    protected $table = 'TT_Autista';

    public const RESOURCE_NAME = 'autisti';

    public const SINGULAR_NAME = 'autista';

    protected $fillable = [
        'autista'
    ];

    protected $hidden = [
        'idOperatore',
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    //* RELASHIONSHIP FUNCTIONS

    public static function table() {
        return (new self)->table;
    }

    public function operatore(): BelongsTo {
        return $this->belongsTo(TT_UtenteModel::class, 'idOperatore');
    }

    public function anagrafica(): BelongsTo {
        return $this->belongsTo(TT_AnagraficaModel::class, 'idAnagrafica');
    }

    public function componenti(): BelongsToMany {
        return $this->belongsToMany(TT_ComponenteModel::class, TC_AutistaComponenteModel::table(), 'idAutista', 'idComponente')
            ->using(TC_AutistaComponenteModel::class)
            ->withPivot([
                'id',
                'idOperatore'
            ])
            ->as('autista_componente');
    }
}
