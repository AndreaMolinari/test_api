<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class AnagraficaAnagrafica extends Model
{
    use AsPivot;


    protected $connection = 'mysql';

    public $table = 'TC_AnagraficaAnagrafica';

    protected $fillable = [];

    protected $hidden = [
        'idOperatore',
        'create',
        'updated_at'
    ];

    protected $with = ['tipologia'];

    /**
     * Get the user that owns the AnagraficaAnagrafica
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipologia(): BelongsTo
    {
        return $this->belongsTo(Tipologia::class, 'idTipologia');
    }
}
