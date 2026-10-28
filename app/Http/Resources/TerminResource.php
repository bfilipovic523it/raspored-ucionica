<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TerminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ucionica_id' => $this->ucionica_id,
            'naziv' => $this->naziv,
            'opis' => $this->opis,
            'vreme_pocetka' => substr($this->vreme_pocetka, 0, 5),
            'vreme_kraja' => substr($this->vreme_kraja, 0, 5),
            'status' => $this->status,
        ];
    }
}
