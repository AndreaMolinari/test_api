<?php

namespace App\Http\Resources\v5\Servizio;

use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
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
            'nome'        => $this->ragSoc ?? $this->nome.' '.$this->cognome,
            'parent'      => ClienteResource::make($this->whenLoaded('parent')),
        ];
    }
}
