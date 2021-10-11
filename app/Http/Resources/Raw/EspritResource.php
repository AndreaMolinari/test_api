<?php 
namespace App\Http\Resources\Raw;

use Illuminate\Http\Resources\Json\JsonResource;

class EspritResource extends JsonResource {
    public function toArray($request) {
        return [
            'id' => $this->id,
        ];
    }
}