@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4 mb-4">Manage Users</h1>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-table me-1"></i> User List</span>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create New User
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by username, email, or name..." value="{{ $search ?? '' }}">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
                    @if($search)
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->full_name ?? '-' }}</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge bg-{{ $user->role == 'admin' ? 'danger' : 'info' }}">{{ ucfirst($user->role) }}</span></td>
                                <td><span class="badge bg-{{ $user->status == 'active' ? 'success' : ($user->status == 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($user->status) }}</span></td>
                                <td>{{ $user->created_at->format('d M Y, H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm me-1" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    {{-- Tombol Show jika diperlukan:
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info btn-sm me-1" title="View">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    --}}
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete" {{ Auth::id() == $user->id ? 'disabled' : '' }}>
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $users->appends(['search' => $search])->links() }}
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
@endsection
