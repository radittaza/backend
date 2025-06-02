<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $itemsPerPage = $request->input('itemsPerPage', 10);
        $skip = $request->input('skip', 0);
        $page = $skip / $itemsPerPage + 1;

        $vehicles = Vehicle::with('brand:id,brand_name,public_url_image')
            ->paginate($itemsPerPage, ['*'], 'page', $page);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Vehicles retrieved successfully',
            'data' => $vehicles->items(),
            'pagination' => [
                'page' => $vehicles->currentPage(),
                'pagesize' => (int) $vehicles->perPage(),
                'totalItems' => $vehicles->total(),
                'totalPages' => $vehicles->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_id' => 'required|exists:brands,id',
            'vehicle_type' => 'required|in:motorcycle,car',
            'vehicle_name' => 'required|string|max:255',
            'rental_price' => 'required|integer|min:0',
            'availability_status' => 'sometimes|in:available,rented,inactive',
            'year' => 'required|date_format:Y',
            'seats' => 'required|integer|min:1',
            'horse_power' => 'required|integer|min:0',
            'description' => 'required|string',
            'specification_list' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if (isset($data['year'])) {
            $data['year'] = Carbon::createFromFormat('Y', $data['year'])->startOfYear();
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('vehicles', 'public');
            $data['public_url_image'] = Storage::url($path);
            $data['secure_url_image'] = Storage::url($path);
        }


        $vehicle = Vehicle::create($data);

        return response()->json(
            [
                'statusCode' => 201,
                'message' => 'Vehicle created successfully',
                'data' => $vehicle->load('brand:id,brand_name'),
            ],
            201,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        // Sesuai respons Postman Anda, load relasi yang dibutuhkan
        $vehicle->load([
            'brand:id,brand_name,public_url_image,secure_url_image,created_at,updated_at', // Pilih field yang spesifik
            'bookings' => function ($query) {
                // Batasi field dari bookings jika perlu, atau load relasi booking lebih lanjut
                $query->with(['user:id,username,email', 'deliveryAddress', 'bank']);
            },
            'banners' => function ($query) {
                // Batasi field dari banners
                $query->with('user:id,username');
            },
        ]);
        return response()->json([
            'message' => 'Vehicle retrieved successfully',
            'data' => $vehicle,
        ]);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'brand_id' => 'sometimes|required|exists:brands,id',
            'vehicle_type' => 'sometimes|required|in:motorcycle,car',
            'vehicle_name' => 'sometimes|required|string|max:255',
            'rental_price' => 'sometimes|required|integer|min:0',
            'availability_status' => 'sometimes|in:available,rented,inactive',
            'year' => 'sometimes|required|date_format:Y',
            'seats' => 'sometimes|required|integer|min:1',
            'horse_power' => 'sometimes|required|integer|min:0',
            'description' => 'sometimes|required|string',
            'specification_list' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if (isset($data['year'])) {
            $data['year'] = Carbon::createFromFormat('Y', $data['year'])->startOfYear();
        }

        if ($request->hasFile('image')) {
            if ($vehicle->public_url_image) {
                $oldPath = str_replace(Storage::url(''), '', $vehicle->public_url_image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('vehicles', 'public');
            $data['public_url_image'] = Storage::url($path);
            $data['secure_url_image'] = Storage::url($path);
        }

        $vehicle->update($data);

        return response()->json([
            'message' => 'Vehicle updated successfully',
            'data' => $vehicle->fresh()->load('brand:id,brand_name'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        if ($vehicle->public_url_image) {
            $oldPath = str_replace(Storage::url(''), '', $vehicle->public_url_image);
            Storage::disk('public')->delete($oldPath);
        }

        if (
            $vehicle
                ->bookings()
                ->whereNotIn('rental_status', ['completed', 'cancelled'])
                ->exists()
        ) {
            return response()->json(['message' => 'Cannot delete vehicle. It has active or pending bookings.'], 400);
        }

        $vehicle->delete();
        return response()->json(['message' => 'Vehicle deleted successfully']);
    }

    /**
     * Search for vehicles.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        if (!$query) {
            return response()->json(['message' => 'Search query is required'], 400);
        }

        $itemsPerPage = $request->input('itemsPerPage', 10);
        $skip = $request->input('skip', 0);
        $page = $skip / $itemsPerPage + 1;

        $vehicles = Vehicle::where(function ($q) use ($query) {
            $q->where('vehicle_name', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%")
                ->orWhere('vehicle_type', 'LIKE', "%{$query}%");
        })
            ->orWhereHas('brand', function ($q) use ($query) {
                $q->where('brand_name', 'LIKE', "%{$query}%");
            })
            ->with('brand:id,brand_name')
            ->paginate($itemsPerPage, ['*'], 'page', $page);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Vehicle search results retrieved successfully',
            'data' => $vehicles->items(),
            'pagination' => [
                'page' => $vehicles->currentPage(),
                'pagesize' => (int) $vehicles->perPage(),
                'totalItems' => $vehicles->total(),
                'totalPages' => $vehicles->lastPage(),
            ],
        ]);
    }
}
