<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\StudentController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\RoleStatusExpiryMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verifyOtp');
Route::post('/resend-otp', [AuthController::class, 'resendOtp'])
    ->middleware(['throttle:3,1',]); // 3 attempts per minute
Route::post('/login',    [AuthController::class, 'login'])->name('login');

// PASSWORD RESET
Route::post('/password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset');

// GET PRICING PLANS
Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');


Route::group(['middleware' => ['api', JwtMiddleware::class,]], function ($router) {
    // LOGOUT
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware([RoleStatusExpiryMiddleware::class .  ':tuition'])->group(function () {
        // DASHBOARD DATA
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dasshboard');

        // MONTHLY DASHBOARD DATA
        Route::get('/dashboard/monthly-collection', [DashboardController::class, 'monthlyCollection'])->name('dasshboard.monthlyCollection');

        // GENERATE FEES
        Route::get('/generate-fees', [FeeController::class, 'generateFess'])->name('generateFess');
        Route::post('/add-fees/{student_id}', [FeeController::class, 'addFees'])->name('addFees');

        // CLASSES ROUTE
        Route::post('/class/store', [ClassController::class, 'store'])->name('class.store');
        Route::get('/class/index', [ClassController::class, 'index'])->name('class.index');
        Route::put('/class/edit/{uuid}', [ClassController::class, 'update'])->name('class.update');

        // CLASSES ROUTE
        Route::post('/student/store', [StudentController::class, 'store'])->name('student.store');
        Route::get('/student/index/{student_uuid?}', [StudentController::class, 'index'])->name('student.index');
        Route::put('/student/update/{student_id}', [StudentController::class, 'update'])->name('student.update');
        Route::put('/student/change/class', [StudentController::class, 'changeClass'])->name('student.change.class');
        Route::put('/student/change/status', [StudentController::class, 'changeStatus'])->name('student.change.status');

        // FEES PAID
        Route::put('/fee/update/{fee_uuid}', [FeeController::class, 'update'])->name('fee.update');

        // DELETE ACCOUNT
        Route::post('/account/delete', [ProfileController::class, 'deleteAccount'])->name('account.delete');
    });

    // PROFILE UPDATE
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // CHNAGE PASSWORD
    Route::post('/password/update', [ProfileController::class, 'changePassword'])->name('password.update');

    //RAZORPAY API
    Route::post('/payment/order', [PaymentController::class, 'createOrder'])->name('createOrder');
    Route::post('/payment/verify', [PaymentController::class, 'verifyPayment'])->name('verifyPayment');
});
