@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4 mb-4">{{ isset($brand) ? 'Edit Brand' : 'Create New Brand' }}</h1>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-{{ isset($brand) ? 'edit' : 'plus-circle' }}"></i>
            {{ isset($brand) ? 'Edit Brand: ' . $brand->brand_name : 'New Brand Form' }}
        </div>
        <div class="card-body">
            <form method="POST" action="{{ isset($brand) ? route('admin.brands.update', $brand->id) : route('admin.brands.store') }}" enctype="multipart/form-data">
                @csrf
                @if(isset($brand))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="brand_name" class="form-label">Brand Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('brand_name') is-invalid @enderror" id="brand_name" name="brand_name" value="{{ old('brand_name', $brand->brand_name ?? '') }}" required>
                    @error('brand_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Brand Image</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image">
                    @if(isset($brand) && $brand->public_url_image)
                        <div class="mt-2">
                            <small>Current Image:</small><br>
                            <img src="{{ $brand->public_url_image }}" alt="{{ $brand->brand_name }}" style="max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                        </div>
                    @endif
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Max file size: 2MB. Allowed types: JPG, PNG, GIF, WEBP.</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ isset($brand) ? 'Update Brand' : 'Create Brand' }}
                </button>
                <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
