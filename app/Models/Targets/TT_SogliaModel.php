<?php

namespace App\Models\Targets;

use App\Models\{TT_TipologiaModel, TT_UtenteModel};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TT_SogliaModel extends Model {
    const TABLE_NAME = 'TT_Soglia';

    protected $connection = 'mysql';
    protected $table = 'TT_Soglia';

    protected $fillable = [
        'inizio',
        'fine'
    ];

    protected $hidden = [
        'idTipologia',
        'idUtente',
        'idOperatore',
        'created_at',
        'updated_at',
    ];

    /**
     * Scope a query to only include users of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int|TT_TipologiaModel  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType(Builder $query, $type): Builder {
        $id = null;

        if ($type instanceof TT_TipologiaModel)
            $id = $type->id;
        else
            $id = $type;

        return $query->where('idTipologia', $id);
    }

    public function tipologia(): BelongsTo {
        return $this->belongsTo(TT_TipologiaModel::class, 'idTipologia');
    }

    public function utente(): BelongsTo {
        return $this->belongsTo(TT_UtenteModel::class, 'idUtente');
    }

    public function triggers(): MorphMany {
        return $this->morphMany(TT_NotificaTargetModel::class, 'trigger');
    }

    public function triggers_evento(): MorphMany {
        return $this->morphMany(TT_TriggerEventoModel::class, 'trigger');
    }
}
