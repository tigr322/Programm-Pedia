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
            // убрали alpha_dash, принимаем любую строку, ниже сами slug-ифицируем
            'slug'         => ['nullable', 'string', 'max:255', 'unique:solutions,slug'],
            'summary'      => ['nullable', 'string'],
            'environment'  => ['nullable', 'array'],
            'root_cause'   => ['nullable', 'string'],
            'steps'        => ['nullable', 'string'],
            'verification' => ['nullable', 'string'],
            'pitfalls'     => ['nullable', 'string'],
            'links'        => ['nullable', 'array'],
            'markdown'     => ['nullable', 'string'],
            'score'        => ['nullable', 'integer'],
            'code'         => ['nullable', 'string'],
            'language'     => ['nullable', 'string', 'max:32'],
            'content'      => ['nullable', 'string'], // HTML из Quill
            'pdf'          => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // 10MB

            // теги придут как массив ID
            'tags'         => ['nullable', 'array'],
            'tags.*'       => ['integer', 'exists:tech_tags,id'],
        ]);

        // Требуем хотя бы что-то: markdown/код/контент или PDF
        if (
            empty($validated['markdown']) &&
            empty($validated['code']) &&
            empty($validated['content']) &&
            !$request->hasFile('pdf')
        ) {
            return back()->withErrors([
                'markdown' => 'Добавьте текст решения (markdown/код/контент) или прикрепите PDF.',
            ])->withInput();
        }

        // slug: если передали — нормализуем, иначе генерим
        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : (!empty($validated['title'])
                ? Str::slug($validated['title'])
                : Str::slug('solution-'.now()->timestamp));

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
            'content'      => $validated['content']      ?? null,  // HTML Quill
            'pdf_path'     => $pdfPath,
            'code'         => $validated['code']         ?? '',
            'language'     => $validated['language']     ?? 'plaintext',
            'markdown'     => $validated['markdown']     ?? '',
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
