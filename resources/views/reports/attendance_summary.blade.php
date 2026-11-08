@extends('layouts.app')

@section('title', 'Attendance Summary Report - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1>Attendance Summary Report</h1>
            <p class="text-muted mb-0">Employee attendance overview</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Reports
        </a>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.attendance.summary') }}" class="row g-3 align-items-end">
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

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Present Days</h6>
                    <h3 class="mb-0 text-success">{{ $summary['present_days'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-danger">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Absent Days</h6>
                    <h3 class="mb-0 text-danger">{{ $summary['absent_days'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-warning">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Half Days</h6>
                    <h3 class="mb-0 text-warning">{{ $summary['half_days'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-info">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Late Marks</h6>
                    <h3 class="mb-0 text-info">{{ $summary['late_marks'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-primary">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Overtime</h6>
                    <h3 class="mb-0 text-primary">{{ $summary['overtime'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee-wise Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Employee-wise Attendance Summary</h5>
            <small class="text-muted">Period: {{ $startDate }} to {{ $endDate }}</small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Employee Name</th>
                            <th>Present Days</th>
                            <th>Absent Days</th>
                            <th>Half Days</th>
                            <th>Late Marks</th>
                            <th>Overtime</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employeeWiseSummary as $employee)
                            <tr>
                                <td><strong>{{ $employee['employee_name'] }}</strong></td>
                                <td><span class="badge bg-success">{{ $employee['present_days'] }}</span></td>
                                <td><span class="badge bg-danger">{{ $employee['absent_days'] }}</span></td>
                                <td><span class="badge bg-warning text-dark">{{ $employee['half_days'] }}</span></td>
                                <td><span class="badge bg-info">{{ $employee['late_marks'] }}</span></td>
                                <td><span class="badge bg-primary">{{ $employee['overtime'] }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No attendance records found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
