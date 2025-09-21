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
    $problems = Problem::with(['solutions' => fn($q) => $q->latest('id')])
        ->when(null, fn($query) => $query->where('personaly', false))
        ->latest('id')
        ->get();

    return Inertia::render('Dashboard', [
        'problems' => $problems,
    ]);
})->name('dashboard');
Route::get('/solutions/{solution}/download', [SolutionController::class, 'download'])
->name('solutions.download');
Route::get('/problems/{problem}/{solution?}', [ProblemController::class, 'show'])
    ->whereNumber('solution')
    ->name('problems.show');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/create/problem', [ProblemController::class, 'storeProb'])->name('problems.store');
    Route::post('/problems/{problem:id}/solutions', [SolutionController::class, 'store'])
    ->name('solutions.store');
    Route::delete('/solutions/{solution}', [SolutionController::class, 'destroy'])
        ->name('solutions.destroy');
});

require __DIR__.'/auth.php';
