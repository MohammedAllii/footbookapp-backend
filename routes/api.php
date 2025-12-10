<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\CampiController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\BookingController;




Route::get('/booked-hours', [BookingController::class, 'getBookedHours']);
Route::get('/today-next-reservation', [BookingController::class, 'todayNextReservation']);






// ---------------------
// API Pubbliche
// ---------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);



Route::get('/campi', [CampiController::class, 'index']);



Route::post('/create-payment-intent', [StripePaymentController::class, 'createPaymentIntent']);
Route::post('/stripe/webhook', [StripePaymentController::class, 'handleWebhook']);





// ---------------------
// API Protette (token required)
// ---------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user/update', [AuthController::class, 'updateProfile']);
});

