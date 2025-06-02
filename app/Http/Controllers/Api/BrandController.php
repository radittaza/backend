<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $itemsPerPage = $request->input('itemsPerPage', 10);
        $skip = $request->input('skip', 0);
        $page = ($skip / $itemsPerPage) + 1;

        $brands = Brand::withCount('vehicles')
                        ->paginate($itemsPerPage, ['*'], 'page', $page);

        return response()->json([
            'message' => 'Brands retrieved successfully',
            'data' => $brands->items(),
            'pagination' => [
                'page' => $brands->currentPage(),
                'pagesize' => (int)$brands->perPage(),
                'totalItems' => $brands->total(),
                'totalPages' => $brands->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_name' => 'required|string|max:255|unique:brands,brand_name',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('brands', 'public');
            $data['public_url_image'] = Storage::url($path);
            $data['secure_url_image'] = Storage::url($path);
        }

        $brand = Brand::create($data);

        return response()->json([
            'statusCode' => 201, // Sesuai contoh Postman
            'message' => 'Brand created successfully',
            'data' => $brand
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        $brand->load('vehicles');
        return response()->json([
            'message' => 'Brand retrieved successfully',
            'data' => $brand
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $validator = Validator::make($request->all(), [
            'brand_name' => 'sometimes|required|string|max:255|unique:brands,brand_name,' . $brand->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('image')) {
            if ($brand->public_url_image) {
                $oldPath = str_replace(Storage::url(''), '', $brand->public_url_image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('brands', 'public');
            $data['public_url_image'] = Storage::url($path);
            $data['secure_url_image'] = Storage::url($path);
        }

        $brand->update($data);

        return response()->json([
            'message' => 'Brand updated successfully',
            'data' => $brand->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        if ($brand->public_url_image) {
            $oldPath = str_replace(Storage::url(''), '', $brand->public_url_image);
            Storage::disk('public')->delete($oldPath);
        }

        if ($brand->vehicles()->exists()) {
            return response()->json(['message' => 'Cannot delete brand. It has associated vehicles.'], 400);
        }

        $brand->delete();

        return response()->json(['message' => 'Brand deleted successfully']);
    }
}
