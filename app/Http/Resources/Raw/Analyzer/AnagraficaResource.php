<?php

namespace App\Http\Resources\Raw\Analyzer;

use Illuminate\Http\Resources\Json\JsonResource;

class AnagraficaResource extends JsonResource
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
            'id' => $this->id,
            'nome' => $this->nome,
            'cognome' => $this->cognome,
            'dataNascita' => $this->dataNascita,
            'codFisc' => $this->codFisc,
            'pIva' => $this->pIva,
            'ragSoc' => $this->ragSoc,
            'idGenere' => $this->idGenere,
            'utenti' => UtenteResource::collection($this->whenLoaded('utenti'))
        ];
    }
}
