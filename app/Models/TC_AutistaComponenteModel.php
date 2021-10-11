<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class TC_AutistaComponenteModel extends Model {
    use AsPivot;

    protected $connection = 'mysql';

    protected $table = 'TC_AutistaComponente';

    public const RESOURCE_NAME = 'autista_componente';

    public const SINGULAR_NAME = 'autista_componente';

    protected $guarded = [];

    public static function table() {
        return (new self)->table;
    }

    //* RELASHIONSHIP FUNCTIONS

    public function operatore(): BelongsTo {
        return $this->belongsTo(UtenteModel::class, 'idOperatore');
    }
}
