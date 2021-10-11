<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany};

class Indirizzo extends Model {
    use HasFactory;

    protected $connection = 'mysql';

    public $table = 'TT_Indirizzo';

    protected $fillable = [
        'istat',
        'provincia',
        'nazione',
        'comune',
        'cap',
        'via',
        'civico',
        'bloccato',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idOperatore',
    ];

    public function note(): HasMany {
        return $this->hasMany(TT_AnnotazioneModel::class, 'idRiferimento', 'id')->where('tabella', $this->table);
    }

    // !! METTERE BENE COME DA DOCS
    // public function annotazioni(): MorphMany {
    //     return $this->morphMany(Annotazione::class, 'TROVA NOME GIUSTO');
    // }
}
