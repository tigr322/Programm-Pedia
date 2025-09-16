<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Str;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Problem;
use Inertia\Inertia;

class ProblemController extends Controller
{
    public function show(int $problem, ?int $solution = null)
    {
        $query = Problem::with('solutions');

        $problemModel = $query->findOrFail($problem);

        if ($solution) {
            // если надо вернуть только одно решение
            $problemModel->setRelation(
                'solutions',
                $problemModel->solutions->where('id', $solution)->values()
            );
        }

        return Inertia::render('Problems/Show', [
            'problem'            => $problemModel,
            'selectedSolutionId' => $solution,
            'canEdit'            => auth()->check(),
        ]);
    }

    public function storeProb(Request $request)
    {
        // 1) обычная валидация
        $validated = $request->validate([
            'slug'        => 'required|string|max:255|unique:problems,slug',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            // если metadata приходит строкой из инпута:
            'metadata'    => 'nullable|string',
            // если хочешь принимать JSON из фронта как объект — используй:
            // 'metadata' => 'nullable|array',
        ]);

        // 2) аккуратно упакуем metadata в массив (который сохранится как JSON)
        // Если пришла строка — положим её в ключ "info".
        $metaInput = $request->input('metadata');
        $meta = null;

        if (is_null($metaInput) || $metaInput === '') {
            $meta = null;
        } elseif (is_array($metaInput)) {
            $meta = $metaInput; // уже объект/массив из фронта
        } else {
            // пробуем распарсить как JSON-строку; если не получилось — кладём как простую строку
            $decoded = json_decode($metaInput, true);
            $meta = json_last_error() === JSON_ERROR_NONE ? $decoded : ['info' => $metaInput];
        }

        // 3) создаём запись
        Problem::create([
            'slug'        => $validated['slug'],
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'metadata'    => $meta, // <- массив/null (Eloquent превратит в JSON)
        ]);

        return back()->with('success', 'Проблема успешно добавлена!');
    }

}
