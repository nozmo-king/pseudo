<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200',
            'prompt' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $prompt = $request->input('prompt', '');

        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('admin-uploads', $filename, 'public');

        $promptPath = null;
        if ($prompt) {
            $promptFilename = time() . '_prompt.txt';
            $promptPath = storage_path('app/public/admin-uploads/' . $promptFilename);
            file_put_contents($promptPath, $prompt);
        }

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => asset('storage/' . $path),
            'prompt_saved' => !is_null($promptPath),
        ], 201);
    }

    public function index()
    {
        $dir = storage_path('app/public/admin-uploads');

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            return response()->json([]);
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        $fileList = [];

        foreach ($files as $file) {
            $filepath = $dir . '/' . $file;
            $fileList[] = [
                'name' => $file,
                'size' => filesize($filepath),
                'modified' => filemtime($filepath),
                'url' => asset('storage/admin-uploads/' . $file),
            ];
        }

        return response()->json($fileList);
    }
}
