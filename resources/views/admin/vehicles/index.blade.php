@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4 mb-4">Manage Vehicles</h1>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-car me-1"></i> Vehicle List</span>
            <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create New Vehicle
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.vehicles.index') }}" class="mb-3 row g-3 align-items-center">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, description..." value="{{ $search ?? '' }}">
                </div>
                <div class="col-md-4">
                    <select name="brand_id" class="form-select form-select-sm">
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ $brand_filter == $brand->id ? 'selected' : '' }}>
                                {{ $brand->brand_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i> Filter</button>
                     @if($search || $brand_filter)
                        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Brand</th>
                            <th>Type</th>
                            <th>Price/Day</th>
                            <th>Availability</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vehicles as $vehicle)
                            <tr>
                                <td>{{ $vehicle->id }}</td>
                                <td>
                                    @if($vehicle->public_url_image)
                                        <img src="{{ $vehicle->public_url_image }}" alt="{{ $vehicle->vehicle_name }}" style="max-height: 50px; max-width: 80px;">
                                    @else
                                        <span class="text-muted">No image</span>
                                    @endif
                                </td>
                                <td>{{ $vehicle->vehicle_name }}</td>
                                <td>{{ $vehicle->brand->brand_name ?? 'N/A' }}</td>
                                <td>{{ ucfirst($vehicle->vehicle_type) }}</td>
                                <td>Rp {{ number_format($vehicle->rental_price, 0, ',', '.') }}</td>
                                <td><span class="badge bg-{{ $vehicle->availability_status == 'available' ? 'success' : ($vehicle->availability_status == 'rented' ? 'warning' : 'danger') }}">{{ ucfirst($vehicle->availability_status) }}</span></td>
                                <td>{{ $vehicle->year ? Carbon\Carbon::parse($vehicle->year)->format('Y') : '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-warning btn-sm me-1" title="Edit">
                                       Edit
                                    </a>
                                    <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No vehicles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $vehicles->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
