<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AiAdviceController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\SymptomController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/search', [DoctorController::class, 'search']);
Route::get('/doctors/{id}', [DoctorController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('symptoms', SymptomController::class);
    Route::apiResource('appointments', AppointmentController::class);

    Route::post('/ai/health-advice', [AiAdviceController::class, 'generateAdvice']);
    Route::get('/ai/health-advice/history', [AiAdviceController::class, 'index']);
});
