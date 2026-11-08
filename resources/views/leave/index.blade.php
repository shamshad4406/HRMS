@extends('layouts.app')

@section('title', 'Leave Management - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Leave Management</h1>
            <p class="text-muted mb-0">Manage leave requests and balances</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('leave.create') }}" class="btn btn-primary">
                <i class="bi bi-calendar-plus me-2"></i>Apply for Leave
            </a>
            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                <a href="{{ route('leave.approvals') }}" class="btn btn-outline-primary">
                    <i class="bi bi-clock-history me-2"></i>Pending Approvals
                </a>
            @endif
            @if(auth()->user()->isAdmin())
                <a href="{{ route('leave.settings') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-gear me-2"></i>Settings
                </a>
            @endif
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-list-check fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-1">Leave Types</h3>
                    <p class="stat-number mb-0">{{ $leaveTypes->count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-1">Pending Requests</h3>
                    <p class="stat-number mb-0">{{ $pendingLeaves->count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-calendar-event fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-1">Upcoming Holidays</h3>
                    <p class="stat-number mb-0">{{ $upcomingHolidays->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- My Leave Requests -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>My Leave Requests</h5>
            <a href="{{ route('leave.history') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-clock-history me-1"></i>View History
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($myLeaves) && $myLeaves->count() > 0)
                            @foreach($myLeaves as $leave)
                            <tr>
                                <td>{{ $leave->leaveType->name ?? 'N/A' }}</td>
                                <td>{{ $leave->from_date }}</td>
                                <td>{{ $leave->to_date }}</td>
                                <td>{{ $leave->total_days }}</td>
                                <td>
                                    @if($leave->status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($leave->status == 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @elseif($leave->status == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($leave->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($leave->status == 'pending')
                                        <form action="{{ route('leave.update.status', $leave->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this leave request?')">
                                            @csrf
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No leave requests found</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Leave Balance -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Leave Balance</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @if(isset($leaveBalances) && $leaveBalances->count() > 0)
                    @foreach($leaveBalances as $balance)
                    <div class="col-md-4">
                        <div class="border rounded p-3 text-center">
                            <h6 class="fw-semibold mb-2">{{ $balance->leaveType->name ?? 'N/A' }}</h6>
                            <div class="mb-2">
                                <span class="badge bg-primary fs-6">{{ $balance->remaining_days }}</span>
                                <span class="text-muted">remaining</span>
                            </div>
                            <small class="text-muted">
                                Used: {{ $balance->used_days }} / Total: {{ $balance->total_days }}
                            </small>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="col-12 text-center py-4">
                        <p class="text-muted">No leave balance information available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
