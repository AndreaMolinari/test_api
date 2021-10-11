<?php

namespace App\Http\Controllers\v5;

use App\Http\Controllers\Controller;
use App\Http\Requests\v5\DDTRequest;
use App\Models\v5\Anagrafica;
use App\Models\v5\DDT;
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

    public function index(Anagrafica $cliente) {
        return DDT::with(static::LOAD_RELATIONS)->get();
    }

    public function show(Anagrafica $cliente, DDT $ddt) {
        return $ddt->load(static::LOAD_RELATIONS);
    }

    public function store(DDTRequest $request, Anagrafica $cliente) {
        $data = $request->validated();

        /** @var DDT */
        $ddt = DDT::make($data);

        // Mandatory relations
        $ddt->cliente()->associate($cliente);
        $ddt->destinazione()->associate($data['destinazione']['id']);
        $ddt->trasportatore()->associate($data['trasportatore']['id']);
        $ddt->trasporto()->associate($data['trasporto']['id']);
        $ddt->causale()->associate($data['causale']['id']);
        $ddt->aspetto()->associate($data['aspetto']['id']);

        $ddt->save();

        $componenti = [];
        foreach ($data['componenti'] ?? [] as $componente) {
            $componenti[$componente['id']] = ['prezzo' => $componente['ddt_componente']['prezzo'] ?? null];
        }
        $ddt->componenti()->sync($componenti);

        $sims = [];
        foreach ($data['sims'] ?? [] as $sim) {
            $sims[$sim['id']] = ['prezzo' => $sim['ddt_componente']['prezzo'] ?? null];
        }
        $ddt->sims()->sync($sims);

        foreach ($data['note'] ?? [] as $nota) {
            $ddt->note()->updateOrCreate(['id' => $nota['id'] ?? null], $nota);
        }

        $ddt->wasRecentlyCreated = true;

        return $ddt;
    }

    public function update(DDTRequest $request, Anagrafica $cliente, DDT $ddt) {
        $data = $request->validated();

        // Mandatory relations
        // $ddt->cliente()->associate($cliente);
        $ddt->destinazione()->associate($data['destinazione']['id']);
        $ddt->trasportatore()->associate($data['trasportatore']['id']);
        $ddt->trasporto()->associate($data['trasporto']['id']);
        $ddt->causale()->associate($data['causale']['id']);
        $ddt->aspetto()->associate($data['aspetto']['id']);

        $componenti = [];
        foreach ($data['componenti'] ?? [] as $componente) {
            $componenti[$componente['id']] = ['prezzo' => $componente['ddt_componente']['prezzo'] ?? null];
        }
        $ddt->componenti()->sync($componenti);

        $sims = [];
        foreach ($data['sims'] ?? [] as $sim) {
            $sims[$sim['id']] = ['prezzo' => $sim['ddt_componente']['prezzo'] ?? null];
        }
        $ddt->sims()->sync($sims);

        foreach ($data['note'] ?? [] as $nota) {
            $ddt->note()->updateOrCreate(['id' => $nota['id'] ?? null], $nota);
        }

        $ddt->update($data);

        return response()->noContent();
    }

    public function destroy(Anagrafica $cliente, DDT $ddt) {
        $ddt->delete();
        return response()->noContent();
    }
}
