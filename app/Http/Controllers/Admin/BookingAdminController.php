<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User; // Untuk filter
use App\Models\Vehicle; // Untuk filter
use Illuminate\Http\Request;
use Carbon\Carbon; // Untuk filter tanggal

class BookingAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with([
            'user:id,username,email',
            'vehicle:id,vehicle_name',
            // 'deliveryAddress:id,full_name', // Mungkin terlalu banyak di list
        ])->orderBy('created_at', 'desc');

        // Filtering
        if ($request->filled('search_user')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search_user . '%')->orWhere('email', 'like', '%' . $request->search_user . '%');
            });
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('rental_status')) {
            $query->where('rental_status', $request->rental_status);
        }
        if ($request->filled('payment_proof')) {
            $query->where('payment_proof', $request->payment_proof);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', Carbon::parse($request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start_date', '<=', Carbon::parse($request->date_to));
        }

        $bookings = $query->paginate(15)->appends($request->query());

        $rentalStatuses = ['pending', 'active', 'completed', 'cancelled'];
        $paymentStatuses = ['unpaid', 'pending', 'paid', 'rejected'];
        $vehicles = Vehicle::select('id', 'vehicle_name')->orderBy('vehicle_name')->get();

        return view('admin.bookings.index', compact('bookings', 'rentalStatuses', 'paymentStatuses', 'vehicles', 'request'));
    }
    public function show(Booking $booking)
    {
        $booking->load([
            'user:id,username,email,full_name,phone',
            'vehicle.brand:id,brand_name',
            'deliveryAddress',
            'bank',
        ]);

        $rentalStatuses = ['pending', 'active', 'completed', 'cancelled'];
        $paymentStatuses = ['unpaid', 'pending', 'paid', 'rejected'];

        return view('admin.bookings.show', compact('booking', 'rentalStatuses', 'paymentStatuses'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $validatedData = $request->validate([
            'rental_status' => 'required|in:pending,active,completed,cancelled',
            'payment_proof' => 'required|in:unpaid,pending,paid,rejected',
        ]);

        $originalRentalStatus = $booking->rental_status;
        $originalPaymentStatus = $booking->payment_proof;

        $booking->rental_status = $validatedData['rental_status'];
        $booking->payment_proof = $validatedData['payment_proof'];

        if ($booking->isDirty('rental_status') || $booking->isDirty('payment_proof')) {
            if ($booking->vehicle) {
                if ($booking->rental_status === 'active' && $booking->payment_proof === 'paid') {
                    $booking->vehicle->availability_status = 'rented';
                } elseif (in_array($booking->rental_status, ['completed', 'cancelled'])) {
                    $otherActiveBookings = Booking::where('vehicle_id', $booking->vehicle_id)->where('id', '!=', $booking->id)->where('rental_status', 'active')->exists();
                    if (!$otherActiveBookings) {
                        $booking->vehicle->availability_status = 'available';
                    }
                }
                $booking->vehicle->save();
            }
        }
        $booking->save();


        return redirect()->route('admin.bookings.show', $booking->id)->with('success', 'Booking status updated successfully.');
    }
}
