<?php

namespace App\Http\Resources\Raw\Analyzer;

use Illuminate\Http\Resources\Json\JsonResource;

class SimResource extends JsonResource
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
            'id'     => $this->id,
            'serial'  => $this->serial,
        ];
    }
}
