<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Menentukan kolom room yang dikirim ke klien API.
 * Konsep Laravel: API Resource (JsonResource), transformasi respons JSON.
 */
class RoomResource extends JsonResource
{
    /**
     * Memilih dan mengonversi atribut room menjadi struktur respons JSON.
     * Konsep Laravel: API Resource, serialisasi melalui toArray().
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'code' => $this->code, 'name' => $this->name,
            'capacity' => (int) $this->capacity, 'location' => $this->location,
            'description' => $this->description, 'is_active' => (bool) $this->is_active,
        ];
    }
}
