<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TT_AnagraficaFatturazioneModel extends Model {
    protected $connection = 'mysql';
    public $table = 'TT_AnagraficaFatturazione';

    protected $fillable = [
        'id',
        'idModalita',
        'idPeriodo',
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
        'idAnagrafica',
        'bloccato',
        'idOperatore'
    ];
}
