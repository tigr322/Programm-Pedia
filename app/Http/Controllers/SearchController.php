<?php

namespace App\Http\Controllers;


// app/Http/Controllers/SearchController.php
use Inertia\Inertia;
use App\Models\Problem;
use App\Models\Solution;

class SearchController
{
    public function index()
    {
        $q = request('q', '');

        $problems  = $q ? Problem::search($q)->take(10)->get() : collect();
        $solutions = $q ? Solution::search($q)->take(10)->get() : collect();

        return Inertia::render('Search', [
            'q'         => $q,
            'problems'  => $problems,
            'solutions' => $solutions,
        ]);
    }
}
