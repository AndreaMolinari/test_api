<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany};

class TT_IndirizzoModel extends Model {
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
        'idOperatore'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idOperatore',
    ];

    public function note(): HasMany {
        return $this->hasMany(TT_AnnotazioneModel::class, 'idRiferimento', 'id')->where('tabella', $this->table);
    }
}
