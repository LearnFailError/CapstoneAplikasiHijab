<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->signedInDestination();
        }

        return view('auth.login');
    }

    public function registerForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->signedInDestination();
        }

        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return $this->signedInDestination();
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau kata sandi tidak sesuai.'])->onlyInput('email');
        }

        if ($request->user()->isAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Akun admin masuk melalui halaman login admin.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('account.show'));
    }

    public function register(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return $this->signedInDestination();
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_admin' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.show')->with('status', 'Akun pembeli berhasil dibuat.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Anda berhasil keluar.');
    }

    private function signedInDestination(): RedirectResponse
    {
        return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'account.show');
    }
}