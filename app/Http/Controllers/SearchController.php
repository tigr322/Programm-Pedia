<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\Solution;
use Inertia\Inertia;

class SearchController
{
    public function index()
    {
        $q = trim((string) request('q', ''));

        // если строка пустая — отдаём пустые результаты и НЕ вызываем load()
        if ($q === '') {
            return Inertia::render('Search', [
                'q'         => '',
                'problems'  => [],
                'solutions' => [],
            ]);
        }

        // при поиске Scout вернёт Eloquent-коллекции -> можно вызывать load()
        if(auth()->check()) {
            // авторизованный видит всё
            $problems  = Problem::search($q)->take(10)->get();
        } else {
            // гость видит только публичные (false или null)
            $problems  = Problem::search($q)
            ->where('personaly', false)
            ->orWhereNull('personaly')
                ->take(10)
                ->get();
        }
        $solutions = Solution::search($q)->take(10)->get();
        $solutions->load('problem:id,slug');

        return Inertia::render('Search', compact('q','problems','solutions'));
    }
}