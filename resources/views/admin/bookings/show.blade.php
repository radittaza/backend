@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4 mb-3">Booking Detail #{{ $booking->id }}</h1>
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary btn-sm mb-3"><i class="fas fa-arrow-left"></i> Back to List</a>

    <div class="row">
        {{-- Kolom Detail Booking --}}
        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-info-circle me-1"></i> Booking Information</div>
                <div class="card-body">
                    <p><strong>User:</strong> {{ $booking->user->username ?? 'N/A' }} ({{ $booking->user->email ?? 'N/A' }})</p>
                    <p><strong>Vehicle:</strong> {{ $booking->vehicle->vehicle_name ?? 'N/A' }} (Brand: {{ $booking->vehicle->brand->brand_name ?? 'N/A' }})</p>
                    <p><strong>Rental Period:</strong> {{ $booking->start_date->format('d M Y, H:i') }} - {{ $booking->end_date->format('d M Y, H:i') }} ({{ $booking->rental_period }} day(s))</p>
                    <p><strong>Total Price:</strong> Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
                    <hr>
                    <p><strong>Delivery Address:</strong> {{ $booking->deliveryAddress->full_name ?? 'N/A' }}</p>
                    <p>{{ $booking->deliveryAddress->full_address ?? 'N/A' }}</p>
                    <p>Phone: {{ $booking->deliveryAddress->phone ?? 'N/A' }}</p>
                    @if($booking->deliveryAddress->latitude && $booking->deliveryAddress->longitude)
                        <p><a href="https://www.google.com/maps?q={{ $booking->deliveryAddress->latitude }},{{ $booking->deliveryAddress->longitude }}" target="_blank">View on Map</a></p>
                    @endif
                    <hr>
                    <p><strong>Payment to Bank:</strong> {{ $booking->bank->name_bank ?? 'N/A' }} - {{ $booking->bank->number ?? 'N/A' }}</p>
                    <p><strong>User Notes:</strong> {{ $booking->notes ?: '-' }}</p>
                    <hr>
                    <p><strong>Created At:</strong> {{ $booking->created_at->format('d M Y, H:i:s') }}</p>
                    <p><strong>Last Updated:</strong> {{ $booking->updated_at->format('d M Y, H:i:s') }}</p>

                    @if($booking->public_url_image)
                        <hr>
                        <p><strong>Payment Proof Image:</strong></p>
                        <img src="{{ $booking->public_url_image }}" alt="Payment Proof for Booking #{{ $booking->id }}" class="img-fluid" style="max-height: 300px; border: 1px solid #ddd;">
                    @else
                        <p><strong>Payment Proof Image:</strong> <span class="text-muted">Not uploaded yet.</span></p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom Update Status --}}
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-edit me-1"></i> Update Status</div>
                <div class="card-body">
                    <form action="{{ route('admin.bookings.updateStatus', $booking->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="rental_status" class="form-label"><strong>Current Rental Status:</strong> <span class="badge bg-info">{{ ucfirst($booking->rental_status) }}</span></label>
                            <select name="rental_status" id="rental_status" class="form-select @error('rental_status') is-invalid @enderror">
                                @foreach($rentalStatuses as $status)
                                    <option value="{{ $status }}" {{ old('rental_status', $booking->rental_status) == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('rental_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="payment_proof" class="form-label"><strong>Current Payment Status:</strong> <span class="badge bg-warning">{{ ucfirst($booking->payment_proof) }}</span></label>
                            <select name="payment_proof" id="payment_proof" class="form-select @error('payment_proof') is-invalid @enderror">
                                 @foreach($paymentStatuses as $status)
                                    <option value="{{ $status }}" {{ old('payment_proof', $booking->payment_proof) == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_proof') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Update Booking Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
