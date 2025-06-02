<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{

    public function index(Request $request)
    {
        $query = Banner::with(['user:id,username', 'vehicle:id,vehicle_name,public_url_image']);


        $banners = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Banners retrieved successfully',
            'data' => $banners->items(),
            'pagination' => [
                'page' => $banners->currentPage(),
                'pagesize' => $banners->perPage(),
                'totalItems' => $banners->total(),
                'totalPages' => $banners->lastPage(),
            ]
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'vehicle_id' => 'required|exists:vehicles,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $data['public_url_image'] = Storage::url($path);
            $data['secure_url_image'] = Storage::url($path);
        }

        $banner = Banner::create($data);

        return response()->json([
            'statusCode' => 201,
            'message' => 'Banner created successfully',
            'data' => $banner->load(['user:id,username', 'vehicle:id,vehicle_name'])
        ], 201);
    }


    public function show(Banner $banner)
    {

        return response()->json([
            'statusCode' => 200,
            'message' => 'Banner retrieved successfully',
            'data' => $banner->load(['user:id,username', 'vehicle:id,vehicle_name,public_url_image'])
        ]);
    }

    /**
     * Update the specified resource in storage. (Admin only)
     */
    public function update(Request $request, Banner $banner)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:1000',
            'vehicle_id' => 'sometimes|required|exists:vehicles,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('image')) {
            if ($banner->public_url_image) {
                $oldPath = str_replace(Storage::url(''), '', $banner->public_url_image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('banners', 'public');
            $data['public_url_image'] = Storage::url($path);
            $data['secure_url_image'] = Storage::url($path);
        }

        $banner->update($data);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Banner updated successfully',
            'data' => $banner->fresh()->load(['user:id,username', 'vehicle:id,vehicle_name'])
        ]);
    }


    public function destroy(Banner $banner)
    {
        if ($banner->public_url_image) {
            $oldPath = str_replace(Storage::url(''), '', $banner->public_url_image);
            Storage::disk('public')->delete($oldPath);
        }

        $banner->delete();
        return response()->json([
            'statusCode' => 200,
            'message' => 'Banner deleted successfully'
        ]);
    }
}
