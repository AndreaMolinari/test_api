<?php

namespace App\Http\Resources\v5\Servizio;

use Illuminate\Http\Resources\Json\JsonResource;

class GpsResource extends JsonResource
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
            'id'       => $this->id,
            'unitcode' => $this->unitcode,
            'imei'     => $this->imei,
            'sim'      => $this->whenLoaded('sim'),
            'modello'  => ModelloResource::make($this->whenLoaded('modello')),
        ];
    }
}
