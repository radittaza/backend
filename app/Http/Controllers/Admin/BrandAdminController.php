<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandAdminController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $brands = Brand::when($search, function ($query, $search) {
            return $query->where('brand_name', 'like', "%{$search}%");
        })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.brands.index', compact('brands', 'search'));
    }

    public function create()
    {
        return view('admin.brands.create_edit');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'brand_name' => 'required|string|max:255|unique:brands,brand_name',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $brandData = $validatedData;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('brands', 'public');
            $brandData['public_url_image'] = Storage::url($path);
            $brandData['secure_url_image'] = Storage::url($path);
        }

        Brand::create($brandData);

        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully.');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.create_edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validatedData = $request->validate([
            'brand_name' => 'required|string|max:255|unique:brands,brand_name,' . $brand->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $brandData = $validatedData;

        if ($request->hasFile('image')) {
            if ($brand->public_url_image) {
                $oldPath = str_replace(Storage::url(''), '', $brand->public_url_image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('brands', 'public');
            $brandData['public_url_image'] = Storage::url($path);
            $brandData['secure_url_image'] = Storage::url($path);
        }

        $brand->update($brandData);

        return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->vehicles()->exists()) {
            return redirect()->route('admin.brands.index')->with('error', 'Cannot delete brand. It has associated vehicles. Please remove or reassign vehicles first.');
        }

        if ($brand->public_url_image) {
            $oldPath = str_replace(Storage::url(''), '', $brand->public_url_image);
            Storage::disk('public')->delete($oldPath);
        }

        $brandName = $brand->brand_name;
        $brand->delete();

        return redirect()
            ->route('admin.brands.index')
            ->with('success', "Brand '{$brandName}' deleted successfully.");
    }
}
