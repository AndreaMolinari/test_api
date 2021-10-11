<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TT_BrandModel extends Model {
    protected $connection = 'mysql';

    public $table = 'TT_Brand';

    protected $fillable = [
        'marca',
        'idFornitore',
        'idOperatore',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'idOperatore'
    ];

    public function modelli(): HasMany {
        return $this->hasMany(TT_ModelloModel::class, 'idBrand');
    }
}
