@extends('layouts.app')

@section('title', 'Edit Role Assignment - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Edit Role Assignment</h1>
            <p class="text-muted mb-0">Update role and reporting manager for {{ $employee->first_name }} {{ $employee->last_name }}</p>
        </div>
        <a href="{{ route('role-assignments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Role & Manager Configuration</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('role-assignments.update', $employee->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Employee:</strong> {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_code }})
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="role" class="form-label">Role</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="">Select Role</option>
                            <option value="admin" {{ $employee->user && $employee->user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="cto" {{ $employee->user && $employee->user->role === 'cto' ? 'selected' : '' }}>CTO (Chief Technology Officer)</option>
                            <option value="hr" {{ $employee->user && $employee->user->role === 'hr' ? 'selected' : '' }}>HR</option>
                            <option value="manager" {{ $employee->user && $employee->user->role === 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="employee" {{ $employee->user && $employee->user->role === 'employee' ? 'selected' : '' }}>Employee</option>
                        </select>
                        <small class="form-text text-muted">
                            <strong>Admin:</strong> Full system access<br>
                            <strong>CTO:</strong> Technology department head<br>
                            <strong>HR:</strong> Human resources management<br>
                            <strong>Manager:</strong> Team management<br>
                            <strong>Employee:</strong> Regular employee
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label for="reporting_manager_id" class="form-label">Reporting Manager</label>
                        <select name="reporting_manager_id" id="reporting_manager_id" class="form-select">
                            <option value="">No Manager (Top Level)</option>
                            @foreach($managers as $manager)
                                @if($manager->id != $employee->id)
                                    <option value="{{ $manager->id }}" {{ $employee->employmentDetail && $employee->employmentDetail->reporting_manager_id == $manager->id ? 'selected' : '' }}>
                                        {{ $manager->first_name }} {{ $manager->last_name }} ({{ $manager->employee_code ?? 'N/A' }})
                                        @if($manager->user)
                                            - {{ ucfirst($manager->user->role) }}
                                        @endif
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Select the person this employee reports to</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Current Department</label>
                        <input type="text" class="form-control" value="{{ $employee->employmentDetail->department ?? 'N/A' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Current Designation</label>
                        <input type="text" class="form-control" value="{{ $employee->employmentDetail->designation ?? 'N/A' }}" readonly>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('role-assignments.index') }}'">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
