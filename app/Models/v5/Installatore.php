<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Laravel\Scout\Searchable;

class Installatore extends Anagrafica
{
    use Searchable;

    protected $connection = 'mysql';

    protected $table = 'TT_Anagrafica';

    protected $fillable = [
        'nome',
        'codFisc',
        'cognome',
        'dataNascita',
        'pIva',
        'referenteLegale',
        'ragSoc',
        'bloccato',
    ];

    protected $hidden = [
        'updated_at',
        'created_at',
        'idOperatore',
        'idAgente',
        'idCommerciale',
        'idGenere',
    ];

    public function searchableAs()
    {
        return 'installatore';
    }

    public function servizi(): BelongsToMany
    {
        return $this->belongsToMany(Servizio::class, 'TC_ServizioInstallatore', 'idInstallatore', 'idServizio')
        ->as('servizio_installatore')
        ->withPivot('data_installazione');
    }
}
