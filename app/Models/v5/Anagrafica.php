<?php

namespace App\Models\v5;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Laravel\Scout\Searchable;

/**
 * @OA\Schema(
 *     title="Anagrafica",
 *     description="Modello di Anagrafica",
 *     @OA\Xml(
 *         name="Anagrafica"
 *     )
 * )
 */
class Anagrafica extends Model
{
    // use Searchable, HasFactory;

    protected $connection = 'mysql';

    protected $table = 'TT_Anagrafica';

    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @property integer
     */
    private $id;

    /**
     * @OA\Property(
     *      title="idGenere",
     *      description="ID del sesso dell'anagrafica",
     *      format="int64",
     *      example="1"
     * )
     *
     * @property string
     */
    private $idGenere;

    /**
     * @OA\Property(
     *      title="Nome",
     *      example="Mario"
     * )
     *
     * @property string
     */
    private $nome;

    /**
     * @OA\Property(
     *      title="Cognome",
     *      example="Rossi"
     * )
     *
     * @property string
     */
    private $cognome;

    /**
     * @OA\Property(
     *      title="Codice Fiscale",
     *      example="XXXXXX00X00X000X"
     * )
     *
     * @property string
     */
    private $codFisc;

    /**
     * @OA\Property(
     *      title="Data di Nascita",
     *      example="2000-01-01"
     * )
     *
     * @property Carbon
     */
    private $dataNascita;

    /**
     * @OA\Property(
     *      title="Ragione Sociale",
     *      example="Mario Rossi S.P.A."
     * )
     *
     * @property string
     */
    private $ragSoc;

    /**
     * @OA\Property(
     *      title="Partita IVA",
     *      example="00000000000"
     * )
     *
     * @property string
     */
    private $pIva;

    /**
     * @OA\Property(
     *      title="Referente Legale",
     *      example="Avvocato Avvocato"
     * )
     *
     * @property string
     */
    private $referenteLegale;

    /**
     * @OA\Property(
     *      title="idAgente",
     *      description="ID anagrafica Agente rivenditore",
     *      format="int64",
     *      example="1"
     * )
     *
     * @property string
     */
    private $idAgente;

    /**
     * @OA\Property(
     *      title="idCommerciale",
     *      description="ID anagrafica Commerciale di riferimento",
     *      format="int64",
     *      example="1"
     * )
     *
     * @property string
     */
    private $idCommerciale;

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

    protected $casts = [
        'dataNascita' => 'date'
    ];

    public function getNominativoAttribute()
    {
        return $this->attributes['ragSoc'] ?? $this->attributes['nome'] . ' ' . $this->attributes['cognome'];
    }

    // public function searchableAs()
    // {
    //     return 'anagrafiche';
    // }

    // public function toSearchableArray()
    // {
    //     $array = $this->toArray();

    //     $array['contatti'] = $this->rubriche->pluck('contatti.*.contatto')->flatten()->unique()->values();

    //     $array['genere'] = $this->genere->tipologia;

    //     $array['tipologie'] = $this->tipologie->map(function ($data) {
    //         return $data['tipologia'];
    //     })->toArray();

    //     $array['utenti_username'] = $this->utenti->map(function ($data) {
    //         return $data['username'];
    //     })->toArray();

    //     $array['utenti_mail'] = $this->utenti->map(function ($data) {
    //         return $data['mail'];
    //     })->toArray();

    //     if ($this->fatturazione && $this->fatturazione->iban) {
    //         $array['fatturazione_iban'] = $this->fatturazione->iban;
    //     }

    //     if ($this->parent) {
    //         $array['parent_nome'] = $this->parent->nome;

    //         $array['parent_cognome'] = $this->parent->cognome;

    //         $array['parent_codFisc'] = $this->parent->codFisc;

    //         $array['parent_pIva'] = $this->parent->pIva;

    //         $array['parent_ragSoc'] = $this->parent->ragSoc;
    //     }
    //     //? Unsetto tutto quello che uso con un'altra chiave!
    //     unset($array['fatturazione'], $array['utenti'], $array['parent'], $array['rubriche']);
    //     // Storage::put('test.json', json_encode($array, JSON_PRETTY_PRINT));
    //     return $array;
    // }

    // protected function makeAllSearchableUsing($query)
    // {
    //     return $query->with('parent', 'genere', 'tipologie', 'utenti', 'fatturazione', 'rubriche.contatti');
    //     // return $query->with('parent', 'genere', 'tipologie', 'utenti', 'fatturazione');
    // }

    /**
     * @return HasMany|BelongsToMany
     */
    public function servizi()
    {
        return $this->hasMany(Servizio::class, 'idAnagrafica');
    }

    public function parent(): HasOneThrough
    {
        return $this->hasOneThrough(Anagrafica::class, AnagraficaAnagrafica::class, 'idChild', 'id', 'id', 'idParent');
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(Anagrafica::class, 'TC_AnagraficaAnagrafica', 'idChild', 'idParent')->withPivot('idTipologia')->using(AnagraficaAnagrafica::class)->as('anagrafica_anagrafica');
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Anagrafica::class, 'TC_AnagraficaAnagrafica', 'idParent', 'idChild');
    }

    /**
     * Get the genere that owns the Anagrafica
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function genere(): BelongsTo
    {
        return $this->belongsTo(Tipologia::class, 'idGenere')->select('id', 'tipologia');
    }

    /**
     * Get the tipologie that owns the Anagrafica
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipologie(): BelongsToMany
    {
        return $this->belongsToMany(Tipologia::class, 'TC_AnagraficaTipologia', 'idAnagrafica', 'idTipologia');
    }

    /**
     * Get all of the utenti for the Anagrafica
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function utenti(): HasMany
    {
        return $this->hasMany(Utente::class, 'idAnagrafica');
    }

    /**
     * Get the fatturazione associated with the Anagrafica
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function fatturazione(): HasOne
    {
        return $this->hasOne(Fatturazione::class, 'idAnagrafica');
    }

    public function rubriche(): HasMany
    {
        return $this->hasMany(Rubrica::class, 'idAnagrafica')->whereNull('idParent');
    }

    /**
     * The indirizzi that belong to the Anagrafica
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function indirizzi(): BelongsToMany
    {
        return $this->belongsToMany(Indirizzo::class, 'TC_AnagraficaIndirizzo', 'idAnagrafica', 'idIndirizzo');
    }

    public function sede_legale(): HasOneThrough
    {
        return $this->hasOneThrough(Indirizzo::class, AnagraficaIndirizzo::class, 'idAnagrafica', 'id', null, 'idIndirizzo')->where('idTipologia', 17);
    }

    /**
     * Get all of the fatture for the Anagrafica
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fatture(): HasMany
    {
        return $this->hasMany(Fattura::class, 'idAnagrafica');
    }

    public function addebiti(): HasMany{
        return $this->hasMany(Addebito::class, 'idAnagrafica');
    }

    /**
     * Get all of the comments for the Anagrafica
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ddts(): HasMany
    {
        return $this->hasMany(DDT::class, 'idCliente');
    }

    /**
     * Get all of the comments for the Anagrafica
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ddts_courier(): HasMany
    {
        return $this->hasMany(DDT::class, 'idTrasportatore');
    }

    /**
     * Servizi che l'installatore ha installato.
     */
    public function installati(): BelongsToMany
    {
        return $this->belongsToMany(Servizio::class, 'TC_ServizioInstallatore', 'idAnagrafica', 'idServizio')
            ->as('servizio_installatore')->withPivot(['dataInstallazione']);
    }
}
