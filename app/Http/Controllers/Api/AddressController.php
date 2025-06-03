<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $pageSize = $request->query('pagesize', 10);

        $addresses = $user->addresses()->paginate($pageSize);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Addresses retrieved successfully',
            'data' => $addresses->items(),
            'pagination' => [
                'page' => $addresses->currentPage(),
                'pagesize' => $addresses->perPage(),
                'totalItems' => $addresses->total(),
                'totalPages' => $addresses->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'full_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'full_address' => 'nullable|string|max:1000',
            'latitude' => 'nullable|string|max:255',
            'longitude' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = $user->id;

        $address = Address::create($data);

        return response()->json(
            [
                'statusCode' => 201,
                'message' => 'Address created successfully',
                'data' => $address,
            ],
            201,
        );
    }


    public function show(Address $address)
    {
        if (Auth::id() !== $address->user_id) {
            return response()->json(['message' => 'Address not found or unauthorized.'], 404);
        }

        return response()->json([
            'statusCode' => 200,
            'message' => 'Address retrieved successfully',
            'data' => $address,
        ]);
    }

    public function update(Request $request, Address $address)
    {
        if (Auth::id() !== $address->user_id) {
            return response()->json(['message' => 'Address not found or unauthorized.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|nullable|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'full_address' => 'sometimes|nullable|string|max:1000',
            'latitude' => 'sometimes|nullable|string|max:255',
            'longitude' => 'sometimes|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $address->update($validator->validated());

        return response()->json([
            'statusCode' => 200,
            'message' => 'Address updated successfully',
            'data' => $address->fresh(),
        ]);
    }

    public function destroy(Address $address)
    {
        if (Auth::id() !== $address->user_id) {
            return response()->json(['message' => 'Address not found or unauthorized.'], 403);
        }

        $activeBookingsCount = $address
            ->bookings()
            ->whereNotIn('rental_status', ['completed', 'cancelled'])
            ->count();

        if ($activeBookingsCount > 0) {
            return response()->json(['message' => 'Cannot delete address. It is currently used in active or pending bookings.'], 400);
        }

        $address->delete();

        return response()->json([
            'statusCode' => 200,
            'message' => 'Address deleted successfully',
        ]);
    }
}
