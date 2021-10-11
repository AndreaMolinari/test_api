<?php

namespace App\Http\Resources\v5\Servizio;

use Illuminate\Http\Resources\Json\JsonResource;

class InstallatoreResource extends JsonResource
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
            'id'                => $this->id,
            'ragSoc'            => $this->ragSoc ?? $this->nome.' '.$this->cognome,
            'pIva'              => $this->pIva ?? $this->codFisc,
            'dataInstallazione' => $this->servizio_installatore->dataInstallazione,
        ];
    }
}
