<?php 
namespace App\Http\Resources\Raw;

use Illuminate\Http\Resources\Json\JsonResource;

class ReverseWithResource extends JsonResource {
    public function toArray($request) {
        return $this;
    }
}