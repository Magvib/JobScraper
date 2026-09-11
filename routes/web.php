<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Socialite;

Route::view('/', 'welcome')->name('home');
Route::livewire('/dashboard', 'dashboard')->name('dashboard')->middleware('auth');
Route::livewire('/jobs', 'jobs')->name('jobs')->middleware('auth');
Route::livewire('/profile', 'profile')->name('profile')->middleware('auth');
Route::livewire('/cv', 'cv')->name('cv')->middleware('auth');

Route::get('/template/{name}', function ($name, Request $request) {
    $user = auth()->user();
    
    try {
        return view('templates.' . $name, ['user' => $user]);
    } catch (Throwable $th) {
        return view('templates.temp1', ['user' => $user]);
    }
})->middleware('auth')->name('template');

Route::get('/signed/template/{slug}', function ($slug, Request $request) {
    if (! $request->hasValidSignature()) {
        abort(401);
    }

    // Get user from praams user
    $user = User::find($request->query('user'));

    if (! $user) {
        abort(404);
    }
    
    try {
        return view('templates.' . $slug, ['user' => $user]);
    } catch (Throwable $th) {
        return view('templates.temp1', ['user' => $user]);
    }
})->name('signed-template');

Route::get('/login', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('home');
})->name('login');

Route::get('/auth/redirect', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Socialite::driver('github')->redirect();
});

Route::get('/auth/callback', function () {
    $githubUser = Socialite::driver('github')->user();

    if (! str_ends_with($githubUser->email, '@p13.dk')) {
        return redirect()->route('home')->with('error', 'You must use a p13.dk email to login.');
    }

    $user = User::firstOrCreate([
        'email' => $githubUser->email,
    ], [
        'name' => $githubUser->name,
        'avatar' => $githubUser->avatar,
        'email_verified_at' => now(),
        'password' => bcrypt(uniqid()),
    ]);

    auth()->login($user);

    return redirect()->route('dashboard');
});

Route::get('/logout', function () {
    auth()->logout();

    return redirect()->route('home');
})->name('logout');
