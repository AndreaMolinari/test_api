<?php

namespace App\Models\v5;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class Servizio extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    public $table = 'TT_Servizio';


    /**
     * @OA\Property(
     *      title="Data inizio erogazione servizio",
     *      example="2000-01-01"
     * )
     *
     * @var Carbon
     */
    private $dataInizio;

    /**
     * @OA\Property(
     *      title="Data fine erogazione servizio",
     *      example="2000-01-01"
     * )
     *
     * @var Carbon
     */
    private $dataFine;

    /**
     * @OA\Property(
     *      title="Data inizio sospensione di pagamento",
     *      example="2000-01-01"
     * )
     *
     * @var Carbon
     */
    private $dataSospInizio;

    /**
     * @OA\Property(
     *      title="Data fine sospensione di pagamento",
     *      example="2000-01-01"
     * )
     *
     * @var Carbon
     */
    private $dataSospFine;

    protected $fillable = [
        'dataInizio',
        'dataFine',
        'dataSospInizio',
        'dataSospFine',
        'prezzo',
        'reverse_with'
    ];

    protected $hidden = [
        'bloccato',
        'updated_at',
        'created_at',
        'idAnagrafica',
        'idPeriodo',
        'idCausale',
        'idOperatore',
        'deleted_at'
    ];

    protected $casts = [
        'dataInizio'     => 'date',
        'dataFine'       => 'date',
        'dataSospInizio' => 'date',
        'dataSospFine'   => 'date',
    ];

    // protected $with = ['gps', 'mezzo'];

    public function getPrezzoInCentesimiAttribute(): int
    {
        return $this->attributes['prezzo'] * 100;
    }

    public function scopeAccessibili(Builder $builder): Builder
    {

        /** @var Utente */
        $user = Auth::user();
        if ($user->getRoleLevel() >= 5) {
            if (in_array(WebTraxMiddleware::class, Route::current()->gatherMiddleware())) {
                $builder->byFlotteUtente(Auth::user());
            } else {
                $builder->byAnagrafica(Auth::user()->idAnagrafica);
            }
        }
        return $builder;
    }

    public function scopeAttivi(Builder $query): Builder
    {
        return $query
            ->where('dataInizio', '<=', now())
            ->where(
                fn ($w) => $w
                    ->where('dataFine', '>=', now())
                    ->orWhereNull('dataFine')
            );
    }

    public function scopeFuturi(Builder $query): Builder {
        return $query->where('dataInizio', '>', now());
    }

    public function scopeScaduti(Builder $query): Builder {
        return $query->where('dataFine', '>=', now());
    }

    public function scopeSospesi(Builder $query): Builder {
        return $query
            ->where('dataSospInizio', '<=', now())
            ->where('dataSospFine', '>=', now());
    }

    public function scopeNonSospesi(Builder $query): Builder {
        return $query
            ->where('dataSospInizio', '>', now())
            ->orWhere('dataSospFine', '<', now());
    }

    public function scopeFatturabili(Builder $query, $date = null): Builder
    {
        $date = $date ?? new Carbon();
        return $query
            ->whereNull('dataSospInizio')->whereNull('dataSospFine')
            ->orWhere( fn ($q) => $q->where('dataSospInizio', '>=', $date)
            ->orWhere('dataSospFine', '<=', $date));
    }

    /**
     * @param Anagrafica|int $anagrafica
     */
    public function scopeByAnagrafica(Builder $builder, $anagrafica): Builder
    {
        return $builder->where('idAnagrafica', $anagrafica instanceof Anagrafica ? $anagrafica->id : $anagrafica);
    }

    /**
     * @param Utente|int $utente
     */
    public function scopeByFlotteUtente(Builder $builder, $utente): Builder
    {
        // /** @var int */
        // $utente = $utente instanceof Utente ? $utente->id : $utente;

        return $builder->whereIn('id', Flotta::byUtente($utente)->with('servizi')->get()->pluck('servizi.*.id')->flatten()->unique()->toArray());
    }

    /**
     * @param Tipologia|int $fatturazione
     */
    public function scopePeriodicitaFatturazione(Builder $builder, $fatturazione): Builder
    {
        /** @var int */
        $fatturazione = $fatturazione instanceof Tipologia ? $fatturazione->id : $fatturazione;
        return $builder->where('idPeriodo', $fatturazione);
    }

    /**
     * @param Tipologia|int $causale
     */
    public function scopeCausaleFatturazione(Builder $builder, $causale): Builder
    {
        /** @var int */
        $causale = $causale instanceof Tipologia ? $causale->id : $causale;
        return $builder->where('idCausale', $causale);
    }

    public function mezzo(): BelongsToMany
    {
        return $this->belongsToMany(Mezzo::class, 'TC_ServizioComponente', 'idServizio', 'idMezzo')
            ->using(ServizioComponente::class)
            // ->withPivot(['principale', 'parziale', 'prezzo', 'dataRestituzione'])
            ->as('servizio_componente')->wherePivotNotNull('idMezzo');
    }

    public function gps(): BelongsToMany
    {
        return $this->belongsToMany(Componente::class, 'TC_ServizioComponente', 'idServizio', 'idComponente')
            ->using(ServizioComponente::class)
            ->withPivot(['principale', 'parziale', 'prezzo', 'dataRestituzione'])
            ->as('servizio_componente')->wherePivotNotNull('idComponente');
    }

    public function radiocomandi(): BelongsToMany
    {
        return $this->belongsToMany(Componente::class, 'TC_ServizioComponente', 'idServizio', 'idRadiocomando')
            ->using(ServizioComponente::class)
            ->as('servizio_componente')->wherePivotNotNull('idRadiocomando');
    }

    public function tacho(): BelongsToMany
    {
        return $this->belongsToMany(Componente::class, 'TC_ServizioComponente', 'idServizio', 'idTacho')
            ->using(ServizioComponente::class)
            ->withPivot(['principale', 'parziale'])
            ->as('servizio_componente')->wherePivotNotNull('idTacho');
    }

    public function manutenzioni(): HasMany
    {
        return $this->hasMany(Manutenzione::class, 'idServizio');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Anagrafica::class, 'idAnagrafica');
    }

    public function flotte(): BelongsToMany
    {
        return $this->belongsToMany(Flotta::class, 'TC_FlottaServizio', 'idServizio', 'idFlotta');
    }

    public function applicativi(): BelongsToMany
    {
        return $this->belongsToMany(Tipologia::class, 'TC_ServizioApplicativo', 'idServizio', 'idTipologia')
            ->as('servizio_applicativo');
    }

    public function installatori(): BelongsToMany
    {
        return $this->belongsToMany(Anagrafica::class, 'TC_ServizioInstallatore', 'idServizio', 'idAnagrafica')
            ->as('servizio_installatore')->withPivot(['dataInstallazione']);
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Tipologia::class, 'idPeriodo', 'id');
    }

    public function causale(): BelongsTo
    {
        return $this->belongsTo(Tipologia::class, 'idCausale', 'id');
    }

    public function fatture(): MorphToMany
    {
        return $this->morphToMany(Fattura::class, 'billable', 'TC_FatturaPart', null, 'idFattura');
        // return $this->belongsToMany(Fattura::class, 'TC_FatturaPart', )
    }

    /**
     * @return string|false
     */
    public function get_unitcode()
    {
        return optional($this->gps()->orderBy('principale', 'DESC')->first())->unitcode ?? false;
    }

    /**
     * @return BelongsToMany
     */
    public function get_principale()
    {
        return $this->gps()->orderBy('principale', 'DESC');
    }
}
