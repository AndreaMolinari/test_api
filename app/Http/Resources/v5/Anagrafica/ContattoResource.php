<?php

namespace App\Http\Resources\v5\Anagrafica;

use Illuminate\Http\Resources\Json\JsonResource;

class ContattoResource extends JsonResource
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
            'nome'        => $this->nome,
            'contatto'    => $this->contatto,
            'predefinito' => $this->predefinito,
            'rubrica'     => RubricaResource::make($this->whenLoaded('rubrica')),
        ];
    }
}
