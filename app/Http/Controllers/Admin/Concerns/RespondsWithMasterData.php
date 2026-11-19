<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Membagikan pola respons sukses yang sama ke controller master data.
 * Konsep Laravel: Trait PHP untuk HTTP Response, Redirect dan Session Flash.
 */
trait RespondsWithMasterData
{
    /**
     * Mengembalikan JSON atau redirect ke tab master yang sesuai sambil menyimpan pesan sukses di session.
     * Konsep Laravel: Trait PHP untuk HTTP Response, Redirect dan Session Flash.
     */
    protected function saved(Request $request, string $message, string $tab): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            $request->session()->flash('master_success', $message);

            return response()->json(['message' => $message]);
        }

        return redirect()->route('admin.rooms.index', ['tab' => $tab])->with('master_success', $message);
    }
}
