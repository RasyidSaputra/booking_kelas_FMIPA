<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Menentukan kolom user yang dikirim ke klien API.
 * Konsep Laravel: API Resource (JsonResource), transformasi respons JSON.
 */
class UserResource extends JsonResource
{
    /**
     * Memilih dan mengonversi atribut user menjadi struktur respons JSON.
     * Konsep Laravel: API Resource, serialisasi melalui toArray().
     */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'identity_number' => $this->identity_number, 'email' => $this->email, 'role' => $this->role, 'avatar_url' => $this->avatarUrl()];
    }
}
