<?php

namespace App\Http\Controllers\v5;

use App\Http\Controllers\Controller;
use App\Http\Resources\v5\Fattura\PDFResource;
use App\Models\v5\Anagrafica;
use App\Models\v5\DDT;
use App\Models\v5\Fattura;
use App\Models\v5\Servizio;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade as PDF;

class FatturaController extends Controller {
    const WHAT_TO_LOAD = ['cliente', 'servizi', 'ddts.componenti', 'ddts.sims', 'addebiti'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Anagrafica $anagrafica) {
        return $anagrafica->fatture()->with(static::WHAT_TO_LOAD)->withCount('voci')->paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Anagrafica $anagrafica) {
        $valid = $request->validate([
            'addebiti.*.id' => [
                'integer',
                Rule::exists('TT_Addebito')
                    ->where('idAnagrafica', $anagrafica->id)
                    ->whereIn('id', $anagrafica->addebiti()->fatturabili()->pluck('id')->toArray())
            ],
            'fattura.id' => [
                'integer',
                Rule::exists('TT_Fattura')
                    ->whereIn('id', Fattura::fatture()->pluck('id')->toArray())
            ]
        ]);
        $ddts = $anagrafica->ddts()->fatturabili()->get();
        $addebiti = $anagrafica->addebiti()->whereIn('id', collect($valid['addebiti'] ?? [])->pluck('id')->toArray())->get();
        if ($ddts->isNotEmpty() || $addebiti->isNotEmpty()) {
            $fattura = new Fattura();
            $fattura->cliente()->associate($anagrafica);
            $fattura->manuale = true;
            $fattura->save();
            $fattura->ddts()->saveMany($ddts, $ddts->map(fn ($s) => [
                'idOperatore' => 1,
                'prezzoUnitario' => $s->costoTotale ?? 0,
            ])->toArray());
            $fattura->addebiti()->saveMany($addebiti, $addebiti->map(fn ($a) => [
                'descrizione' => $a->descrizione,
                'idOperatore' => 1,
                'prezzoUnitario' => $a->prezzoUnitario ?? 0,
            ])->toArray());
            return $fattura;
        }
        return response()->json(['message' => 'Non ci sono addebiti o ddt da fatturare per l\'anagrafica selezionata'], 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Fattura  $fattura
     * @return \Illuminate\Http\Response
     */
    public function show(Anagrafica $anagrafica, Fattura $fattura) {
        return $fattura->load(static::WHAT_TO_LOAD);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Fattura  $fattura
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Anagrafica $anagrafica, Fattura $fattura) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Fattura  $fattura
     * @return \Illuminate\Http\Response
     */
    public function destroy(Anagrafica $anagrafica, Fattura $fattura) {
        //
    }

    /**
     * Generate all the new.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generate(Anagrafica $anagrafica = null) {
        set_time_limit(0);

        $anag = Anagrafica::query();

        if ($anagrafica ?? false) {
            $anag->whereKey($anagrafica->id);
        }
        return $anag
            ->where(
                fn ($w) => $w
                    ->whereHas('fatturazione')
                    ->orWhereHas('servizi', fn (Builder $b) => $b->attivi()->fatturabili())
                    ->orWhereHas('ddts', fn (Builder $b) => $b->fatturabili())
            )
            ->get()
            ->map(function (Anagrafica $anagrafica) {
                $fattura = new Fattura();
                $fattura->cliente()->associate($anagrafica);
                /** @var Collection */
                $servizi = $anagrafica->servizi()->get()
                    ->filter(function (Servizio $servizio) {
                        return static::checkFatturabile($servizio);
                    })
                    ->values();

                $ddts = $anagrafica->ddts()->get();

                $addebiti = $anagrafica->addebiti()->get();

                if ($servizi->isNotEmpty() || $ddts->isNotEmpty() || $addebiti->isNotEmpty()) {
                    $fattura->save();
                    $fattura->servizi()->saveMany($servizi, $servizi->map(fn ($s) => [
                        'idOperatore' => 1,
                        'prezzoUnitario' => $s->prezzo ?? 0,
                    ])->toArray());
                    $fattura->ddts()->saveMany($ddts, $ddts->map(fn ($s) => [
                        'idOperatore' => 1,
                        'prezzoUnitario' => $s->costoTotale ?? 0,
                    ])->toArray());
                    $fattura->addebiti()->saveMany($addebiti, $addebiti->map(fn ($a) => [
                        'descrizione' => $a->descrizione,
                        'idOperatore' => 1,
                        'prezzoUnitario' => $a->prezzoUnitario ?? 0,
                    ])->toArray());
                    return $fattura;
                }
                return null;
            })
            ->flatten()
            ->filter(fn ($f) => !!$f)
            ->values();
    }

    public function generate_sicuro() {
        foreach (Anagrafica::has('fatturazione')->has('servizi')->get() as $anagrafica) {
            $fattura = new Fattura();
            $fattura->idAnagrafica = $anagrafica->id;

            $servizi_da_fatturare = [];
            $servizi_da_fatturare_op = [];
            $isFatturabile = false;

            // ? fatturo i servizi
            foreach ($anagrafica->servizi()->attivi()->fatturabili()->get() as $servizio) {
                if (static::checkFatturabile($servizio)) {
                    $isFatturabile = true;
                    $servizi_da_fatturare[] = $servizio;
                    $servizi_da_fatturare_op[] = [
                        'idOperatore' => 1,
                        'prezzoUnitario' => $servizio->prezzo ?? 0,
                        'iva' => 22
                    ];
                }
            }

            if ($isFatturabile) {
                $fattura->save();
                $fattura->servizi()->saveMany($servizi_da_fatturare, $servizi_da_fatturare_op);
            }
            // ? fatturo i ddt
            // ? fatturo i extra
        }

        return Fattura::with(self::WHAT_TO_LOAD)->get();
    }

    private static function checkFatturabile(Servizio $servizio, Carbon $data_target = null): bool {
        try {
            $monthsDifference = $servizio->periodo()->first()->data;
            $data_target      = new Carbon($data_target);
            $data_inizio      = (clone $data_target)->startOfMonth()->subMonthsNoOverflow($monthsDifference['months'] - 1); // faccio -1 perchè sono gayo
            $data_fine        = (clone $data_target)->endOfMonth();

            $lastFattura = $servizio->fatture()
                ->where('manuale', false)
                // where sospensione false
                ->whereBetween('TT_Fattura.created_at', [$data_inizio, $data_fine])
                ->orderBy('id', 'DESC')
                ->count();
            return $lastFattura === 0 ? true : false;
        } catch (\Throwable $th) {
            return false;
            dd($th->getMessage(), $servizio);
        }
    }

    public function proiezione(Request $request, Anagrafica $anagrafica = null) {
        $validate = $request->validate([
            'data_from' => ['required_with:data_to', 'date'],
            'data_to'   => ['date'],
        ]);

        $data_from = new Carbon($validate['data_from'] ?? null);
        $data_to   = new Carbon($validate['data_to'] ?? (clone $data_from)->addMonth(1));

        /** @var Fattura|null */
        $lastFattura = null;
        $anag = Anagrafica::query();

        if ($anagrafica ?? false) {
            $anag->whereKey($anagrafica->id);
        }
        return $anag
            ->where(
                fn ($w) => $w
                    ->whereHas('fatturazione')
                    ->orWhereHas('servizi', fn (Builder $b) => $b->attivi()->fatturabili())
                    ->orWhereHas('ddts', fn (Builder $b) => $b->fatturabili())
            )
            ->has('fatturazione')
            ->has('servizi')
            ->get()
            ->map(function (Anagrafica $anagrafica) use ($data_from, $data_to, $lastFattura) {
                $fatture = [];
                for ($data_current = clone $data_from; $data_current <= $data_to; $data_current->addMonthsNoOverflow(1)) {
                    $fattura = new Fattura();
                    $fattura->anno = $data_current->year;
                    $fattura->numero = ($lastFattura->numero ?? 0) + 1;
                    $fattura->idAnagrafica = $anagrafica->id;

                    $servizi = $anagrafica
                        ->servizi()
                        // ->attivi() // !! Controllo se il servizio è attivo al momento che sto proiettando
                        ->fatturabili($data_current)
                        // ->with('periodo')
                        ->get()
                        ->filter(function (Servizio $servizio) use ($data_current) {

                            $monthsDifference = $servizio->periodo()->first()->data['months'];

                            $dataInizioFatturazione = (clone $servizio->dataInizio);

                            $dataSospInizio = $servizio->dataSospInizio ? (clone $servizio->dataSospInizio)->startOfMonth() : null;
                            $dataSospFine   = $servizio->dataSospFine ? (clone $servizio->dataSospFine)->endOfMonth() : null;

                            if ($dataSospInizio && $dataSospFine && $dataSospFine <= $data_current) {
                                $dataInizioFatturazione->addMonths((new Carbon($dataSospFine))->diffInMonths($dataSospInizio));
                            }

                            while ($dataInizioFatturazione->addMonth($monthsDifference) <= $data_current);

                            return $dataInizioFatturazione->startOfMonth() == $data_current->startOfMonth();
                        })
                        ->values();

                    $fattura->setRelation('servizi', $servizi);
                    $fattura->setRelation('voci', $servizi->map(fn ($s) => [
                        'idOperatore' => 1,
                        'prezzoUnitario' => $s->prezzo ?? 0
                    ]));

                    $lastFattura = $fattura;
                    $fatture[] = $fattura;
                }
                return $fatture;
            })
            ->flatten();
    }

    public function pdf(Request $request, Fattura $fattura) {;
        // ? Solo perchè piace a Ciro merda
        // $fatturaccia = Fattura::with([
        //     'voci.billable' => fn (MorphTo $billable) => $billable->morphWith([
        //         Servizio::class => [
        //             'periodo',
        //             'mezzo',
        //             'gps',
        //             'tacho',
        //         ],
        //         DDT::class => [
        //             'componenti.modello.tipologia',
        //             'componenti.modello.brand',
        //             'sims.modello.brand',
        //         ]
        //     ]),
        //     'cliente.fatturazione',
        //     'cliente.sede_legale',
        // ])->whereHas('addebiti')->first();
        $load_for_pdf = [ 'cliente.fatturazione', 'cliente.sede_legale', 'servizi.periodo', 'servizi.mezzo', 'servizi.gps', 'servizi.tacho', 'ddts.componenti', 'ddts.sims', 'addebiti'];
        
        return PDF::loadView('pdf.fattura', ['fattura' => PDFResource::make($fattura->load($load_for_pdf))->toArray($request)])->download('invoice.pdf');

        $fatturaccia = Fattura::with([
            'cliente.fatturazione',
            'cliente.sede_legale',
            'servizi.periodo',
            'servizi.mezzo',
            'servizi.gps',
            'servizi.tacho',
            'ddts.componenti',
            'ddts.sims',
            'addebiti'
        ])->has('ddts', '>', 1)->first();
        return $fatturaccia;
        // return $fatturaccia;
        return PDF::loadView('pdf.fattura', ['fattura' => PDFResource::make($fatturaccia)->toArray($request)])->download('invoice.pdf');
    }
}
