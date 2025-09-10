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
    $solutionId = $request->input('solution_id');

    // Базовые правила
    $base = [
        'title'        => ['nullable', 'string', 'max:255'],
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
        'content'      => ['nullable', 'string'],
        'pdf'          => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        'tags'         => ['nullable', 'array'],
        'tags.*'       => ['integer', 'exists:tech_tags,id'],
    ];

    if ($solutionId) {
        // === UPDATE ===
        $solution = $problem->solutions()->whereKey($solutionId)->firstOrFail();

        $rules = $base + [
            'slug' => ['nullable', 'string', 'max:255', 'unique:solutions,slug,' . $solution->id],
        ];

        $validated = $request->validate($rules);

        // Слаг: если передали новое имя → пересчитать; если нет — оставить старый
        $slug = $solution->slug;
        if (!empty($validated['slug'])) {
            $slug = Str::slug($validated['slug']);
        } elseif (!empty($validated['title'])) {
            $slug = Str::slug($validated['title']);
        }

        // PDF: если пришёл новый — заменить, старый удалить
        if ($request->hasFile('pdf')) {
            if ($solution->pdf_path) {
                Storage::disk('public')->delete($solution->pdf_path);
            }
            $solution->pdf_path = $request->file('pdf')->store('solutions', 'public');
        }

        // Обновить поля (только то, что пришло)
        $solution->fill([
            'slug'         => $slug,
            'title'        => $validated['title']        ?? $solution->title,
            'summary'      => $validated['summary']      ?? $solution->summary,
            'environment'  => $validated['environment']  ?? $solution->environment,
            'root_cause'   => $validated['root_cause']   ?? $solution->root_cause,
            'steps'        => $validated['steps']        ?? $solution->steps,
            'verification' => $validated['verification'] ?? $solution->verification,
            'pitfalls'     => $validated['pitfalls']     ?? $solution->pitfalls,
            'links'        => $validated['links']        ?? $solution->links,
            'score'        => $validated['score']        ?? $solution->score,
            'content'      => array_key_exists('content', $validated)  ? $validated['content']  : $solution->content,
            'code'         => array_key_exists('code', $validated)     ? $validated['code']     : $solution->code,
            'language'     => array_key_exists('language', $validated) ? $validated['language'] : $solution->language,
            'markdown'     => array_key_exists('markdown', $validated) ? $validated['markdown'] : $solution->markdown,
        ]);

        $solution->save();

        if (!empty($validated['tags'])) {
            $solution->tags()->sync($validated['tags']);
        }

        return back()->with('success', 'Решение обновлено.');
    }

    // === CREATE ===
    $rules = $base + [
        'slug' => ['nullable', 'string', 'max:255', 'unique:solutions,slug'],
    ];

    $validated = $request->validate($rules);

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

    $slug = !empty($validated['slug'])
        ? Str::slug($validated['slug'])
        : (!empty($validated['title'])
            ? Str::slug($validated['title'])
            : Str::slug('solution-'.now()->timestamp));

    $pdfPath = null;
    if ($request->hasFile('pdf')) {
        $pdfPath = $request->file('pdf')->store('solutions', 'public');
    }

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
        'code'         => $validated['code']         ?? '',
        'language'     => $validated['language']     ?? 'plaintext',
        'markdown'     => $validated['markdown']     ?? '',
    ]);

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
