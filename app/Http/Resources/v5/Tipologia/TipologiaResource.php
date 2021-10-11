<?php

namespace App\Http\Resources\v5\Tipologia;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

use function PHPUnit\Framework\isNull;

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
            'nome'        => $this->whenAppended('nome'),
            'tipologia'   => $this->whenAppended('nome', new MissingValue(), $this->tipologia),
            'idParent'    => $this->when($this->idParent, $this->idParent, new MissingValue()),
            'descendants' => TipologiaResource::collection($this->whenLoaded('descendants')),
            'ancestors'   => TipologiaResource::make($this->whenLoaded('ancestors')),
        ];
    }
}
