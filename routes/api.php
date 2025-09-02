<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/**
 * @group Authentication
 *
 * APIs for registering and authenticating users
 */
Route::prefix('auth')->group(function () {
    /**
     * Register a new user
     *
     * @unauthenticated
     */
    Route::post('/register', [AuthController::class, 'register']);

    /**
     * Login a user
     *
     * @unauthenticated
     */
    Route::post('/login', [AuthController::class, 'login']);
});

/**
 * @group User Profile
 *
 * APIs for managing user profile
 *
 * @authenticated
 *
 * These endpoints require a valid Bearer token in the Authorization header.
 * You can obtain a token by registering or logging in using the authentication endpoints.
 */
Route::middleware('auth:sanctum')->group(function () {
    /**
     * Get authenticated user profile
     */
    Route::get('/user', [UserController::class, 'index']);
});
