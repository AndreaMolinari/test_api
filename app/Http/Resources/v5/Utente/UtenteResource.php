<?php

namespace App\Http\Resources\v5\Utente;

use App\Http\Resources\v5\Anagrafica\{AnagraficaResource, TipologiaResource};
use Illuminate\Http\Resources\Json\JsonResource;

class UtenteResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id'            => $this->id,
            'email'         => $this->email,
            'username'      => $this->username,
            'actiaMail'     => $this->actiaMail,
            'actiaUser'     => $this->actiaUser,
            'actiaPassword' => $this->actiaPassword,
            'bloccato'      => $this->when(true, $this->bloccato),
            'tipologia'     => TipologiaResource::make($this->whenLoaded('tipologia')),
            'anagrafica'    => AnagraficaResource::make($this->whenLoaded('anagrafica')),
        ];
    }
}
