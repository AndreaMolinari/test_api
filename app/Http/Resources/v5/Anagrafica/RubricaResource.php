<?php

namespace App\Http\Resources\v5\Anagrafica;

use Illuminate\Http\Resources\Json\JsonResource;

class RubricaResource extends JsonResource
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
            'id'          => $this->id,
            'descrizione' => $this->descrizione,
            'nome'        => $this->nome,
            'contatti'    => ContattoResource::collection($this->whenLoaded('contatti')),
            'anagrafica'  => AnagraficaResource::make($this->whenLoaded('anagrafica')),
        ];
    }
}
