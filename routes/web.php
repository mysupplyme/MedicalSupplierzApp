<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/admin', function () {
    return view('admin.dashboard');
});

Route::get('/admin/doctors', function () {
    return view('admin.doctors');
});

Route::get('/terms', function () {
    return view('terms');
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/doctor-register', function () {
    return view('doctor-register');
});

Route::get('/reset-password', function () {
    return view('reset-password');
});