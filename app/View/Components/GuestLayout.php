<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Menyediakan komponen layout Blade untuk halaman pengunjung.
 * Konsep Laravel: Class-based Blade Component.
 */
class GuestLayout extends Component
{
    /**
     * Mengembalikan Blade view layout yang digunakan oleh komponen.
     * Konsep Laravel: Class-based Blade Component, render() dan View.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
