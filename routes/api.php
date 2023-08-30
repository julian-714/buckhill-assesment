<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Api\V1\Admin\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'v1'], function (): void {
    /** Admin Routes */
    Route::group(['prefix' => 'admin'], function (): void {
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::group(['middleware' => ['admin']], function (): void {
            Route::get('/user-listing', [UserController::class, 'index']);
        });
    });
});
