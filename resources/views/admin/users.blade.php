@extends('layouts.app')

@section('title', 'User Management - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">User Management</h1>
            <p class="text-muted mb-0">Manage system users and their roles</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Search -->
            <form method="GET" action="{{ route('admin.users') }}" class="d-flex">
                <div class="input-group">
                   
                    <input
                        type="text"
                        class="form-control search-input"
                        name="search"
                        placeholder="Search by name, email, role..."
                        value="{{ $search ?? '' }}"
                       
                    >
                    @if($search ?? null)
                        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary">
                         <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            <!-- <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>Create User
            </a> -->
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Employee</th>
                            <th>Manager</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                @if($user->personalInformation && $user->personalInformation->photo)
                                    <img src="{{ asset('storage/' . $user->personalInformation->photo) }}" class="avatar" alt="{{ $user->name }}">
                                @else
                                    <div class="avatar-placeholder">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $user->name }}</div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role == 'admin')
                                    <span class="badge bg-danger">Admin</span>
                                @elseif($user->role == 'hr')
                                    <span class="badge bg-warning text-dark">HR</span>
                                @elseif($user->role == 'manager')
                                    <span class="badge bg-primary">Manager</span>
                                @else
                                    <span class="badge bg-success">Employee</span>
                                @endif
                            </td>
                            <td>
                                @if($user->personalInformation)
                                    <span class="badge bg-light text-dark">{{ $user->personalInformation->employee_code ?? 'N/A' }}</span>
                                @else
                                    <span class="text-muted">Not linked</span>
                                @endif
                            </td>
                            <td>
                                @if($user->personalInformation && $user->personalInformation->employmentDetail && $user->personalInformation->employmentDetail->reportingManager)
                                    <span class="badge bg-info text-dark">
                                        {{ $user->personalInformation->employmentDetail->reportingManager->first_name }} {{ $user->personalInformation->employmentDetail->reportingManager->last_name }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                @if($user->id !== auth()->id())
                                    <div class="btn-group" role="group">
                                        <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="badge bg-secondary">Current user</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @if($users->count() == 0)
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">
                                    @if($search ?? null)
                                        No users found matching "{{ $search }}"
                                    @else
                                        No users found
                                    @endif
                                </p>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-end mb-0">
                        {{-- Previous Page Link --}}
                        @if($users->onFirstPage())
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->appends(['search' => $search ?? ''])->previousPageUrl() }}">Previous</a>
                            </li>
                        @endif

                        {{-- Page Numbers --}}
                        @for($i = 1; $i <= $users->lastPage(); $i++)
                            @if($i == $users->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <a class="page-link" href="#">{{ $i }}</a>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $users->appends(['search' => $search ?? ''])->url($i) }}">{{ $i }}</a>
                                </li>
                            @endif
                        @endfor

                        {{-- Next Page Link --}}
                        @if($users->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->appends(['search' => $search ?? ''])->nextPageUrl() }}">Next</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Next</a>
                            </li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    </div>
@endsection
