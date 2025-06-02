@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">Admin Dashboard</h1>
    <p>Welcome back, {{ Auth::user()->full_name ?? Auth::user()->username }}!</p>

    <div class="row mt-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    Total Users
                    <span class="badge bg-light text-primary ms-2">{{ $totalUsers }}</span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#">View Details</a> {{-- Nanti link ke user management --}}
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    Total Vehicles
                    <span class="badge bg-light text-warning ms-2">{{ $totalVehicles }}</span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#">View Details</a> {{-- Nanti link ke vehicle management --}}
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    Active Bookings
                    <span class="badge bg-light text-success ms-2">{{ $activeBookings }}</span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#">View Details</a> {{-- Nanti link ke booking management --}}
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    Pending Bookings
                    <span class="badge bg-light text-danger ms-2">{{ $pendingBookings }}</span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#">View Details</a> {{-- Nanti link ke booking management --}}
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tempat untuk grafik atau tabel ringkasan lainnya --}}

</div>
@endsection
