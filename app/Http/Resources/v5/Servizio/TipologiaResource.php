<?php

namespace App\Http\Resources\v5\Servizio;

use Illuminate\Http\Resources\Json\JsonResource;

class TipologiaResource extends JsonResource
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
            'nome'        => $this->tipologia,
            'descrizione' => $this->descrizione,
        ];
    }
}
