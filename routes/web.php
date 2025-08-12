<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('doctor-register');
})->name('register');

Route::get('/admin', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/admin/doctors-management', function () {
    return view('admin.doctors-management');
})->name('admin.doctors-management');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

// Test webhook endpoint
Route::get('/test-webhook', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Webhook endpoint is accessible',
        'timestamp' => now()->toISOString()
    ]);
});