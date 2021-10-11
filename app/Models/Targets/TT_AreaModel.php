<?php

namespace App\Models\Targets;

use App\Models\{TT_UtenteModel};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class TT_AreaModel extends Model {
    use SoftDeletes;

    const TABLE_NAME = 'TT_Area';

    protected $connection = 'mysql';
    protected $table = 'TT_Area';

    protected $fillable = [
        'nome',
        'gruppo',
        // 'rawGeoJson',
        'geo_json'
    ];

    protected $hidden = [
        'rawGeoJson',
        'idParent',
        'idUtente',
        'idOperatore',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'geo_json' => 'object'
    ];

    public function scopeUtente(Builder $query, int $idUtente = null): Builder {
        return $query->where('idUtente', $idUtente ?? Auth::id());
    }

    public function scopeFlotta(Builder $query, int $idFlotta = null): Builder {
        return $query->whereHas('notificheFlotta', function (Builder $has) use ($idFlotta) {
            return $has->where('observable_id', $idFlotta);
        });
    }

    public function scopeServizio(Builder $query, int $idServizio = null): Builder {
        return $query->whereHas('notificheServizio', function (Builder $has) use ($idServizio) {
            return $has->where('observable_id', $idServizio);
        });
    }

    public function parent(): BelongsTo {
        return $this->belongsTo(TT_AreaModel::class, 'idParent');
    }

    public function ancestors(): BelongsTo {
        return $this->parent()->with('ancestors');
    }

    public function utente(): BelongsTo {
        return $this->belongsTo(TT_UtenteModel::class, 'idUtente');
    }

    public function notifiche(): MorphMany {
        return $this->morphMany(TT_NotificaTargetModel::class, 'trigger');
    }

    public function  notificheFlotta(): MorphMany {
        return $this->morphMany(TT_NotificaTargetModel::class, 'trigger')->where('observable_type', 'TT_Flotta');
    }

    public function notificheServizio(): MorphMany {
        return $this->morphMany(TT_NotificaTargetModel::class, 'trigger')->where('observable_type', 'TT_Servizio');
    }

    public function triggers_evento(): MorphMany {
        return $this->morphMany(TT_TriggerEventoModel::class, 'trigger');
    }

    public function storici_evento(): MorphMany {
        return $this->morphMany(TT_StoricoEventoModel::class, 'trigger');
    }
}
