<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo};

class Fatturazione extends Model
{
    use HasFactory;
    
    protected $connection = 'mysql';

    protected $table = 'TT_AnagraficaFatturazione';

    protected $fillable = [
        'sdi',
        'splitPA',
        'esenteIVA',
        'speseIncasso',
        'speseSpedizione',
        'banca',
        'filiale',
        'iban',
        'iban_abi',
        'iban_cab',
        'iban_cin',
        'pec',
        'mail',
        'bloccato',
    ];

    protected $hidden = [
        'updated_at',
        'created_at',
        'idOperatore',
        'idAnagrafica',
        'idModalita',
        'idPeriodo',
    ];

    protected $with = [
        'modalita',
        'periodo',
    ];

    public function anagrafica(): BelongsTo{
        return $this->belongsTo(Anagrafica::class, 'idAnagrafica');
    }

    public function modalita(): BelongsTo{
        return $this->belongsTo(Tipologia::class, 'idModalita')->where('idParent', 38);
    }

    public function periodo(): BelongsTo{
        return $this->belongsTo(Tipologia::class, 'idPeriodo')->where('idParent', 39);
    }

    public function operatore(): BelongsTo{
        return $this->belongsTo(Anagrafica::class, 'idOperatore');
    }
}
