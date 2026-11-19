<?php

namespace App\Http\Resources;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Menentukan kolom booking yang dikirim ke klien API.
 * Konsep Laravel: API Resource (JsonResource), transformasi respons JSON.
 */
class BookingResource extends JsonResource
{
    /**
     * Memilih dan mengonversi atribut booking menjadi struktur respons JSON.
     * Konsep Laravel: API Resource, serialisasi melalui toArray().
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'room_id' => $this->room_id, 'user_id' => $this->user_id,
            'purpose' => $this->purpose, 'participant_count' => (int) $this->participant_count,
            'starts_at' => CarbonImmutable::parse($this->starts_at)->toIso8601String(),
            'ends_at' => CarbonImmutable::parse($this->ends_at)->toIso8601String(),
            'status' => $this->status, 'reviewed_by' => $this->reviewed_by,
            'rejection_reason' => $this->rejection_reason,
        ];
    }
}
