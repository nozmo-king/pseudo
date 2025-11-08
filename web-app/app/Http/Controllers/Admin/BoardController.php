<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index()
    {
        $boards = Board::withCount('threads')
            ->orderBy('position')
            ->get();

        return response()->json($boards);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);

        $board = Board::create([
            'slug' => \Illuminate\Support\Str::slug($request->input('name')),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'position' => $request->input('position', 0),
        ]);

        return response()->json($board, 201);
    }

    public function update(Request $request, Board $board)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'position' => 'sometimes|integer',
        ]);

        $board->update($request->only(['name', 'description', 'position']));

        return response()->json($board);
    }

    public function destroy(Board $board)
    {
        $board->delete();
        return response()->json(['success' => true]);
    }
}
