<?php

namespace App\Models\Targets;

use App\Events\InOutTargetEvent;
use App\Events\InTargetEvent;
use App\Events\OutTargetEvent;
use App\Models\TT_ServizioModel;
use App\Models\TT_TipologiaModel;
use App\Models\TT_UtenteModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TT_TriggerEventoModel extends Model {
    // use AsPivot;
    protected $connection = 'mysql';
    protected $table = 'TT_TriggerEvento';

    protected $fillable = [
        'trigger_id',
        'trigger_type',
        'action_id',
        'action_type',
        'cambiaUscita',
        'idTipologiaEvento',
    ];

    protected $hidden = [
        'idTipologiaEvento',
        'idOperatore',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'cambiaUscita' => 'boolean',
    ];

    public function getEventClassAttribute(): ?string {
        switch ($this->idTipologiaEvento) {
            case 121: // In
                return InTargetEvent::class;
            case 122: // Out
                return OutTargetEvent::class;
            case 123: // In/Out
                return InOutTargetEvent::class;
            case 124: // Industria 4.0
                return null;
        }
    }

    public function trigger(): MorphTo {
        return $this->morphTo();
    }

    public function servizi(): BelongsToMany {
        return $this->belongsToMany(TT_ServizioModel::class, 'TC_TriggerEventoServizio', 'idTriggerEvento', 'idServizio')->as('trigger_evento_servizio');
    }

    public function action(): MorphTo {
        return $this->morphTo();
    }

    public function evento(): BelongsTo {
        return $this->belongsTo(TT_TipologiaModel::class, 'idTipologiaEvento');
    }

    public function operatore(): BelongsTo {
        return $this->belongsTo(TT_UtenteModel::class, 'idOperatore');
    }
}
