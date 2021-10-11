<?php

namespace App\Http\Resources\v5\Anagrafica;

use App\Http\Resources\v5\Utente\UtenteResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AnagraficaResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        // return parent::toArray($request);
        return [
            'id'              => $this->id,
            'nome'            => $this->nome,
            'cognome'         => $this->cognome,
            'ragSoc'          => $this->ragSoc,
            'dataNascita'     => $this->dataNascita,
            'codFisc'         => $this->codFisc,
            'pIva'            => $this->pIva,
            'referenteLegale' => $this->referenteLegale,
            'bloccato'        => $this->when(true, $this->bloccato),                              // Si nasconde se non è soddisfatto
            'tipologie'       => TipologiaResource::collection($this->whenLoaded('tipologie')),
            'fatturazione'    => FatturazioneResource::make($this->whenLoaded('fatturazione')),
            'genere'          => TipologiaResource::make($this->whenLoaded('genere')),
            'utenti'          => UtenteResource::collection($this->whenLoaded('utenti')),
            'parent'          => AnagraficaResource::make($this->whenLoaded('parent')),
            'rubriche'        => RubricaResource::collection($this->whenLoaded('rubriche')),
            'indirizzi'       => IndirizzoResource::collection($this->whenLoaded('indirizzi')),
            'servizi'         => $this->whenLoaded('servizi'),
            'servizi_count'   => $this->servizi_count,
        ];
    }
}
