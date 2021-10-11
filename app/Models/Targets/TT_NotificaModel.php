<?php

namespace App\Models\Targets;

use App\Models\{TT_ContattoModel, TT_UtenteModel};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TT_NotificaModel extends Model {
    
    const TABLE_NAME = 'TT_Notifica';

    protected $connection = 'mysql';
    protected $table = 'TT_Notifica';

    protected $fillable = [
        // 'usaEmailUtente',
        'messaggioCustom',
    ];

    protected $hidden = [
        'idUtente',
        'idOperatore',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'usaEmailUtente' => 'boolean',
    ];

    public function triggers_evento(): MorphMany {
        return $this->morphMany(TT_TriggerEventoModel::class, 'action');
    }

    public function utente(): BelongsTo {
        return $this->belongsTo(TT_UtenteModel::class, 'idUtente');
    }

    public function contatti(): BelongsToMany {
        return $this->belongsToMany(TT_ContattoModel::class, 'TC_NotificaContatto', 'idNotifica', 'idContatto')->whereNull('idParent')->whereNotNull('idUtente');
    }
}
