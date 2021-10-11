<?php

namespace App\Http\Resources\v5\Servizio;

use Illuminate\Http\Resources\Json\JsonResource;

class TachoResource extends JsonResource
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
            'modello'  => ModelloResource::make($this->whenLoaded('modello')),
            'sim'      => $this->whenLoaded('sim'),
        ];
    }
}
