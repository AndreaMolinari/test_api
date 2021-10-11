<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modello extends Model {
    use HasFactory;
    
    protected $connection = 'mysql';

    public $table = 'TT_Modello';

    protected $fillable = [
        'nome',
        'batteria',
    ];
    
    protected $hidden = [
        'modello',
        'created_at',
        'updated_at',
        'idOperatore',
        'idBrand',
        'idTipologia',
    ];

    protected $appends = [
        'nome',
    ];

    // protected $with = ['brand', 'tipologia'];

    public function getNomeAttribute(): string {
        return $this->modello;
    }

    public function setNomeAttribute($value): void {
        $this->attributes['modello'] = $value;
    }


    public function brand(): BelongsTo {
        return $this->belongsTo(Brand::class, 'idBrand');
    }

    public function componenti(): HasMany {
        return $this->hasMany(Componente::class, 'idModello');
    }

    public function mezzi(): HasMany {
        return $this->hasMany(Mezzo::class, 'idModello');
    }

    public function sims(): HasMany {
        return $this->hasMany(Sim::class, 'idModello');
    }

    public function tipologia(): BelongsTo {
        return $this->belongsTo(Tipologia::class, 'idTipologia');
    }
}
