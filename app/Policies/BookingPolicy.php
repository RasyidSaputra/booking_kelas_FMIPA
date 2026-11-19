<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Menyiapkan policy Booking; seluruh keputusan saat ini masih menolak akses.
 * Konsep Laravel: Authorization Policy; kerangka belum diimplementasikan.
 */
class BookingPolicy
{
    /**
     * Menyiapkan keputusan izin melihat daftar data; implementasi saat ini selalu mengembalikan false.
     * Konsep Laravel: Authorization Policy, ability viewAny.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Menyiapkan keputusan izin melihat detail data; implementasi saat ini selalu mengembalikan false.
     * Konsep Laravel: Authorization Policy, ability view.
     */
    public function view(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Menyiapkan keputusan izin membuat data; implementasi saat ini selalu mengembalikan false.
     * Konsep Laravel: Authorization Policy, ability create.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Menyiapkan keputusan izin mengubah data; implementasi saat ini selalu mengembalikan false.
     * Konsep Laravel: Authorization Policy, ability update.
     */
    public function update(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Menyiapkan keputusan izin menghapus data; implementasi saat ini selalu mengembalikan false.
     * Konsep Laravel: Authorization Policy, ability delete.
     */
    public function delete(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Menyiapkan keputusan izin memulihkan data; implementasi saat ini selalu mengembalikan false.
     * Konsep Laravel: Authorization Policy, ability restore.
     */
    public function restore(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Menyiapkan keputusan izin menghapus permanen data; implementasi saat ini selalu mengembalikan false.
     * Konsep Laravel: Authorization Policy, ability forceDelete.
     */
    public function forceDelete(User $user, Booking $booking): bool
    {
        return false;
    }
}
