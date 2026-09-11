<?php

use App\Http\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'auth.register')->name('home');
Route::view('/register', 'auth.register')->name('register');

Route::get('/admin', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin', [AdminAuthController::class, 'store'])->middleware('throttle:5,1')->name('admin.authenticate');

Route::middleware('admin.session')->prefix('admin')->name('admin.')->group(function () {
	Route::view('/dashboard', 'admin.home')->name('dashboard');
	Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
});
