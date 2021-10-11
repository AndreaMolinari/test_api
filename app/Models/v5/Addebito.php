<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Addebito extends Model
{
    use SoftDeletes, HasFactory;

    protected $connection = "mysql";

    protected $table = "TT_Addebito";

    protected $fillable = [
        'descrizione',
        'prezzoUnitario',
        'sconto',
        'iva',
    ];

    public function scopeFatturabili(Builder $b): Builder
    {
        return $b->whereDoesntHave('fatture');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Anagrafica::class, 'idAnagrafica');
    }

    public function fatture(): MorphToMany
    {
        return $this->morphToMany(Fattura::class, 'billable', 'TC_FatturaPart', null, 'idFattura');
        // return $this->belongsToMany(Fattura::class, 'TC_FatturaPart', )
    }
}
