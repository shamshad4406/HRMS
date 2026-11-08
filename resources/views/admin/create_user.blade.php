@extends('layouts.app')

@section('title', 'Create User - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Create User</h1>
            <p class="text-muted mb-0">Add a new user to the system</p>
        </div>
        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Users
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>User Information</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Full Name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control"
                            placeholder="Enter full name"
                            required
                            value="{{ old('name') }}"
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter email address"
                            required
                            value="{{ old('email') }}"
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Create password (min 6 characters)"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Confirm password"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="role" class="form-label">Role</label>
                        <select
                            id="role"
                            name="role"
                            class="form-select"
                            required
                        >
                            <option value="">Select role</option>
                            <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                            <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="hr" {{ old('role') == 'hr' ? 'selected' : '' }}>HR</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-exclamation-triangle me-1"></i>Admin, Manager, and HR roles have elevated permissions. Assign carefully.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label for="personal_information_id" class="form-label">Link to Employee (Optional)</label>
                        <select
                            id="personal_information_id"
                            name="personal_information_id"
                            class="form-select"
                        >
                            <option value="">No employee linked</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('personal_information_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->employee_code }} - {{ $employee->first_name }} {{ $employee->last_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle me-1"></i>Linking to an employee will automatically initialize leave balances.
                        </small>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Create User
                    </button>
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hide success message after 2 seconds
        const successMessage = document.querySelector('.alert-success');
        if (successMessage) {
            setTimeout(function() {
                const alert = new bootstrap.Alert(successMessage);
                alert.close();
            }, 2000);
        }
    });
</script>
@endpush
