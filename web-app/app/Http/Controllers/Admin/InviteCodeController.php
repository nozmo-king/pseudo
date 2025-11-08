<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InviteCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InviteCodeController extends Controller
{
    public function index()
    {
        $codes = InviteCode::with(['creator', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($codes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'count' => 'nullable|integer|min:1|max:100',
        ]);

        $count = $request->input('count', 1);
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = InviteCode::create([
                'code' => Str::random(16),
                'created_by_user_id' => auth()->id(),
            ]);
        }

        return response()->json($codes, 201);
    }

    public function destroy(InviteCode $inviteCode)
    {
        $inviteCode->delete();
        return response()->json(['success' => true]);
    }
}
