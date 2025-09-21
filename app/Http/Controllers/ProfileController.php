<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form. Add rendering to my docs
     */

    /*public function index(Request $request)
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $user = User::findOrFail($id);
            $problems = Problem::where('user_id', $id)->with('solutions')->get();
            $personaly_docs = $problems->where('personaly', true)->count();
            return Inertia::render('Profile/Edit', [
                'problems' => $problems,
                'user' => $user,
                'personaly_docs' => $personaly_docs,
            ]);
        }
    } */
    public function makepersonaly(Request $request, $idProblem, $idSolution): RedirectResponse
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $problem = Problem::where('id', $idProblem)->where('user_id', $id)->first();
            if($idSolution == null) {
                if ($problem) {
                    $problem->personaly = !$problem->personaly;
                    $problem->save();
                }
            }
            if ($problem) {
                $solution = $problem->solutions()->where('id', $idSolution)->where('user_id', $id)->first();
                if ($solution) {
                    $solution->personaly = !$solution->personaly;
                    $solution->save();
                }
            }
            return Redirect::route('profile.edit')->with('success', 'Проблема видна всем.');
        }
        return Redirect::route('profile.edit')->with('error', 'Не авторизованный пользователь.');
    }
    /**
     * Show the form for editing the user's profile.
     */

     public function edit(Request $request)
     {
         if (!Auth::check()) {
             return redirect()->route('login');
         }
     
         $id = Auth::id();
         $user = User::findOrFail($id);
     
         $problems = Problem::where('user_id', $id)
             ->with('solutions')
             ->get();
     
         $personaly_docs = $problems->where('personaly', true)->count();
     
         return Inertia::render('Profile/Edit', [
             'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
             'status'          => session('status'),
             'user'            => $user,
             'problems'        => $problems,
             'personaly_docs'  => $personaly_docs,
         ]);
     }
     

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
