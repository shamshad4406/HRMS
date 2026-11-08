@extends('layouts.app')

@section('title', 'Dashboard - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header">
        <h1>Dashboard</h1>
        <p class="text-muted">Welcome back, {{ auth()->user()->name }}!</p>
    </div>

    <!-- Welcome Banner for Employees -->
    @if(auth()->user()->role === 'employee')
        <div class="card card-hero mb-4">
            <div class="card-body py-4">
                <h3 class="card-title mb-2">Welcome, {{ auth()->user()->name }}!</h3>
                <p class="card-text mb-3">You can now mark your attendance and manage your leave requests.</p>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('attendances.create') }}" class="btn btn-light text-primary fw-semibold">
                        <i class="bi bi-calendar-check me-2"></i>Mark Attendance
                    </a>
                    <a href="{{ route('change.password') }}" class="btn btn-outline-light">
                        <i class="bi bi-key me-2"></i>Change Password
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-1">Total Employees</h3>
                    <p class="stat-number mb-0">{{ $totalEmployees }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-building fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-1">Departments</h3>
                    <p class="stat-number mb-0">{{ $totalDepartments }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-calendar-check fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-1">Present Today</h3>
                    <p class="stat-number mb-0">{{ $presentToday }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-calendar3 fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-1">On Leave</h3>
                    <p class="stat-number mb-0">{{ $onLeave }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Activity -->
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Activity</h5>
                </div>
                <div class="card-body">
                    @if(count($recentActivities) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentActivities as $activity)
                                <div class="list-group-item px-0 py-3 d-flex align-items-center gap-3">
                                    <div class="activity-icon">
                                        {!! $activity['icon'] !!}
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1 activity-text">{!! $activity['text'] !!}</p>
                                        <small class="text-muted">{{ $activity['time'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No recent activity</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if(auth()->user()->role === 'admin')
                        <div class="row g-3">
                            <div class="col-6">
                                <a href="{{ route('employees.create') }}" class="quick-action-btn">
                                    <i class="bi bi-person-plus"></i>
                                    <span>Add Employee</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('reports.index') }}" class="quick-action-btn">
                                    <i class="bi bi-graph-up"></i>
                                    <span>View Reports</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('payrolls.generate') }}" class="quick-action-btn">
                                    <i class="bi bi-cash-coin"></i>
                                    <span>Process Payroll</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('attendances.create') }}" class="quick-action-btn">
                                    <i class="bi bi-calendar-check"></i>
                                    <span>Mark Attendance</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('leave.create') }}" class="quick-action-btn">
                                    <i class="bi bi-calendar3"></i>
                                    <span>Apply Leave</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('admin.users') }}" class="quick-action-btn">
                                    <i class="bi bi-people"></i>
                                    <span>Manage Users</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="row g-3">
                            <div class="col-6">
                                <a href="{{ route('attendances.create') }}" class="quick-action-btn">
                                    <i class="bi bi-calendar-check"></i>
                                    <span>Mark Attendance</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('leave.create') }}" class="quick-action-btn">
                                    <i class="bi bi-calendar3"></i>
                                    <span>Apply Leave</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('leave.index') }}" class="quick-action-btn">
                                    <i class="bi bi-list-check"></i>
                                    <span>Leave History</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('change.password') }}" class="quick-action-btn">
                                    <i class="bi bi-key"></i>
                                    <span>Change Password</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

