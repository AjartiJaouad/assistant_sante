<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::apiResource('symptoms', App\Http\Controllers\SymptomController::class);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});
// Gestion des Médecins
    Route::get('/doctors', [App\Http\Controllers\DoctorController::class, 'index']);
    Route::get('/doctors/search', [App\Http\Controllers\DoctorController::class, 'search']);
    Route::get('/doctors/{id}', [App\Http\Controllers\DoctorController::class, 'show']);
Route::apiResource('appointments', App\Http\Controllers\AppointmentController::class)->except(['show', 'update']);
// Intelligence Artificielle
    Route::post('/ai-advice', [App\Http\Controllers\AiAdviceController::class, 'generateAdvice']);
    Route::get('/ai-advice', [App\Http\Controllers\AiAdviceController::class, 'index']);
