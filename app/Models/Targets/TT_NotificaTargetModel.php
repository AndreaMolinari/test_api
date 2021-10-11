<?php

namespace App\Models\Targets;

use App\Models\{TT_CampoAnagraficaModel, TT_TipologiaModel};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TT_NotificaTargetModel extends Model {
    protected $connection = 'mysql';
    protected $table = 'TT_NotificaTarget';

    protected $fillable = [
        'observable_id',
        'observable_type',
        'trigger_id',
        'trigger_type',
        // 'usaEmailUtente',
        'messaggioCustom',
    ];

    protected $hidden = [
        'idCampoAnagrafica',
        'idTipologia',
        'idOperatore',
        'observable_id',
        'observable_type',
        'trigger_id',
        'trigger_type',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'usaEmailUtente' => 'boolean',
    ];

    public function campo_notifica(): BelongsTo {
        return $this->belongsTo(TT_CampoAnagraficaModel::class, 'idCampoAnagrafica');
    }

    public function tipologia(): BelongsTo {
        return $this->belongsTo(TT_TipologiaModel::class, 'idTipologia');
    }

    public function trigger(): MorphTo {
        return $this->morphTo();
    }

    public function observable(): MorphTo {
        return $this->morphTo();
    }
}
