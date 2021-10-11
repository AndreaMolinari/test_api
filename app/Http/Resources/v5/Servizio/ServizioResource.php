<?php

namespace App\Http\Resources\v5\Servizio;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

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
        $installatori = $this->whenLoaded('installatori');
        return [
            'id'           => $this->id,
            'dataInizio'   => $this->dataInizio,
            'dataFine'     => $this->dataFine,
            'prezzo'       => $this->prezzo,
            'periodo'      => TipologiaResource::make($this->whenLoaded('periodo')),
            'causale'      => TipologiaResource::make($this->whenLoaded('causale')),
            'cliente'      => ClienteResource::make($this->whenLoaded('cliente')),
            'applicativi'  => ApplicativoResource::collection($this->whenLoaded('applicativi')),
            'gps'          => GpsResource::make($this->whenLoaded('gps')->first()),
            'tacho'        => TachoResource::make($this->whenLoaded('tacho')->first()),
            'mezzo'        => MezzoResource::make($this->whenLoaded('mezzo')->first()),
            'installatore' => InstallatoreResource::make($installatori instanceof MissingValue ? $installatori : $installatori->first()),
            'radiocomandi' => RadiocomandoResource::collection($this->whenLoaded('radiocomandi')),
        ];
    }
}
