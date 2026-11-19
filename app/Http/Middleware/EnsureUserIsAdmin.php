<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menyiapkan middleware admin, tetapi saat ini hanya meneruskan request tanpa memeriksa role.
 * Konsep Laravel: HTTP Middleware; pembatasan admin aktif menggunakan Gate pada route.
 */
class EnsureUserIsAdmin
{
    /**
     * Meneruskan request ke middleware berikutnya; belum memeriksa apakah pengguna adalah admin.
     * Konsep Laravel: HTTP Middleware, request pipeline dan Closure $next.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
