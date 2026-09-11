<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $username = (string) config('oculus.admin.username');
        $password = (string) config('oculus.admin.password');
        $expectedSignature = hash_hmac('sha256', $username."\0".$password, (string) config('app.key'));
        $sessionSignature = (string) $request->session()->get('admin.signature', '');

        if ($username === '' || $password === ''
            || $request->session()->get('admin.authenticated') !== true
            || ! hash_equals($expectedSignature, $sessionSignature)) {
            $request->session()->forget('admin');

            return redirect()->guest(route('admin.login'));
        }

        return $next($request);
    }
}