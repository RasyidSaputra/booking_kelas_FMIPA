<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Merepresentasikan ruangan dan relasinya dengan fasilitas serta booking.
 * Konsep Laravel: Eloquent Model dan Eloquent Relationships.
 */
class Room extends Model
{
    /** @use HasFactory<\Database\Factories\RoomFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * Mendefinisikan relasi banyak-ke-banyak ruangan dengan fasilitas melalui tabel pivot.
     * Konsep Laravel: Eloquent Relationship, belongsToMany() dan Pivot Table.
     */
    public function facilities()
    {
        return $this->belongsToMany(Facility::class);
    }

    /**
     * Mendefinisikan relasi satu-ke-banyak ruangan dengan data peminjaman.
     * Konsep Laravel: Eloquent Relationship, hasMany().
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
