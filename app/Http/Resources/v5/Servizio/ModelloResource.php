<?php

namespace App\Http\Resources\v5\Servizio;

use Illuminate\Http\Resources\Json\JsonResource;

class ModelloResource extends JsonResource
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
            'id'      => $this->id,
            'modello'    => $this->modello,
            'tipologia' => TipologiaResource::make($this->whenLoaded('tipologia')),
            'brand' => BrandResource::make($this->whenLoaded('brand')),
        ];
    }
}
