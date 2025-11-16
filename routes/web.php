<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminAuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/register', function () {
    return view('doctor-register');
});

Route::get('/admin', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard.direct');

Route::get('/admin/login', function () {
    return view('admin.login');
})->name('admin.login');

Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');

Route::post('/admin/logout', function () {
    return redirect('/admin/login');
})->name('admin.logout');

Route::get('/admin/doctors-management', function () {
    return view('admin.doctors-management');
})->name('admin.doctors.management');

Route::get('/admin/subscriptions-management', function () {
    return view('admin.subscriptions-management');
})->name('admin.subscriptions.management');

Route::get('/admin/subscription-packages', function () {
    return view('admin.subscription-packages');
})->name('admin.packages.management');

// Test IAP page
Route::get('/test-iap', function () {
    return view('test-iap');
});

// Terms and conditions
Route::get('/terms', function () {
    return view('terms');
});