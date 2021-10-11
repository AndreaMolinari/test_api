<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TT_ModelloModel extends Model {
    protected $connection = 'mysql';
    public $table = 'TT_Modello';
    protected $fillable = [
        'idBrand',
        'idTipologia',
        'modello',
        'idOperatore',
        'batteria'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idOperatore'
    ];

    protected $with = ['brand', 'tipologia'];

    public function brand(): BelongsTo {
        return $this->belongsTo(TT_BrandModel::class, 'idBrand');
    }

    public function componente(): HasMany {
        return $this->hasMany(TT_ComponenteModel::class, 'idModello');
    }

    public function mezzo(): HasMany {
        return $this->hasMany(TT_MezzoModel::class, 'idModello');
    }

    public function sim(): HasMany {
        return $this->hasMany(TT_SimModel::class, 'idModello');
    }

    public function tipologia(): BelongsTo {
        return $this->belongsTo(TT_TipologiaModel::class, 'idTipologia');
    }
}
