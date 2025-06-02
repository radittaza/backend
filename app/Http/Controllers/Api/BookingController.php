<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Address;
use App\Models\BankTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * User creates a new booking.
     */
    public function createBooking(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'delivery_location' => [
                'required',
                'exists:addresses,id',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Address::where('id', $value)->where('user_id', $user->id)->exists()) {
                        $fail('The selected delivery location is invalid or does not belong to you.');
                    }
                },
            ],
            'bank_transfer' => 'required|exists:bank_transfers,id',
            'start_date' => 'required',
            'end_date' => 'required',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $vehicle = Vehicle::find($data['vehicle_id']);

        $bank = BankTransfer::find($data['bank_transfer']);

        try {
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);

            if ($endDate->lt($startDate)) {
                return response()->json(['message' => 'End date cannot be before start date.'], 422);
            }

            $signedDifference = $endDate->diffInDays($startDate, false);
            $rentalPeriod = abs($signedDifference);

            if ($rentalPeriod == 0) {
                $rentalPeriod = 1;
            }

            $existingBooking = Booking::where('vehicle_id', $vehicle->id)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->where(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<', $endDate)->where('end_date', '>', $startDate);
                    });
                })
                ->whereNotIn('rental_status', ['cancelled', 'completed'])
                ->exists();

            if ($existingBooking) {
                return response()->json(['message' => 'Vehicle is already booked for the selected dates.'], 409); // 409 Conflict
            }

            $totalPrice = $rentalPeriod * $vehicle->rental_price;

            $booking = Booking::create([
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id,
                'rental_period' => $rentalPeriod,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'delivery_location' => $data['delivery_location'],
                'rental_status' => 'pending',
                'total_price' => $totalPrice,
                'payment_proof' => 'unpaid',
                'bank_transfer' => $bank->id,
                'notes' => $data['notes'],
            ]);

            return response()->json(
                [
                    'statusCode' => 201,
                    'message' => 'Booking created successfully. Please proceed with payment.',
                    'data' => [
                        'booking_id' => $booking->id,
                        'vehicle_name' => $vehicle->vehicle_name,
                        'date_range' => $startDate->toDateString() . ' - ' . $endDate->toDateString(),
                        'rental_period_days' => $rentalPeriod,
                        'total_price' => $totalPrice,
                        'bank_name' => $bank->name_bank,
                        'account_number' => $bank->number,
                        'notes' => $booking->notes,
                        'rental_status' => $booking->rental_status,
                        'payment_status' => $booking->payment_proof,
                    ],
                ],
                201,
            );
        } catch (\Exception $e) {

            return response()->json(['message' => 'Server error during booking process.', 'error_detail' => $e->getMessage()], 500);
        }
    }

    /**
     * User uploads payment proof for their booking.
     */
    public function uploadPaymentProof(Request $request, Booking $booking)
    {
        $user = Auth::user();
        if ($booking->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to update this booking.'], 403);
        }

        if (!in_array($booking->payment_proof, ['unpaid', 'pending'])) {
            return response()->json(['message' => 'Payment proof cannot be uploaded for this booking status: ' . $booking->payment_proof], 400);
        }
        if ($booking->rental_status == 'cancelled') {
            return response()->json(['message' => 'Cannot upload payment proof for a cancelled booking.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Maks 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($booking->secure_url_image) {
            $oldPath = str_replace(Storage::url(''), '', $booking->secure_url_image);
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('image')->store('payment_proofs', 'public');
        $booking->secure_url_image = Storage::url($path);
        $booking->public_url_image = Storage::url($path);
        $booking->payment_proof = 'pending';
        $booking->save();

        return response()->json([
            'statusCode' => 200,
            'message' => 'Payment proof uploaded successfully. Waiting for admin confirmation.',
            'data' => $booking->only(['id', 'user_id', 'vehicle_id', 'rental_period', 'start_date', 'end_date', 'delivery_location', 'rental_status', 'total_price', 'secure_url_image', 'public_url_image', 'payment_proof', 'bank_transfer', 'notes', 'created_at', 'updated_at']),
        ]);
    }

    /**
     * Get booking history for the authenticated user.
     */
    public function userBookingHistory(Request $request)
    {
        $user = Auth::user();
        $pageSize = $request->query('pagesize', 10);

        $bookings = Booking::where('user_id', $user->id)
            ->with(['vehicle:id,vehicle_name,vehicle_type,public_url_image', 'deliveryAddress:id,full_name,phone,full_address,latitude,longitude', 'bank:id,name_bank,number'])
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize);

        $transformedBookings = $bookings->getCollection()->transform(function ($booking) {
            return [
                'booking_id' => $booking->id,
                'rental_period_days' => $booking->rental_period,
                'start_date' => $booking->start_date->toIso8601String(),
                'end_date' => $booking->end_date->toIso8601String(),
                'total_price' => $booking->total_price,
                'rental_status' => $booking->rental_status,
                'payment_status' => $booking->payment_proof,
                'payment_image_url' => $booking->public_url_image,
                'notes' => $booking->notes,
                'vehicle' => $booking->vehicle
                    ? [
                        'vehicle_id' => $booking->vehicle->id,
                        'vehicle_name' => $booking->vehicle->vehicle_name,
                        'vehicle_type' => $booking->vehicle->vehicle_type,
                        'vehicle_image_url' => $booking->vehicle->public_url_image,
                    ]
                    : null,
                'delivery_address' => $booking->deliveryAddress
                    ? [
                        'id' => $booking->deliveryAddress->id,
                        'full_name' => $booking->deliveryAddress->full_name,
                        'phone' => $booking->deliveryAddress->phone,
                        'full_address_text' => $booking->deliveryAddress->full_address,
                        'latitude' => $booking->deliveryAddress->latitude,
                        'longitude' => $booking->deliveryAddress->longitude,
                    ]
                    : null,
                'bank_details' => $booking->bank
                    ? [
                        'id' => $booking->bank->id,
                        'bank_name' => $booking->bank->name_bank,
                        'account_number' => $booking->bank->number,
                    ]
                    : null,
                'created_at' => $booking->created_at->toIso8601String(),
                'updated_at' => $booking->updated_at->toIso8601String(),
            ];
        });

        return response()->json([
            'statusCode' => 200,
            'message' => 'Bookings retrieved successfully',
            'data' => [
                'success' => true,
                'data' => $transformedBookings,
                'message' => 'Data booking berhasil diambil',
            ],
            'pagination' => [
                'page' => $bookings->currentPage(),
                'pagesize' => $bookings->perPage(),
                'totalItems' => $bookings->total(),
                'totalPages' => $bookings->lastPage(),
            ],
        ]);
    }

    /**
     * Get detail of a specific booking for the authenticated user.
     */
    public function userBookingDetail(Booking $booking)
    {
        $user = Auth::user();
        if ($booking->user_id !== $user->id) {
            return response()->json(['message' => 'Booking not found or access denied.'], 404);
        }

        $booking->load(['user:id,username,email,full_name,phone', 'vehicle.brand:id,brand_name', 'deliveryAddress', 'bank']);

        $transformedBooking = [
            'id' => $booking->id,
            'user_id' => $booking->user_id,
            'vehicle_id' => $booking->vehicle_id,
            'rental_period_days' => $booking->rental_period,
            'start_date' => $booking->start_date->toIso8601String(),
            'end_date' => $booking->end_date->toIso8601String(),
            'delivery_location_id' => $booking->delivery_location, // ID Alama
            'rental_status' => $booking->rental_status,
            'total_price' => $booking->total_price,
            'payment_image_secure_url' => $booking->secure_url_image,
            'payment_image_public_url' => $booking->public_url_image,
            'payment_status' => $booking->payment_proof,
            'bank_transfer_id' => $booking->bank_transfer,
            'notes' => $booking->notes,
            'created_at' => $booking->created_at->toIso8601String(),
            'updated_at' => $booking->updated_at->toIso8601String(),
            'user_details' => $booking->user,
            'vehicle_details' => $booking->vehicle,
            'delivery_address_details' => $booking->deliveryAddress,
            'bank_details' => $booking->bank,
        ];

        return response()->json([
            'statusCode' => 200,
            'message' => 'Booking retrieved successfully',
            'data' => $transformedBooking,
        ]);
    }

    /**
     * User cancels their own booking.
     */
    public function userCancelBooking(Booking $booking)
    {
        $user = Auth::user();
        if ($booking->user_id !== $user->id) {
            return response()->json(['message' => 'Booking not found or unauthorized.'], 403);
        }

        if ($booking->rental_status !== 'pending' || !in_array($booking->payment_proof, ['unpaid', 'pending'])) {
            return response()->json(['message' => 'This booking cannot be cancelled at its current state.'], 400);
        }

        $booking->rental_status = 'cancelled';

        $booking->save();

        return response()->json([
            'statusCode' => 200,
            'message' => 'Booking cancelled successfully.',
            'data' => $booking->fresh()->only(['id', 'rental_status', 'payment_proof']),
        ]);
    }
}
