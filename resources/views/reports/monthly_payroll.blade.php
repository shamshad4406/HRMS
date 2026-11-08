@extends('layouts.app')

@section('title', 'Monthly Payroll Report - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1>Monthly Payroll Report</h1>
            <p class="text-muted mb-0">Employee-wise salary details</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Reports
        </a>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.monthly.payroll') }}" class="row g-3 align-items-end">
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

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Gross Salary</h6>
                    <h3 class="mb-0">₹{{ number_format($totalGrossSalary, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Deductions</h6>
                    <h3 class="mb-0 text-danger">₹{{ number_format($totalDeductions, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Net Salary</h6>
                    <h3 class="mb-0 text-success">₹{{ number_format($totalNetSalary, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Employee-wise Salary Details - {{ $month }} {{ $year }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>Basic Salary</th>
                            <th>Allowances</th>
                            <th>Gross Salary</th>
                            <th>Deductions</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $payroll)
                            <tr>
                                <td>{{ $payroll->personalInformation->first_name }} {{ $payroll->personalInformation->last_name }}</td>
                                <td>{{ $payroll->personalInformation->employmentDetail->department ?? 'N/A' }}</td>
                                <td>₹{{ number_format($payroll->basic_salary, 2) }}</td>
                                <td>₹{{ number_format($payroll->allowances, 2) }}</td>
                                <td>₹{{ number_format($payroll->basic_salary + $payroll->allowances, 2) }}</td>
                                <td>₹{{ number_format($payroll->deductions, 2) }}</td>
                                <td><strong>₹{{ number_format($payroll->net_salary, 2) }}</strong></td>
                                <td>
                                    @if($payroll->status == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($payroll->status == 'processed')
                                        <span class="badge bg-info">Processed</span>
                                    @elseif($payroll->status == 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($payroll->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">No payroll records found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
