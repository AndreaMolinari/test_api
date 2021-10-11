<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FatturaPart extends MorphPivot
{
    use SoftDeletes;

    protected $connection = "mysql";

    protected $table = "TC_FatturaPart";

    protected $fillable = [
        'billable_type',
        'billable_id',
        'idOperatore',
        'prezzoUnitario',
        'sconto',
        'iva',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'prezzoScontato',
        'imposta'
    ];

    protected $casts = [
        'prezzoUnitario' => 'double'
    ];

    public function getPrezzoScontatoAttribute()
    {
        return round($this->prezzoUnitario * (1 - ($this->sconto / 100)), 2);
    }

    public function getImpostaAttribute()
    {
        return round($this->prezzoScontato * ($this->iva / 100), 2);
    }

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }
}
