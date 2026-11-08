@extends('layouts.app')

@section('title', 'Role Assignments - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Role Assignments</h1>
            <p class="text-muted mb-0">Manage employee roles and reporting managers</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('role-assignments.hierarchy') }}" class="btn btn-outline-info">
                <i class="bi bi-diagram-3 me-2"></i>View Hierarchy
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Emp Code</th>
                            <th>Employee</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Reporting Manager</th>
                            <th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        <tr>
                            <td>
                                @if($employee->photo)
                                    <img src="{{ asset('storage/' . $employee->photo) }}" class="avatar" alt="{{ $employee->first_name }} {{ $employee->last_name }}">
                                @else
                                    <div class="avatar-placeholder">
                                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                    </div>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $employee->employee_code }}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <div class="fw-semibold">{{ $employee->first_name }} {{ $employee->last_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $employee->email }}</td>
                            <td>
                                @if($employee->user)
                                    @switch($employee->user->role)
                                        @case('admin')
                                            <span class="badge bg-danger">Admin</span>
                                            @break
                                        @case('cto')
                                            <span class="badge bg-dark">CTO</span>
                                            @break
                                        @case('hr')
                                            <span class="badge bg-info">HR</span>
                                            @break
                                        @case('manager')
                                            <span class="badge bg-warning">Manager</span>
                                            @break
                                        @case('employee')
                                            <span class="badge bg-secondary">Employee</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark">{{ $employee->user->role }}</span>
                                    @endswitch
                                @else
                                    <span class="badge bg-light text-dark">No User</span>
                                @endif
                            </td>
                            <td>
                                @if($employee->employmentDetail && $employee->employmentDetail->reportingManager)
                                    <span class="badge bg-primary">
                                        {{ $employee->employmentDetail->reportingManager->first_name }} {{ $employee->employmentDetail->reportingManager->last_name }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark">Not Assigned</span>
                                @endif
                            </td>
                            <td>{{ $employee->employmentDetail->department ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('role-assignments.edit', $employee->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Role & Manager">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </td>
                        </tr>
                        @endforeach
                        @if($employees->count() == 0)
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No employees found</p>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
