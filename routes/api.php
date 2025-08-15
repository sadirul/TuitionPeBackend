<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login',    [AuthController::class, 'login'])->name('login');


Route::group(['middleware' => ['api', JwtMiddleware::class, ]], function ($router) {
    // LOGOUT
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // CLASSES ROUTE
    Route::post('/class/store', [ClassController::class, 'store'])->name('class.store');
    Route::get('/class/index', [ClassController::class, 'index'])->name('class.index');
    Route::get('/class/update/{uuid}', [ClassController::class, 'update'])->name('class.update');
});
