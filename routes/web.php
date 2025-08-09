<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/admin', function () {
    return view('admin.login');
});

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
});

Route::get('/admin/doctors', function () {
    return view('admin.doctors');
});

Route::get('/admin/doctors-management', function () {
    return view('admin.doctors-management');
});

Route::get('/admin/subscriptions-management', function () {
    return view('admin.subscriptions-management');
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