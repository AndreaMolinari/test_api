<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TT_CampoAnagraficaModel extends Model {
    use SoftDeletes;

    protected $connection = 'mysql';
    public $table = 'TT_CampoAnagrafica';
    protected $fillable = [
        'nome',
        'descrizione',
        'idAnagrafica',
        'idTipologia',
        'idOperatore'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idOperatore',
    ];

    public function tipologia(): BelongsTo {
        return $this->belongsTo(TT_TipologiaModel::class, 'idTipologia');
    }

    public function anagrafica(): BelongsTo {
        return $this->belongsTo(TT_AnagraficaModel::class, 'idAnagrafica');
    }

}

