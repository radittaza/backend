<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Contoh jika mau menampilkan statistik
use App\Models\Booking;
use App\Models\Vehicle;


class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalVehicles = Vehicle::count();
        $pendingBookings = Booking::where('rental_status', 'pending')->count();
        $activeBookings = Booking::where('rental_status', 'active')->count();

        return view('admin.dashboard', compact('totalUsers', 'totalVehicles', 'pendingBookings', 'activeBookings'));
    }
}
