@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4 mb-4">{{ isset($vehicle) ? 'Edit Vehicle' : 'Create New Vehicle' }}</h1>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-car-side me-1"></i>
            {{ isset($vehicle) ? 'Edit Vehicle: ' . $vehicle->vehicle_name : 'New Vehicle Form' }}
        </div>
        <div class="card-body">
            <form method="POST" action="{{ isset($vehicle) ? route('admin.vehicles.update', $vehicle->id) : route('admin.vehicles.store') }}" enctype="multipart/form-data">
                @csrf
                @if(isset($vehicle))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="vehicle_name" class="form-label">Vehicle Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('vehicle_name') is-invalid @enderror" id="vehicle_name" name="vehicle_name" value="{{ old('vehicle_name', $vehicle->vehicle_name ?? '') }}" required>
                        @error('vehicle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="brand_id" class="form-label">Brand <span class="text-danger">*</span></label>
                        <select class="form-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id" required>
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $vehicle->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->brand_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('brand_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="vehicle_type" class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('vehicle_type') is-invalid @enderror" id="vehicle_type" name="vehicle_type" required>
                            @foreach($vehicleTypes as $type)
                                <option value="{{ $type }}" {{ old('vehicle_type', $vehicle->vehicle_type ?? '') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="year" class="form-label">Year (YYYY) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('year') is-invalid @enderror" id="year" name="year" value="{{ old('year', isset($vehicle) ? Carbon\Carbon::parse($vehicle->year)->format('Y') : '') }}" required placeholder="e.g., 2023">
                        @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="rental_price" class="form-label">Rental Price/Day (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('rental_price') is-invalid @enderror" id="rental_price" name="rental_price" value="{{ old('rental_price', $vehicle->rental_price ?? '') }}" required min="0">
                        @error('rental_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="seats" class="form-label">Seats <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('seats') is-invalid @enderror" id="seats" name="seats" value="{{ old('seats', $vehicle->seats ?? '') }}" required min="1">
                        @error('seats') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="horse_power" class="form-label">Horse Power (HP) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('horse_power') is-invalid @enderror" id="horse_power" name="horse_power" value="{{ old('horse_power', $vehicle->horse_power ?? '') }}" required min="0">
                        @error('horse_power') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="availability_status" class="form-label">Availability <span class="text-danger">*</span></label>
                        <select class="form-select @error('availability_status') is-invalid @enderror" id="availability_status" name="availability_status" required>
                            @foreach($availabilityStatuses as $status)
                                <option value="{{ $status }}" {{ old('availability_status', $vehicle->availability_status ?? 'available') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                        @error('availability_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description', $vehicle->description ?? '') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="specification_list" class="form-label">Specification List <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('specification_list') is-invalid @enderror" id="specification_list" name="specification_list" rows="3" required placeholder="e.g., AC, Bluetooth, GPS, Automatic Transmission">{{ old('specification_list', $vehicle->specification_list ?? '') }}</textarea>
                    <small class="form-text text-muted">Enter specifications separated by comma or new line. Will be stored as text.</small>
                    @error('specification_list') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Vehicle Image</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image">
                    @if(isset($vehicle) && $vehicle->public_url_image)
                        <div class="mt-2">
                            <small>Current Image:</small><br>
                            <img src="{{ $vehicle->public_url_image }}" alt="{{ $vehicle->vehicle_name }}" style="max-height: 150px; border: 1px solid #ddd; padding: 5px;">
                        </div>
                    @endif
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-text text-muted">Max file size: 2MB. Allowed types: JPG, PNG, GIF, WEBP.</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ isset($vehicle) ? 'Update Vehicle' : 'Create Vehicle' }}
                </button>
                <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
