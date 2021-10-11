<?php

namespace App\Http\Resources\Raw\Analyzer;

use Illuminate\Http\Resources\Json\JsonResource;

class ServizioResource extends JsonResource
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
            'id'           => $this->id,
            'idAnagrafica' => $this->idAnagrafica,
            'dataInizio'   => $this->dataInizio,
            'dataFine'     => $this->dataFine,
            'bloccato'     => $this->bloccato,
            'gps'          => GpsResource::collection($this->whenLoaded('gps')),
            'mezzo'        => MezzoResource::make($this->whenLoaded('mezzo')->first()),
        ];
    }
}
