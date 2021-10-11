<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class RubricaModel extends Model {

    protected $connection = 'mysql';

    public $table = 'TT_Contatto';

    protected $fillable = [
        // 'idAnagrafica',
        'nome',
        'descrizione',
        // 'idOperatore',
    ];

    protected $visible = [
        'id',
        'nome',
        'descrizione',
        'contatti',
    ];

    protected $with = ['contatti'];

    public function contatti(): HasMany {
        return $this->hasMany(ContattoModel::class, 'idParent');
    }
}
