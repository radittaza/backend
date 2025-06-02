@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4 mb-4">Manage Brands</h1>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-tags me-1"></i> Brand List</span>
            <a href="{{ route('admin.brands.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create New Brand
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.brands.index') }}" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by brand name..." value="{{ $search ?? '' }}">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
                    @if($search)
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Brand Name</th>
                            <th>Image</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($brands as $brand)
                            <tr>
                                <td>{{ $brand->id }}</td>
                                <td>{{ $brand->brand_name }}</td>
                                <td>
                                    @if($brand->public_url_image)
                                        <img src="{{ $brand->public_url_image }}" alt="{{ $brand->brand_name }}" style="max-height: 50px; max-width: 100px;">
                                    @else
                                        <span class="text-muted">No image</span>
                                    @endif
                                </td>
                                <td>{{ $brand->created_at ? $brand->created_at->format('d M Y, H:i') : '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-warning btn-sm me-1" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this brand? This might affect vehicles associated with it.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No brands found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $brands->appends(['search' => $search])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
