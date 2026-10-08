<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'account.show');
        }

        return view('admin.login');
    }

    public function store(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'account.show');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Email atau kata sandi tidak sesuai.'])
                ->onlyInput('email');
        }

        if (! $request->user()?->isAdmin()) {
            Auth::logout();

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Akun ini tidak memiliki akses admin.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
