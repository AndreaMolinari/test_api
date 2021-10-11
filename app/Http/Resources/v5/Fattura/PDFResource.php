<?php

namespace App\Http\Resources\v5\Fattura;

use App\Models\v5\Componente;
use App\Models\v5\DDT;
use App\Models\v5\Servizio;
use App\Models\v5\Sim;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

class PDFResource extends JsonResource {
    private function formatCurrency($price) {
        return number_format($price, 2, ',', '.');
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        // return parent::toArray($request);
        if (!(($cliente = $this->whenLoaded('cliente')) instanceof MissingValue)) {
            $cliente = [
                'id'         => $cliente->id,
                'nominativo' => $cliente->nominativo,
                'pIva'       => $cliente->pIva,
                'codFisc'    => $cliente->codFisc,
                'fatturazione' => $cliente->relationLoaded('fatturazione') ? [
                    'sdi'             => $cliente->fatturazione->sdi,
                    'splitPA'         => $cliente->fatturazione->splitPA,
                    'esenteIVA'       => $cliente->fatturazione->esenteIVA,
                    'speseIncasso'    => $cliente->fatturazione->speseIncasso,
                    'speseSpedizione' => $cliente->fatturazione->speseSpedizione,
                    'iban'            => $cliente->fatturazione->iban,
                    'pec'             => $cliente->fatturazione->pec,
                    'mail'            => $cliente->fatturazione->mail,
                    'modalita'        => $cliente->fatturazione->relationLoaded('modalita') ? $cliente->fatturazione->modalita : new MissingValue,
                    'periodo'         => $cliente->fatturazione->relationLoaded('periodo') ? $cliente->fatturazione->periodo : new MissingValue,
                ] : new MissingValue,
                'sede_legale' => $cliente->relationLoaded('sede_legale') ? $cliente->sede_legale : new MissingValue,
            ];
        }

        $servizi_per_fattura = function (Collection $servizi) {
            return $servizi
                ->groupBy(['idPeriodo', 'prezzoInCentesimi'])
                ->map(fn ($gruppiPrezzi, $idPeriodo) => $gruppiPrezzi
                    ->map(fn (Collection $aServizi) => $aServizi->chunk(50)->map(fn ($chunk) => [
                        'periodo' => $chunk->first()->periodo,
                        'prezzoUnitario' => $this->formatCurrency($chunk->first()->prezzo),
                        'prezzoImponibile' => $this->formatCurrency($chunk->first()->prezzo * $chunk->count()),
                        'identificativi' => $chunk
                            ->map(fn (Servizio $servizio) => count($servizio->mezzo) > 0
                                ? $servizio->mezzo->map(fn ($m) => $m->targa ?? $m->telaio)
                                : ($servizio->gps->sortByDesc('servizio_componente.principale')->first() ? [$servizio->gps->sortByDesc('servizio_componente.principale')->first()->unitcode] : []))
                                // !!! si spacca con i radiocomandi perche non ha ne tacho e gps
                                // !!! mancano i tacho
                            ->flatten()
                            ->map(fn ($str) => str_replace(' ', '', $str))
                    ])))->flatten(2);
        };
        $ddts_per_fattura = function (Collection $ddts) {
            return $ddts
                ->map(fn (DDT $ddt) => [
                    $ddt->componenti
                        ->map(fn (Componente $comp) => [
                            'riferimentoDDT' => "$ddt->anno/$ddt->numero",
                            'dataSpedizione' => $ddt->dataSpedizione,
                            'identificativo' => $comp->unitcode,
                            'modello'        => $comp->modello,
                            'prezzo'         => $this->formatCurrency(0),
                            'sconto'         => 0,
                            'iva'            => 22,
                        ])
                        ->merge(
                            $ddt->sims
                                ->map(fn (Sim $sim) => [
                                    'riferimentoDDT' => "$ddt->anno/$ddt->numero",
                                    'dataSpedizione' => $ddt->dataSpedizione,
                                    'identificativo' => $sim->serial,
                                    'modello'        => $sim->modello,
                                    'prezzo'         => $this->formatCurrency(0),
                                    'sconto'         => 0,
                                    'iva'            => 22,
                                ])
                        )
                        ->merge([[
                            'riferimentoDDT' => "$ddt->anno/$ddt->numero",
                            'identificativo' => "Spese di spedizione",
                            'dataSpedizione' => $ddt->dataSpedizione,
                            'modello'        => null,
                            'prezzo'         => $this->formatCurrency($ddt->costoSpedizione),
                            'sconto'         => 0,
                            'iva'            => 22
                        ]]),
                ])
                ->flatten(2);
        };

        // exit(json_encode($this->transform($this->whenLoaded('servizi'), $servizi_per_fattura)));

        return [
            'id'            => $this->id,
            'numero'        => $this->numero,
            'anno'          => $this->anno,
            'manuale'       => $this->manuale,
            'idAnagrafica'  => $this->idAnagrafica,
            'idNotaCredito' => $this->idNotaCredito,
            // 'idOperatore'   => $this->idOperatore,
            'scadenze'      => $this->transform($this->whenAppended('scadenze'), fn ($n) => $n->map(fn ($l) => $l->data->format('d/m/Y') . ': €' . $this->formatCurrency($l->importo))->toArray()),
            'created_at'    => $this->created_at,
            'imponibile'    => $this->transform($this->whenAppended('imponibile'), fn ($n) => $this->formatCurrency($n)),
            'imposta'       => $this->transform($this->whenAppended('imposta'), fn ($n) => $this->formatCurrency($n)),
            'speseIncasso'  => $this->transform($this->whenAppended('speseIncasso'), fn ($n) => $this->formatCurrency($n)),
            'totale'        => $this->transform($this->whenAppended('totale'), fn ($n) => $this->formatCurrency($n)),
            'cliente'       => $cliente,
            // 'voci'          => $this->whenLoaded('voci'),
            // 'servizi'       => $this->transform($this->whenLoaded('servizi'), fn ($servizi) => $servizi->map(fn ($s) => $s->dataInizio)),
            // 'servizi'       => $this->transform($this->whenLoaded('servizi'), fn ($servizi) => $servizi->map(fn ($s) => $s->dataInizio)),
            'ddts'     => $this->transform($this->whenLoaded('ddts'), $ddts_per_fattura),
            'addebiti' => $this->whenLoaded('addebiti'),
            'servizi'  => $this->transform($this->whenLoaded('servizi'), $servizi_per_fattura),
        ];
    }
}
