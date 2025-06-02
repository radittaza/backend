@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4 mb-4">Manage Bookings</h1>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar-check me-1"></i> Booking List
        </div>
        <div class="card-body">
            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="mb-4 p-3 border rounded">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search_user" class="form-label">Search User (Username/Email)</label>
                        <input type="text" name="search_user" id="search_user" class="form-control form-control-sm" value="{{ $request->search_user }}">
                    </div>
                    <div class="col-md-3">
                        <label for="vehicle_id" class="form-label">Vehicle</label>
                        <select name="vehicle_id" id="vehicle_id" class="form-select form-select-sm">
                            <option value="">All Vehicles</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ $request->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->vehicle_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="rental_status" class="form-label">Rental Status</label>
                        <select name="rental_status" id="rental_status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            @foreach($rentalStatuses as $status)
                                <option value="{{ $status }}" {{ $request->rental_status == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="payment_proof" class="form-label">Payment Status</label>
                        <select name="payment_proof" id="payment_proof" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            @foreach($paymentStatuses as $status)
                                <option value="{{ $status }}" {{ $request->payment_proof == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                     <div class="col-md-3">
                        <label for="date_from" class="form-label">Start Date From</label>
                        <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ $request->date_from }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Start Date To</label>
                        <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ $request->date_to }}">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button class="btn btn-primary btn-sm w-100" type="submit"><i class="fas fa-filter"></i> Filter</button>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary btn-sm w-100"><i class="fas fa-times"></i> Clear</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Vehicle</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Total Price</th>
                            <th>Rental Status</th>
                            <th>Payment Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr>
                                <td>{{ $booking->id }}</td>
                                <td>{{ $booking->user->username ?? 'N/A' }}</td>
                                <td>{{ $booking->vehicle->vehicle_name ?? 'N/A' }}</td>
                                <td>{{ $booking->start_date->format('d M Y H:i') }}</td>
                                <td>{{ $booking->end_date->format('d M Y H:i') }}</td>
                                <td>Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                                <td><span class="badge bg-{{ strtolower($booking->rental_status) == 'active' ? 'success' : (strtolower($booking->rental_status) == 'pending' ? 'warning' : (strtolower($booking->rental_status) == 'completed' ? 'primary' : 'danger')) }}">{{ ucfirst($booking->rental_status) }}</span></td>
                                <td><span class="badge bg-{{ strtolower($booking->payment_proof) == 'paid' ? 'success' : (strtolower($booking->payment_proof) == 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($booking->payment_proof) }}</span></td>
                                <td>{{ $booking->created_at->format('d M Y, H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-info btn-sm" title="View Details & Update Status">
                                        <i class="fas fa-eye"></i> Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No bookings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $bookings->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
