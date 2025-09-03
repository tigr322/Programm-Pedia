<?php
// app/Http/Controllers/SolutionController.php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\Solution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SolutionController extends Controller
{
    public function store(Request $request, Problem $problem)
    {
        $validated = $request->validate([
            'content' => ['nullable', 'string'],
            'pdf'     => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // 10MB
        ]);

        if (empty($validated['content']) && !$request->hasFile('pdf')) {
            return back()->withErrors([
                'content' => 'Добавьте текст решения или прикрепите PDF.',
            ]);
        }

        $pdfPath = null;
        if ($request->hasFile('pdf')) {
            // Сохранение в storage/app/public/solutions
            $pdfPath = $request->file('pdf')->store('solutions', 'public');
        }

        $problem->solutions()->create([
            'content'  => $validated['content'] ?? null,
            'pdf_path' => $pdfPath,
        ]);

        return back()->with('success', 'Решение добавлено.');
    }

    public function download(Solution $solution)
{
    abort_unless(
        $solution->pdf_path && Storage::disk('public')->exists($solution->pdf_path),
        Response::HTTP_NOT_FOUND
    );

    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
    $disk = Storage::disk('public'); // <— хинт для Intelephense

    return $disk->download($solution->pdf_path, basename($solution->pdf_path));
}
}
