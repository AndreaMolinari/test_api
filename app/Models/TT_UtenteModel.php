<?php

namespace App\Models;

use App\Http\Controllers\TC_RolesTipologiaController;
use App\Models\Targets\TT_NotificaModel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class TT_UtenteModel extends Authenticatable {
    use HasApiTokens, Notifiable;

    protected $connection = 'mysql';

    public $table = 'TT_Utente';

    protected $fillable = [
        'idAnagrafica',
        'idParent',
        'email',
        'username',
        'password',
        'dataStart',
        'dataEnd',
        'actiaMail',
        'actiaUser',
        'actiaPassword',
        'bloccato',
        'idOperatore',
        'idTipologia',
        'password_dec'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function anagrafica(): BelongsTo {
        return $this->belongsTo(TT_AnagraficaModel::class, 'idAnagrafica');
    }

    public function flotte(): BelongsToMany {
        return $this->belongsToMany(TT_FlottaModel::class, 'TC_UtenteFlotta', 'idUtente', 'idRiferimento')->as('utente_flotta');
    }

    public function tipologia(): BelongsTo {
        return $this->belongsTo(TT_TipologiaModel::class, 'idTipologia');
    }

    public function contatti(): HasMany {
        return $this->hasMany(ContattoModel::class, 'idUtente')->whereNull('idParent')->whereNotNull('idUtente');
    }

    public function notifiche(): HasMany {
        return $this->hasMany(TT_NotificaModel::class, 'idUtente');
    }

    public function getRoleLevel() {
        $controller = new TC_RolesTipologiaController;
        $escalation = $controller->getallcleaned();

        if (isset($this->idTipologia)) {
            if (property_exists($escalation, $this->idTipologia)) {
                $tipologia = $this->idTipologia;

                return $escalation->$tipologia->Roles;
            }
        }

        return false;
    }
}
