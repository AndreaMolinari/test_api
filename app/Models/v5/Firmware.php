<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Firmware extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';

    protected $table = 'TT_Firmware';

    protected $fillable = [
        'revision',
        'version',
        // 'configuration_path',
        // 'firmware_path',
    ];

    public function modello(): BelongsTo
    {
        return $this->belongsTo(Modello::class, 'idModello');
    }

    public function componenti(): HasMany
    {
        return $this->hasMany(Componente::class, 'idFirmware');
    }
}
