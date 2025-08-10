<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Api\CommonController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CategoryListController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\InAppPurchaseController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\EventDetailController;
use App\Http\Controllers\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\SubscriptionPackageController;
use Illuminate\Support\Facades\Route;

// Event Details (public)
Route::get('/events/{eventId}/details', [EventDetailController::class, 'getEventDetails']);

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

// Category Lists API
Route::prefix('lists')->group(function () {
    Route::get('/categories', [CategoryListController::class, 'index']);
    Route::get('/categories/tree', [CategoryListController::class, 'tree']);
});

// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Contact form
Route::post('/contact', [ContactController::class, 'sendMessage']);

// Webhooks (no auth required)
Route::post('/webhooks/apple', [WebhookController::class, 'appleWebhook']);
Route::post('/webhooks/google', [WebhookController::class, 'googleWebhook']);

// Admin routes (no auth for now)
Route::prefix('admin')->group(function () {
    // Doctor management
    Route::get('/doctors', [AdminDoctorController::class, 'index']);
    Route::get('/doctors/{id}', [AdminDoctorController::class, 'show']);
    Route::put('/doctors/{id}/status', [AdminDoctorController::class, 'updateStatus']);
    Route::delete('/doctors/{id}', [AdminDoctorController::class, 'delete']);
    
    // Subscription management
    Route::get('/subscriptions', [AdminSubscriptionController::class, 'index']);
    Route::get('/subscriptions/{id}', [AdminSubscriptionController::class, 'show']);
    Route::put('/subscriptions/{id}/status', [AdminSubscriptionController::class, 'updateStatus']);
    Route::put('/subscriptions/{id}/extend', [AdminSubscriptionController::class, 'extend']);
    Route::get('/subscription-stats', [AdminSubscriptionController::class, 'stats']);
    
    // Package management
    Route::get('/packages', [SubscriptionPackageController::class, 'index']);
    Route::post('/packages', [SubscriptionPackageController::class, 'store']);
    Route::get('/packages/{id}', [SubscriptionPackageController::class, 'show']);
    Route::put('/packages/{id}', [SubscriptionPackageController::class, 'update']);
    Route::delete('/packages/{id}', [SubscriptionPackageController::class, 'destroy']);
});

// Protected routes
Route::middleware(['simple.auth'])->group(function () {
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
    
    // In-App Purchases
    Route::get('/subscription-plans', [InAppPurchaseController::class, 'getPlans']);
    Route::post('/verify-ios-purchase', [InAppPurchaseController::class, 'verifyIosPurchase']);
    Route::post('/verify-android-purchase', [InAppPurchaseController::class, 'verifyAndroidPurchase']);
    Route::get('/my-subscriptions', [InAppPurchaseController::class, 'getMySubscriptions']);
    Route::get('/subscription-status', [InAppPurchaseController::class, 'checkSubscriptionStatus']);
    Route::post('/cancel-subscription', [InAppPurchaseController::class, 'cancelSubscription']);
    Route::post('/restore-subscription', [InAppPurchaseController::class, 'restoreSubscription']);
});