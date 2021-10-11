<?php

namespace App\Http\Resources\v5\Anagrafica;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

class TipologiaResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        // return parent::toArray($request);
        return [
            'id'   => $this->id,
            'nome' => $this->whenAppended('nome'),
            'tipologia' => $this->whenAppended('nome', new MissingValue(), $this->tipologia)
        ];
    }
}
