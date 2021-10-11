<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TT_ManutenzioneModel extends Model {
    protected $connection = 'mysql';

    public $table = 'TT_Manutenzione';

    protected $morphClass = 'TT_Manutenzione';

    protected $fillable = [
        'idServizio',
        'idTipologia',
        'idCampoAnagrafica',
        'idOfficina',
        'giorno_end',
        'data_ritiro',
        'giorno_start',
        "giorni_intervallo",
        'giorni_preavviso',
        "km_start",
        'km_fine',
        'km_intervallo',
        'km_preavviso',
        "ore_start",
        'ore_fine',
        'ore_intervallo',
        'ore_preavviso',
        'ore_lavoro',
        'sent_preavviso',
        'sent_scaduta',
        'prezzo',
        'ripeti',
        'custom_email_id',
        'idOperatore',
    ];

    protected $hidden = [
        'email_notifica',
        'idOperatore',
        'updated_at',
        'created_at',
    ];

    // protected $with = ['tipologia', 'servizio', 'campo_anagrafica', 'officina'];

    public function servizio(): BelongsTo {
        return $this->belongsTo(TT_ServizioModel::class, 'idServizio');
    }

    public function custom_tipologia(): BelongsTo {
        return $this->belongsTo(TT_CampoAnagraficaModel::class, 'idCampoAnagrafica')->withTrashed();
    }

    public function custom_officina(): BelongsTo {
        return $this->belongsTo(TT_CampoAnagraficaModel::class, 'idOfficina')->withTrashed();
    }

    public function custom_email(): BelongsTo {
        return $this->belongsTo(TT_CampoAnagraficaModel::class, 'custom_email_id')->withTrashed();
    }

    public function tipologia(): BelongsTo {
        return $this->belongsTo(TT_TipologiaModel::class, 'idTipologia');
    }

    public function note(): MorphMany {
        return $this->morphMany(TT_AnnotazioneModel::class, null, 'tabella', 'idRiferimento', 'id');
    }
}
