<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\BrandAdminController;
use App\Http\Controllers\Admin\VehicleAdminController;
use App\Http\Controllers\Admin\BookingAdminController;
use App\Http\Controllers\Admin\BannerAdminController;
use App\Http\Controllers\Admin\BankTransferAdminController;


Auth::routes();

Route::get('/', function () {
    return redirect('login');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Grup Rute untuk Admin Panel
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserAdminController::class);
    Route::resource('brands', BrandAdminController::class);
    Route::resource('vehicles', VehicleAdminController::class);
    Route::get('bookings', [BookingAdminController::class, 'index'])->name('bookings.index');
    Route::get('bookings/{booking}', [BookingAdminController::class, 'show'])->name('bookings.show');
    Route::put('bookings/{booking}/status', [BookingAdminController::class, 'updateStatus'])->name('bookings.updateStatus');

});
