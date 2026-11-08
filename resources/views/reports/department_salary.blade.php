@extends('layouts.app')

@section('title', 'Department-wise Salary Report - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1>Department-wise Salary Report</h1>
            <p class="text-muted mb-0">Department salary summary</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Reports
        </a>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.department.salary') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        <option value="January" {{ $month == 'January' ? 'selected' : '' }}>January</option>
                        <option value="February" {{ $month == 'February' ? 'selected' : '' }}>February</option>
                        <option value="March" {{ $month == 'March' ? 'selected' : '' }}>March</option>
                        <option value="April" {{ $month == 'April' ? 'selected' : '' }}>April</option>
                        <option value="May" {{ $month == 'May' ? 'selected' : '' }}>May</option>
                        <option value="June" {{ $month == 'June' ? 'selected' : '' }}>June</option>
                        <option value="July" {{ $month == 'July' ? 'selected' : '' }}>July</option>
                        <option value="August" {{ $month == 'August' ? 'selected' : '' }}>August</option>
                        <option value="September" {{ $month == 'September' ? 'selected' : '' }}>September</option>
                        <option value="October" {{ $month == 'October' ? 'selected' : '' }}>October</option>
                        <option value="November" {{ $month == 'November' ? 'selected' : '' }}>November</option>
                        <option value="December" {{ $month == 'December' ? 'selected' : '' }}>December</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        <option value="2024" {{ $year == '2024' ? 'selected' : '' }}>2024</option>
                        <option value="2025" {{ $year == '2025' ? 'selected' : '' }}>2025</option>
                        <option value="2026" {{ $year == '2026' ? 'selected' : '' }}>2026</option>
                        <option value="2027" {{ $year == '2027' ? 'selected' : '' }}>2027</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-2"></i>Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Department Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Department Salary Summary - {{ $month }} {{ $year }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Department Name</th>
                            <th>Employee Count</th>
                            <th>Total Salary Paid</th>
                            <th>Average Salary</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departmentData as $data)
                            <tr>
                                <td><strong>{{ $data['department'] }}</strong></td>
                                <td>{{ $data['employee_count'] }}</td>
                                <td>₹{{ number_format($data['total_salary'], 2) }}</td>
                                <td>₹{{ number_format($data['average_salary'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">No department data found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Overall Summary -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Departments</h6>
                    <h3 class="mb-0">{{ count($departmentData) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Employees</h6>
                    <h3 class="mb-0">{{ collect($departmentData)->sum('employee_count') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Grand Total Salary</h6>
                    <h3 class="mb-0 text-success">₹{{ number_format(collect($departmentData)->sum('total_salary'), 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
@endsection
