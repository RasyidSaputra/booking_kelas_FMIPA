<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RespondsWithMasterData
{
    protected function saved(Request $request, string $message, string $tab): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            $request->session()->flash('master_success', $message);

            return response()->json(['message' => $message]);
        }

        return redirect()->route('admin.rooms.index', ['tab' => $tab])->with('master_success', $message);
    }
}
