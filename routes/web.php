<?php

use App\Http\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Register'))->name('home');
Route::get('/register', fn () => Inertia::render('Register'))->name('register');

Route::get('/admin', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin', [AdminAuthController::class, 'store'])->middleware('throttle:5,1')->name('admin.authenticate');

Route::middleware('admin.session')->prefix('admin')->name('admin.')->group(function () {
	Route::get('/dashboard', [AdminAuthController::class, 'dashboard'])->name('dashboard');
	Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
});
