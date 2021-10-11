<?php

namespace App\Models\Targets;

use App\Models\TT_ServizioModel;
use App\Models\TT_TipologiaModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TT_StoricoEventoModel extends Model {
    protected $connection = 'mysql';
    protected $table = 'TT_StoricoEvento';

    protected $fillable = [
        'posizione'
    ];

    protected $hidden = [
        'idServizio',
        'idTipologiaEvento',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'posizione' => 'json',
    ];

    public function servizio(): BelongsTo {
        return $this->belongsTo(TT_ServizioModel::class, 'idServizio');
    }

    public function evento(): BelongsTo {
        return $this->belongsTo(TT_TipologiaModel::class, 'idTipologiaEvento');
    }

    public function trigger(): MorphTo {
        return $this->morphTo();
    }
}
