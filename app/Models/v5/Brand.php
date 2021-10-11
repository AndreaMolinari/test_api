<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model {
    use HasFactory;

    protected $connection = 'mysql';

    public $table = 'TT_Brand';

    protected $fillable = [
        'nome',
    ];

    protected $hidden = [
        'marca',
        'created_at',
        'updated_at',
        'idOperatore',
        'idFornitore',
    ];

    protected $appends = [
        'nome',
    ];

    public function getNomeAttribute(): string {
        return $this->marca;
    }

    public function setNomeAttribute($value): void {
        $this->attributes['marca'] = $value;
    }

    public function modelli(): HasMany {
        return $this->hasMany(Modello::class, 'idBrand');
    }
}
