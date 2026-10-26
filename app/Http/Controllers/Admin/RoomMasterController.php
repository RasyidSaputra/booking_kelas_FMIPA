<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoomMasterController extends Controller
{
    public function __invoke(Request $request): View
    {
        $input = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $search = trim($input['q'] ?? '');
        $rooms = DB::table('rooms')->when($search !== '', function ($query) use ($search): void {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%');
            });
        })->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.rooms.index', compact('rooms', 'search'));
    }
}
