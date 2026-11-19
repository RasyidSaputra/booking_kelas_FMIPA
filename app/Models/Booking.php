<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Merepresentasikan tabel booking dengan dukungan factory; logika peminjaman ada di service.
 * Konsep Laravel: Eloquent Model dan HasFactory.
 */
class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;
}
