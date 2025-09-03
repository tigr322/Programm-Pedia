<?php

use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Problem;
use App\Http\Controllers\SolutionController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
        $problem = Problem::all();

        return Inertia::render('Dashboard', [
            'problems' =>$problem,
        ]);
    
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/create/problem', [ProblemController::class, 'storeProb'])->name('problems.store');
    Route::post('/problems/{problem}/solutions', [SolutionController::class, 'store'])
    ->name('solutions.store');

    Route::get('/solutions/{solution}/download', [SolutionController::class, 'download'])
    ->name('solutions.download');
});

require __DIR__.'/auth.php';
