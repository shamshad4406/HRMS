@extends('layouts.app')

@section('title', 'Leave Report - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1>Leave Report</h1>
            <p class="text-muted mb-0">Employee leave summary</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Reports
        </a>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.leave') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-2"></i>Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Leave Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Applied Leaves</h6>
                    <h3 class="mb-0">{{ collect($employeeLeaveSummary)->sum('total_leaves') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Approved Leaves</h6>
                    <h3 class="mb-0 text-success">{{ collect($employeeLeaveSummary)->sum('approved_leaves') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Rejected Leaves</h6>
                    <h3 class="mb-0 text-danger">{{ collect($employeeLeaveSummary)->sum('rejected_leaves') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Leave Balance</h6>
                    <h3 class="mb-0 text-warning">{{ collect($employeeLeaveSummary)->sum('leave_balance') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee-wise Leave Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Employee-wise Leave Summary</h5>
            <small class="text-muted">Period: {{ $startDate }} to {{ $endDate }}</small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Employee Name</th>
                            <th>Total Leaves</th>
                            <th>Approved Leaves</th>
                            <th>Rejected Leaves</th>
                            <th>Leave Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employeeLeaveSummary as $employee)
                            <tr>
                                <td><strong>{{ $employee['employee_name'] }}</strong></td>
                                <td><span class="badge bg-info">{{ $employee['total_leaves'] }}</span></td>
                                <td><span class="badge bg-success">{{ $employee['approved_leaves'] }}</span></td>
                                <td><span class="badge bg-danger">{{ $employee['rejected_leaves'] }}</span></td>
                                <td><span class="badge bg-warning text-dark">{{ $employee['leave_balance'] }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No leave records found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Leave Type Breakdown -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Leave Type Breakdown</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Leave Type</th>
                            <th>Applied</th>
                            <th>Approved</th>
                            <th>Rejected</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Sick Leave</strong></td>
                            <td>{{ $leaveSummary['sick_leave']['applied'] ?? 0 }}</td>
                            <td>{{ $leaveSummary['sick_leave']['approved'] ?? 0 }}</td>
                            <td>{{ $leaveSummary['sick_leave']['rejected'] ?? 0 }}</td>
                            <td>{{ $leaveSummary['sick_leave']['balance'] ?? 12 }}</td>
                        </tr>
                        <tr>
                            <td><strong>Casual Leave</strong></td>
                            <td>{{ $leaveSummary['casual_leave']['applied'] ?? 0 }}</td>
                            <td>{{ $leaveSummary['casual_leave']['approved'] ?? 0 }}</td>
                            <td>{{ $leaveSummary['casual_leave']['rejected'] ?? 0 }}</td>
                            <td>{{ $leaveSummary['casual_leave']['balance'] ?? 10 }}</td>
                        </tr>
                        <tr>
                            <td><strong>Earned Leave</strong></td>
                            <td>{{ $leaveSummary['earned_leave']['applied'] ?? 0 }}</td>
                            <td>{{ $leaveSummary['earned_leave']['approved'] ?? 0 }}</td>
                            <td>{{ $leaveSummary['earned_leave']['rejected'] ?? 0 }}</td>
                            <td>{{ $leaveSummary['earned_leave']['balance'] ?? 15 }}</td>
                        </tr>
                        <tr>
                            <td><strong>Compensatory Leave</strong></td>
                            <td>{{ $leaveSummary['compensatory_leave']['applied'] ?? 0 }}</td>
                            <td>{{ $leaveSummary['compensatory_leave']['approved'] ?? 0 }}</td>
                            <td>{{ $leaveSummary['compensatory_leave']['rejected'] ?? 0 }}</td>
                            <td>{{ $leaveSummary['compensatory_leave']['balance'] ?? 5 }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
