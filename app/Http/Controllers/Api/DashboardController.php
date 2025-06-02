<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Admin: Get all bookings with filters and pagination.
     */
    public function getAllBookings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pagesize' => 'integer|min:1|max:100', // Batasi pagesize
            'user_id' => 'nullable|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'rental_status' => 'nullable|in:active,cancelled,pending,completed', // Tambah 'completed'
            'payment_proof' => 'nullable|in:paid,unpaid,pending,rejected', // Tambah 'rejected'
            'start_date_from' => 'nullable|date_format:Y-m-d',
            'start_date_to' => 'nullable|date_format:Y-m-d|after_or_equal:start_date_from',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = Booking::with([
            'user:id,username,email,full_name',
            'vehicle:id,vehicle_name,public_url_image',
            'deliveryAddress:id,full_name,full_address,phone',
            'bank:id,name_bank,number'
        ])->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
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
        if ($request->filled('start_date_from')) {
            $query->whereDate('start_date', '>=', Carbon::parse($request->start_date_from));
        }
        if ($request->filled('start_date_to')) {
            $query->whereDate('start_date', '<=', Carbon::parse($request->start_date_to));
        }

        $pageSize = $request->query('pagesize', 15);
        $bookings = $query->paginate($pageSize);

        // Transformasi data jika diperlukan untuk menyesuaikan output
        $transformedBookings = $bookings->getCollection()->transform(function ($booking) {
             return [
                'id' => $booking->id,
                'user' => $booking->user,
                'vehicle' => $booking->vehicle,
                'rental_period_days' => $booking->rental_period,
                'start_date' => $booking->start_date->toIso8601String(),
                'end_date' => $booking->end_date->toIso8601String(),
                'delivery_address' => $booking->deliveryAddress,
                'rental_status' => $booking->rental_status,
                'total_price' => $booking->total_price,
                'payment_status' => $booking->payment_proof,
                'payment_image_url' => $booking->public_url_image,
                'bank_details' => $booking->bank,
                'notes' => $booking->notes,
                'created_at' => $booking->created_at->toIso8601String(),
             ];
        });


        return response()->json([
            'statusCode' => 200,
            'message' => 'All bookings retrieved successfully.',
            'data' => $transformedBookings,
            'pagination' => [
                'page' => $bookings->currentPage(),
                'pagesize' => $bookings->perPage(),
                'totalItems' => $bookings->total(),
                'totalPages' => $bookings->lastPage(),
            ]
        ]);
    }

    /**
     * Admin: Get a specific booking by ID.
     */
    public function getBookingById(Booking $booking)
    {
        $booking->load([
            'user:id,username,email,full_name,phone',
            'vehicle.brand:id,brand_name',
            'deliveryAddress',
            'bank'
        ]);

        // Transformasi data booking agar lebih detail dan sesuai kebutuhan admin
        $transformedBooking = [
             'id' => $booking->id,
             'user_details' => $booking->user,
             'vehicle_details' => $booking->vehicle, // Termasuk brand
             'rental_period_days' => $booking->rental_period,
             'start_date' => $booking->start_date->toIso8601String(),
             'end_date' => $booking->end_date->toIso8601String(),
             'delivery_address_details' => $booking->deliveryAddress,
             'rental_status' => $booking->rental_status,
             'total_price' => $booking->total_price,
             'payment_image_secure_url' => $booking->secure_url_image,
             'payment_image_public_url' => $booking->public_url_image,
             'payment_status' => $booking->payment_proof,
             'bank_details' => $booking->bank,
             'notes' => $booking->notes,
             'created_at' => $booking->created_at->toIso8601String(),
             'updated_at' => $booking->updated_at->toIso8601String(),
        ];


        return response()->json([
            'statusCode' => 200,
            'message' => 'Booking retrieved successfully.',
            'data' => $transformedBooking
        ]);
    }

    /**
     * Admin: Update booking status (rental_status and payment_proof).
     */
    public function updateBookingStatus(Request $request, Booking $booking)
    {
        $validator = Validator::make($request->all(), [
            'rental_status' => 'sometimes|in:active,cancelled,pending,completed', // 'completed' ditambahkan
            'payment_proof' => 'sometimes|in:paid,unpaid,pending,rejected', // 'rejected' ditambahkan
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updated = false;
        $originalRentalStatus = $booking->rental_status;

        if ($request->has('rental_status') && $booking->rental_status !== $request->rental_status) {
            $booking->rental_status = $request->rental_status;
            $updated = true;

            // Update vehicle availability status based on new booking rental_status
            if ($booking->vehicle) {
                if ($request->rental_status === 'active') {
                    $booking->vehicle->availability_status = 'rented';
                    $booking->vehicle->save();
                } elseif (in_array($request->rental_status, ['completed', 'cancelled']) && $originalRentalStatus === 'active') {
                    // Cek apakah ada booking aktif lain untuk kendaraan ini sebelum mengubah jadi available
                    $otherActiveBookings = Booking::where('vehicle_id', $booking->vehicle_id)
                                                ->where('id', '!=', $booking->id)
                                                ->where('rental_status', 'active')
                                                ->exists();
                    if (!$otherActiveBookings) {
                        $booking->vehicle->availability_status = 'available';
                        $booking->vehicle->save();
                    }
                }
                 // Jika dari pending ke cancelled, dan vehicle belum 'rented' oleh booking lain, pastikan available.
                elseif ($request->rental_status === 'cancelled' && $originalRentalStatus === 'pending') {
                    if ($booking->vehicle->availability_status !== 'rented') { // Hanya jika tidak sedang dirental oleh booking lain
                        $booking->vehicle->availability_status = 'available';
                        $booking->vehicle->save();
                    }
                }
            }
        }

        if ($request->has('payment_proof') && $booking->payment_proof !== $request->payment_proof) {
            $booking->payment_proof = $request->payment_proof;
            $updated = true;

            // Jika pembayaran 'paid', dan status rental 'pending', ubah jadi 'active'
            if ($request->payment_proof === 'paid' && $booking->rental_status === 'pending') {
                $booking->rental_status = 'active';
                if ($booking->vehicle) {
                    $booking->vehicle->availability_status = 'rented';
                    $booking->vehicle->save();
                }
            }
        }

        if ($updated) {
            $booking->save();

        }

        return response()->json([
            'statusCode' => 200,
            'message' => 'Booking status updated successfully.',
            'data' => $booking->fresh()->load(['user:id,username', 'vehicle:id,vehicle_name', 'deliveryAddress', 'bank'])
        ]);
    }


    public function getBookingsByUserId(Request $request, $userId)
    {
        if (!User::find($userId)) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        $request->merge(['user_id' => $userId]);
        return $this->getAllBookings($request);
    }


    public function getBookingsByVehicleId(Request $request, $vehicleId)
    {
        if (!Vehicle::find($vehicleId)) {
            return response()->json(['message' => 'Vehicle not found.'], 404);
        }
        $request->merge(['vehicle_id' => $vehicleId]);
        return $this->getAllBookings($request);
    }
}
