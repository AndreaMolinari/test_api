<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Http\Requests\DDTRequest;
use App\Models\TT_DDTModel;
use Illuminate\Support\Facades\Auth;

class DDTController extends Controller {
    private const LOAD_RELATIONS = [
        'cliente:id,nome,cognome,ragSoc',
        'destinazione',
        'trasportatore:id,nome,cognome,ragSoc',
        'trasporto:id,tipologia,idParent',
        'causale:id,tipologia,idParent',
        'aspetto:id,tipologia,idParent',
        'componenti:id,unitcode,imei',
        'sims:id,serial',
        'note',
    ];

    public function index() {
        return TT_DDTModel::with(static::LOAD_RELATIONS)->get();
    }

    public function show(TT_DDTModel $ddt) {
        return $ddt->load(static::LOAD_RELATIONS);
    }

    public function store(DDTRequest $request) {
        $data = $request->validated();

        /** @var TT_DDTModel */
        $ddt = TT_DDTModel::make($data);
        $ddt->idOperatore = Auth::id() ?? 1;
        $ddt->anno = now()->year;
        // Prende l'ultimo numero o zero e lo incrementa di uno
        $ddt->numero = (TT_DDTModel::orderBy('numero', 'desc')->firstWhere('anno', $ddt->anno)->numero ?? 0) + 1;

        // Mandatory relations
        $ddt->cliente()->associate($data['cliente']['id']);
        $ddt->destinazione()->associate($data['destinazione']['id']);
        $ddt->trasportatore()->associate($data['trasportatore']['id']);
        $ddt->trasporto()->associate($data['trasporto']['id']);
        $ddt->causale()->associate($data['causale']['id']);
        $ddt->aspetto()->associate($data['aspetto']['id']);

        $ddt->save();

        $componenti = [];
        foreach ($data['componenti'] as $componente) {
            $componenti[$componente['id']] = ['prezzo' => $componente['ddt_componente']['prezzo'] ?? null];
        }
        $ddt->componenti()->sync($componenti);

        $sims = [];
        foreach ($data['sims'] as $sim) {
            $sims[$sim['id']] = ['prezzo' => $sim['ddt_componente']['prezzo'] ?? null];
        }
        $ddt->sims()->sync($sims);

        foreach ($data['note'] as $nota) {
            $ddt->note()->updateOrCreate(['id' => $nota['id'] ?? null], $nota);
        }

        $ddt->wasRecentlyCreated = true;

        return $ddt;
    }

    public function update(DDTRequest $request, TT_DDTModel $ddt) {
        $data = $request->validated();

        // Mandatory relations
        $ddt->cliente()->associate($data['cliente']['id']);
        $ddt->destinazione()->associate($data['destinazione']['id']);
        $ddt->trasportatore()->associate($data['trasportatore']['id']);
        $ddt->trasporto()->associate($data['trasporto']['id']);
        $ddt->causale()->associate($data['causale']['id']);
        $ddt->aspetto()->associate($data['aspetto']['id']);

        $componenti = [];
        foreach ($data['componenti'] as $componente) {
            $componenti[$componente['id']] = ['prezzo' => $componente['ddt_componente']['prezzo'] ?? null];
        }
        $ddt->componenti()->sync($componenti);

        $sims = [];
        foreach ($data['sims'] as $sim) {
            $sims[$sim['id']] = ['prezzo' => $sim['ddt_componente']['prezzo'] ?? null];
        }
        $ddt->sims()->sync($sims);

        foreach ($data['note'] as $nota) {
            $ddt->note()->updateOrCreate(['id' => $nota['id'] ?? null], $nota);
        }

        $ddt->update($data);

        return response()->noContent();
    }

    public function destroy(TT_DDTModel $ddt) {
        $ddt->delete();
        return response()->noContent();
    }
}
