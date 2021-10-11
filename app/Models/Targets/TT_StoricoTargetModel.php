<?php

namespace App\Models\Targets;

use App\Models\TT_ServizioModel;
use App\Models\TT_TipologiaModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TT_StoricoTargetModel extends Model {
    protected $connection = 'mysql';
    protected $table = 'TT_StoricoTarget';

    protected $fillable = [
        'trigger_id',
        'trigger_type',
        'posizione'
    ];

    protected $hidden = [
        'idServizio',
        'idTipologia',
        'idOperatore',
        'trigger_id',
        'trigger_type',
        'rawPositionJson',
        'created_at',
        'updated_at',
    ];

    /**
     * Aggiunge il mio custom accessor automaticamente
     */
    protected $appends = ['posizione'];

    /**
     * @return array|object|null
     */
    public function getPosizioneAttribute() {
        return json_decode($this->rawPositionJson);
    }

    /**
     * @param array|object|null Il geosjon
     * @return void
     */
    public function setPosizioneAttribute($posizione) {
        $this->attributes['rawPositionJson'] = json_encode($posizione);
    }

    public function tipologia(): BelongsTo {
        return $this->belongsTo(TT_TipologiaModel::class, 'idTipologia');
    }

    public function servizio(): BelongsTo {
        return $this->belongsTo(TT_ServizioModel::class, 'idServizio');
    }

    public function trigger(): MorphTo {
        return $this->morphTo();
    }
}
