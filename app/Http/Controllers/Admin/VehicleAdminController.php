<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class VehicleAdminController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $brand_filter = $request->query('brand_id');

        $vehicles = Vehicle::with('brand:id,brand_name')
            ->when($search, function ($query, $search) {
                return $query->where('vehicle_name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
            })
            ->when($brand_filter, function ($query, $brand_filter) {
                return $query->where('brand_id', $brand_filter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $brands = Brand::orderBy('brand_name')->get();

        return view('admin.vehicles.index', compact('vehicles', 'brands', 'search', 'brand_filter'));
    }

    public function create()
    {
        $brands = Brand::orderBy('brand_name')->get();
        $vehicleTypes = ['motorcycle', 'car'];
        $availabilityStatuses = ['available', 'rented', 'inactive'];
        return view('admin.vehicles.create_edit', compact('brands', 'vehicleTypes', 'availabilityStatuses'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'vehicle_type' => 'required|in:motorcycle,car',
            'vehicle_name' => 'required|string|max:255',
            'rental_price' => 'required|integer|min:0',
            'availability_status' => 'required|in:available,rented,inactive',
            'year' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1), // Validasi tahun YYYY
            'seats' => 'required|integer|min:1',
            'horse_power' => 'required|integer|min:0',
            'description' => 'required|string|max:5000',
            'specification_list' => 'required|string|max:5000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $vehicleData = $validatedData;

        $vehicleData['year'] = Carbon::createFromFormat('Y', $validatedData['year'])->startOfYear();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('vehicles', 'public');
            $vehicleData['public_url_image'] = Storage::url($path);
            $vehicleData['secure_url_image'] = Storage::url($path);
        } else {
            $vehicleData['public_url_image'] = null;
            $vehicleData['secure_url_image'] = null;
        }

        Vehicle::create($vehicleData);

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle created successfully.');
    }


    public function edit(Vehicle $vehicle)
    {
        $brands = Brand::orderBy('brand_name')->get();
        $vehicleTypes = ['motorcycle', 'car'];
        $availabilityStatuses = ['available', 'rented', 'inactive'];
        $vehicle->year_form = $vehicle->year ? Carbon::parse($vehicle->year)->format('Y') : null;
        return view('admin.vehicles.create_edit', compact('vehicle', 'brands', 'vehicleTypes', 'availabilityStatuses'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validatedData = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'vehicle_type' => 'required|in:motorcycle,car',
            'vehicle_name' => 'required|string|max:255',
            'rental_price' => 'required|integer|min:0',
            'availability_status' => 'required|in:available,rented,inactive',
            'year' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1),
            'seats' => 'required|integer|min:1',
            'horse_power' => 'required|integer|min:0',
            'description' => 'required|string|max:5000',
            'specification_list' => 'required|string|max:5000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $vehicleData = $validatedData;
        $vehicleData['year'] = Carbon::createFromFormat('Y', $validatedData['year'])->startOfYear();

        if ($request->hasFile('image')) {
            if ($vehicle->public_url_image) {
                $oldPath = str_replace(Storage::url(''), '', $vehicle->public_url_image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('vehicles', 'public');
            $vehicleData['public_url_image'] = Storage::url($path);
            $vehicleData['secure_url_image'] = Storage::url($path);
        }

        $vehicle->update($vehicleData);

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle)
    {
        if (
            $vehicle
                ->bookings()
                ->whereNotIn('rental_status', ['completed', 'cancelled'])
                ->exists()
        ) {
            return redirect()->route('admin.vehicles.index')->with('error', 'Cannot delete vehicle. It has active or pending bookings.');
        }

        if ($vehicle->public_url_image) {
            $oldPath = str_replace(Storage::url(''), '', $vehicle->public_url_image);
            Storage::disk('public')->delete($oldPath);
        }

        $vehicleName = $vehicle->vehicle_name;
        $vehicle->delete();

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', "Vehicle '{$vehicleName}' deleted successfully.");
    }
}
