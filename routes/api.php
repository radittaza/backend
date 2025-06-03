<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

Route::get('/storage-files/{filePath}', function ($filePath) {
    if (!Storage::disk('public')->exists($filePath)) {
        abort(404);
    }

    $file = Storage::disk('public')->get($filePath);
    $type = Storage::disk('public')->mimeType($filePath);

    $response = Response::make($file, 200);
    $response->header('Content-Type', $type);

    return $response;
})->where('filePath', '.*');

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::get('vehicles', [\App\Http\Controllers\Api\VehicleController::class, 'index']);
Route::get('vehicles/search', [\App\Http\Controllers\Api\VehicleController::class, 'search']);
Route::get('vehicles/{vehicle}', [\App\Http\Controllers\Api\VehicleController::class, 'show']);

Route::get('banners', [\App\Http\Controllers\Api\BannerController::class, 'index']);
Route::get('banners/{banner}', [\App\Http\Controllers\Api\BannerController::class, 'show']);

Route::get('bank-transfers', [\App\Http\Controllers\Api\BankTransferController::class, 'index'])->name('bank-transfers.index');
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
        Route::put('user/update', [AuthController::class, 'updateUser']);
        Route::put('user/password', [AuthController::class, 'updatePassword']);
        Route::post('user/profile-picture', [AuthController::class, 'updateProfilePicture']);
    });

    Route::post('/', [\App\Http\Controllers\Api\BankTransferController::class, 'store'])->name('store');
    Route::get('/bank-transfer', [\App\Http\Controllers\Api\BankTransferController::class, 'index'])->name('index');
    Route::put('/{bank_transfer}', [\App\Http\Controllers\Api\BankTransferController::class, 'update'])->name('update');
    Route::delete('/{bank_transfer}', [\App\Http\Controllers\Api\BankTransferController::class, 'destroy'])->name('destroy');

    Route::post('banners', [\App\Http\Controllers\Api\BannerController::class, 'store']);
    Route::put('banners/{banner}', [\App\Http\Controllers\Api\BannerController::class, 'update']);
    Route::post('banners/{banner}/update', [\App\Http\Controllers\Api\BannerController::class, 'update']);
    Route::delete('banners/{banner}', [\App\Http\Controllers\Api\BannerController::class, 'destroy']);

    Route::get('bookings', [\App\Http\Controllers\Api\DashboardController::class, 'getAllBookings'])->name('bookings.index');
    Route::get('bookings/{booking}', [\App\Http\Controllers\Api\DashboardController::class, 'getBookingById'])->name('bookings.show');
    Route::put('bookings/{booking}/status', [\App\Http\Controllers\Api\DashboardController::class, 'updateBookingStatus'])->name('bookings.updateStatus');
    Route::get('bookings/user/{userId}', [\App\Http\Controllers\Api\DashboardController::class, 'getBookingsByUserId'])->name('bookings.byUser');
    Route::get('bookings/vehicle/{vehicleId}', [\App\Http\Controllers\Api\DashboardController::class, 'getBookingsByVehicleId'])->name('bookings.byVehicle');

    Route::prefix('book')
        ->name('book.')
        ->group(function () {
            Route::post('create', [\App\Http\Controllers\Api\BookingController::class, 'createBooking'])->name('create');
            Route::get('user/history', [\App\Http\Controllers\Api\BookingController::class, 'userBookingHistory'])->name('user.history');
            Route::get('user/history/{booking}', [\App\Http\Controllers\Api\BookingController::class, 'userBookingDetail'])->name('user.history.detail');
            Route::POST('user/payment/{booking}', [\App\Http\Controllers\Api\BookingController::class, 'uploadPaymentProof'])->name('user.payment.upload');
            Route::post('user/cancel/{booking}', [\App\Http\Controllers\Api\BookingController::class, 'userCancelBooking'])->name('user.cancel');
        });

    Route::apiResource('addresses', \App\Http\Controllers\Api\AddressController::class);
    Route::post('vehicles', [\App\Http\Controllers\Api\VehicleController::class, 'store'])->middleware('admin');
    Route::put('vehicles/{vehicle}', [\App\Http\Controllers\Api\VehicleController::class, 'update'])->middleware('admin');
    Route::delete('vehicles/{vehicle}', [\App\Http\Controllers\Api\VehicleController::class, 'destroy'])->middleware('admin');

    Route::apiResource('brands', \App\Http\Controllers\Api\BrandController::class)->middleware('admin');
});
