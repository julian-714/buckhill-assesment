<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\BrandController;
use App\Http\Controllers\Api\V1\Main\BlogPostController;
use App\Http\Controllers\Api\V1\User\UserAuthController;
use App\Http\Controllers\Api\V1\Main\PromotionController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\OrderStatusController;

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

    /** User Routes */
    Route::group(['prefix' => 'user'], function (): void {
        Route::post('/login', [UserAuthController::class, 'login']);
        Route::post('/register', [UserAuthController::class, 'register']);
        Route::get('/logout', [UserAuthController::class, 'logout']);
    });

    /** Main Page Routes */
    Route::group(['prefix' => 'main'], function (): void {
        Route::get('/promotions', [PromotionController::class, 'index']);
        Route::get('/blog', [BlogPostController::class, 'index']);
        Route::get('/blog/{uuid}', [BlogPostController::class, 'getBlog']);
    });

    /** Category List */
    Route::get('/categories', [CategoryController::class, 'index']);

    /** Brand List */
    Route::get('/brands', [BrandController::class, 'index']);

    /** Order Status List */
    Route::get('/order-statuses', [OrderStatusController::class, 'index']);
});
