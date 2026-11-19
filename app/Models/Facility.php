<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Merepresentasikan tabel fasilitas dengan dukungan factory untuk data pengujian.
 * Konsep Laravel: Eloquent Model dan HasFactory.
 */
class Facility extends Model
{
    /** @use HasFactory<\Database\Factories\FacilityFactory> */
    use HasFactory;
}
