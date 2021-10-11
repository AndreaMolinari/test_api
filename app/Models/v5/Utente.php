<?php

namespace App\Models\v5;

use App\Http\Controllers\TC_RolesTipologiaController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Utente extends Authenticatable {
    use HasApiTokens, Notifiable, HasFactory;

    protected $connection = 'mysql';

    public $table = 'TT_Utente';

    protected $fillable = [
        'email',
        'username',
        'password',
        'data_inizio',
        'data_fine',
        'actiaMail',
        'actiaUser',
        'actiaPassword',
        'bloccato',
        'password_dec'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function anagrafica(): BelongsTo {
        return $this->belongsTo(Anagrafica::class, 'idAnagrafica');
    }

    public function flotte(): BelongsToMany {
        return $this->belongsToMany(Flotta::class, 'TC_UtenteFlotta', 'idUtente', 'idRiferimento')->as('utente_flotta');
    }

    public function tipologia(): BelongsTo {
        return $this->belongsTo(Tipologia::class, 'idTipologia');
    }

    public function ruolo(): HasOne
    {
        return $this->hasOne(RolesTipologia::class, 'idTipologia', 'idTipologia');
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
