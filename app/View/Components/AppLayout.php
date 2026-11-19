<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Menyediakan komponen layout Blade untuk halaman aplikasi.
 * Konsep Laravel: Class-based Blade Component.
 */
class AppLayout extends Component
{
    /**
     * Mengembalikan Blade view layout yang digunakan oleh komponen.
     * Konsep Laravel: Class-based Blade Component, render() dan View.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
