<?php

// app/Http/Controllers/EditorUploadController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EditorUploadController extends Controller
{
    public function image(Request $request)
    {
        $request->validate([
            'image' => ['required','image','mimes:jpeg,png,gif,webp','max:4096'],
        ]);

        $path = $request->file('image')->store('solutions/images', 'public');
        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}
