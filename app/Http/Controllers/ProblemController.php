<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;

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
{  public function storeProb(Request $request)
    {
       
        $validated = $request->validate([
            'slug' => 'required|string|max:255',
            'title' => 'required|string',
            'description' => 'required|string',
            'metadata' => 'required|string',
        ]);

        Problem::create($validated);

        // редирект обратно с флеш-сообщением
        return redirect()->back()->with('success', 'Проблема и решение успешно добавлены!');
    }
       
    
}
