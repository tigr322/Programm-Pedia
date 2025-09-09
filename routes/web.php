<?php

use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Problem;
use App\Http\Controllers\SolutionController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MainController;

use App\Models\Solution;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
Route::get('/about', [MainController::class, 'about']);
Route::get('/search', [SearchController::class, 'index'])
    ->name('search');
Route::get('/dashboard', function () {
    $problems = Problem::with([
        'solutions' => function ($q) {
            $q->latest('id'); // по желанию: сортировка решений
        },
    ])
    ->latest('id')          // по желанию: сортировка проблем
    ->get();

    return Inertia::render('Dashboard', [
        'problems' => $problems,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/create/problem', [ProblemController::class, 'storeProb'])->name('problems.store');
    Route::post('/problems/{problem:slug}/solutions', [SolutionController::class, 'store'])
    ->name('solutions.store');


    Route::get('/solutions/{solution}/download', [SolutionController::class, 'download'])
    ->name('solutions.download');

    Route::post('/problems/{problem}/solutions', [SolutionController::class, 'store'])
    ->name('solutions.store');
    
});

require __DIR__.'/auth.php';
