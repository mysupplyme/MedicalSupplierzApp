<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Api\CommonController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

// Public/Common routes (no authentication required)
Route::prefix('common')->group(function () {
    Route::get('/specialties', [CommonController::class, 'getSpecialties']);
    Route::get('/sub-specialties', [CommonController::class, 'getSubSpecialties']);
    Route::get('/get_residencies', [CommonController::class, 'getResidencies']);
    Route::get('/get_nationalities', [CommonController::class, 'getNationalities']);
    Route::get('/get_country_codes', [CommonController::class, 'getCountryCodes']);
    Route::get('/get_categories', [CategoryController::class, 'getCategories']);
    Route::get('/get_specialties/{categoryId?}', [CategoryController::class, 'getSpecialties']);
    Route::get('/get_sub_specialties', [CategoryController::class, 'getSubSpecialties']);
    Route::get('/conferences', [CategoryController::class, 'getConferences']);
    Route::get('/category-products/{categoryId}', [CategoryController::class, 'getCategoryProducts']);
    Route::get('/product-suppliers/{productId}', [CategoryController::class, 'getProductSuppliers']);
    Route::get('/clients', [CategoryController::class, 'getClients']);
    Route::get('/doctors', [CategoryController::class, 'getDoctors']);
    Route::get('/subscription_packages', [SubscriptionController::class, 'getSubscriptionPackages']);
});

// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// Protected routes
Route::group(function () {
    // User management
    Route::post('/signout', [AuthController::class, 'logout']);
    Route::get('/get_profile', [AuthController::class, 'getProfile']);
    Route::put('/update_profile', [AuthController::class, 'updateProfile']);
    Route::post('/change_password', [AuthController::class, 'changePassword']);
    
    // Events
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);
    Route::post('/events/{event}/register', [EventController::class, 'register']);
    
    // Subscriptions
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
});