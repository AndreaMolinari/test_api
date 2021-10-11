<?php

namespace App\Http\Resources\v5\Servizio;

use Illuminate\Http\Resources\Json\JsonResource;

class MezzoResource extends JsonResource
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
            'targa'   => $this->targa,
            'telaio'  => $this->telaio,
            'colore'  => $this->colore,
            'info'    => $this->info,
            'modello' => ModelloResource::make($this->whenLoaded('modello')),
        ];
    }
}
