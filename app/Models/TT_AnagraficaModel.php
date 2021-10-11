<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\CustomCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Storage;

class TT_AnagraficaModel extends Model {

    protected $connection = 'mysql';

    public $table = 'TT_Anagrafica';

    protected $fillable = [
        'idGenere',
        'nome',
        'codFisc',
        'cognome',
        'dataNascita',
        'pIva',
        'referenteLegale',
        'ragSoc',
        'idAgente',
        'idCommerciale',
        'bloccato',
        'idOperatore'
    ];

    protected $hidden = [
        'updated_at',
        'created_at',
        'idOperatore',
    ];

    public function servizi(): HasMany {
        return $this->hasMany(TT_ServizioModel::class, 'idAnagrafica');
    }

    public function parent(): HasOneThrough {
        return $this->hasOneThrough(TT_AnagraficaModel::class, TC_AnagraficaAnagraficaModel::class, 'idChild', 'id', 'id', 'idParent');
    }

    public function genere(): BelongsTo {
        return $this->belongsTo(TT_TipologiaModel::class, 'idGenere')->select('id', 'tipologia');
    }

    public function tipologia(): BelongsToMany {
        return $this->belongsToMany(TT_TipologiaModel::class, 'TC_AnagraficaTipologia', 'idAnagrafica', 'idTipologia');
    }

    public function utenti(): HasMany {
        return $this->hasMany(TT_UtenteModel::class, 'idAnagrafica');
    }

    public function fatturazione(): HasOne {
        return $this->hasOne(TT_AnagraficaFatturazioneModel::class, 'idAnagrafica');
    }

    public function rubriche(): HasMany {
        return $this->hasMany(RubricaModel::class, 'idAnagrafica')->whereNull('idParent');
    }
}
