<?php

namespace App\Http\Resources\v5\Anagrafica;

use Illuminate\Http\Resources\Json\JsonResource;

class FatturazioneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'sdi'             => $this->sdi,
            'splitPA'         => $this->splitPA,
            'esenteIVA'       => $this->esenteIVA,
            'speseIncasso'    => $this->speseIncasso,
            'speseSpedizione' => $this->speseSpedizione,
            'banca'           => $this->banca,
            'filiale'         => $this->filiale,
            'iban'            => $this->iban,
            'iban_abi'        => $this->iban_abi,
            'iban_cab'        => $this->iban_cab,
            'iban_cin'        => $this->iban_cin,
            'pec'             => $this->pec,
            'mail'            => $this->mail,
            'bloccato'        => $this->bloccato,
            'modalita'        => TipologiaResource::make($this->whenLoaded('modalita')),
            'periodo'         => TipologiaResource::make($this->whenLoaded('periodo')),
        ];
    }
}
