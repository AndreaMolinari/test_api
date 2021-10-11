<?php

namespace App\Http\Resources\v5\Anagrafica;

use Illuminate\Http\Resources\Json\JsonResource;

class IndirizzoResource extends JsonResource
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
            'id'        => $this->id,
            'istat'     => $this->istat,
            'nazione'   => $this->nazione,
            'provincia' => $this->provincia,
            'comune'    => $this->comune,
            'cap'       => $this->cap,
            'via'       => $this->via,
            'civico'    => $this->civico,
        ];
    }
}
