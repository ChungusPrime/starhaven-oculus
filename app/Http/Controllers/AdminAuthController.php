<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthController extends Controller {

    public function create(Request $request): Response|RedirectResponse {
        if ($request->session()->get('admin.authenticated') === true) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Admin/Login', [
            'username' => (string) $request->old('username', ''),
        ]);
    }

    public function store(Request $request): RedirectResponse {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $username = (string) config('oculus.admin.username');
        $password = (string) config('oculus.admin.password');

        if ($username === '' || $password === ''
            || ! hash_equals($username, $validated['username'])
            || ! hash_equals($password, $validated['password'])) {
            return back()
                ->withErrors(['username' => 'The administrator credentials are incorrect.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();
        $request->session()->put([
            'admin.authenticated' => true,
            'admin.username' => $username,
            'admin.signature' => $this->credentialSignature($username, $password),
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function dashboard(Request $request): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'username' => (string) $request->session()->get('admin.username'),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function credentialSignature(string $username, string $password): string
    {
        return hash_hmac('sha256', $username."\0".$password, (string) config('app.key'));
    }
}