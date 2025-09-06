<?php
// app/Http/Controllers/SolutionController.php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\Solution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SolutionController extends Controller
{
    
    public function store(Request $request, Problem $problem)
    {
        $validated = $request->validate([
            'title'        => ['nullable', 'string', 'max:255'],
            'slug'         => ['nullable', 'string', 'max:255', 'alpha_dash', 'unique:solutions,slug'],
            'summary'      => ['nullable', 'string'],
            'environment'  => ['nullable', 'array'],
            'root_cause'   => ['nullable', 'string'],
            'steps'        => ['nullable', 'string'],
            'verification' => ['nullable', 'string'],
            'pitfalls'     => ['nullable', 'string'],
            'links'        => ['nullable', 'array'],
            'score'        => ['nullable', 'integer'],

            'content'      => ['nullable', 'string'],
            'pdf'          => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // 10MB

            // теги придут как массив ID
            'tags'         => ['nullable', 'array'],
            'tags.*'       => ['integer', 'exists:tech_tags,id'],
        ]);

        // Требуем либо content, либо pdf
        if (empty($validated['content']) && !$request->hasFile('pdf')) {
            return back()->withErrors([
                'content' => 'Добавьте текст решения или прикрепите PDF.',
            ])->withInput();
        }

        // slug: если не прислали, пробуем из title; если нет title — из времени
        $slug = $validated['slug']
            ?? ($validated['title'] ?? null ? Str::slug($validated['title']) : Str::slug('solution-'.now()->timestamp));

        // сохранить PDF (storage/app/public/solutions)
        $pdfPath = null;
        if ($request->hasFile('pdf')) {
            $pdfPath = $request->file('pdf')->store('solutions', 'public');
        }

        // создаём Solution через связь у проблемы
        $solution = $problem->solutions()->create([
            'slug'         => $slug,
            'title'        => $validated['title']        ?? null,
            'summary'      => $validated['summary']      ?? null,
            'environment'  => $validated['environment']  ?? null,
            'root_cause'   => $validated['root_cause']   ?? null,
            'steps'        => $validated['steps']        ?? null,
            'verification' => $validated['verification'] ?? null,
            'pitfalls'     => $validated['pitfalls']     ?? null,
            'links'        => $validated['links']        ?? null,
            'score'        => $validated['score']        ?? null,
            'content'      => $validated['content']      ?? null,
            'pdf_path'     => $pdfPath,
        ]);

        // прикрепляем теги, если пришли
        if (!empty($validated['tags'])) {
            $solution->tags()->sync($validated['tags']);
        }

        return back()->with('success', 'Решение добавлено.');
    }

    public function download(Solution $solution)
    {
        abort_unless(
            $solution->pdf_path && Storage::disk('public')->exists($solution->pdf_path),
            Response::HTTP_NOT_FOUND
        );

        $disk = Storage::disk('public');
        // отдадим файл под «красивым» именем (если нужно — можно хранить оригинал)
        $downloadName = basename($solution->pdf_path);

        return $disk->download($solution->pdf_path, $downloadName);
    }
}
