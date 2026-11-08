@extends('layouts.app')

@section('title', 'Reports - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header">
        <h1>Reports</h1>
        <p class="text-muted">Generate and view various reports</p>
    </div>

    <!-- Reports Grid -->
    <div class="row g-4">
        <!-- Monthly Payroll Report -->
        <div class="col-xl-4 col-md-6">
            <a href="{{ route('reports.monthly.payroll') }}" class="report-card">
                <div class="report-icon"><i class="bi bi-cash-coin"></i></div>
                <div class="report-info">
                    <h3>Monthly Payroll Report</h3>
                    <p>Employee-wise salary, gross salary, deductions, and net salary</p>
                </div>
                <div class="report-arrow"><i class="bi bi-arrow-right"></i></div>
            </a>
        </div>

        <!-- Department-wise Salary Report -->
        <div class="col-xl-4 col-md-6">
            <a href="{{ route('reports.department.salary') }}" class="report-card">
                <div class="report-icon"><i class="bi bi-building"></i></div>
                <div class="report-info">
                    <h3>Department-wise Salary Report</h3>
                    <p>Department name, employee count, total salary, and average salary</p>
                </div>
                <div class="report-arrow"><i class="bi bi-arrow-right"></i></div>
            </a>
        </div>

        <!-- Attendance Summary Report -->
        <div class="col-xl-4 col-md-6">
            <a href="{{ route('reports.attendance.summary') }}" class="report-card">
                <div class="report-icon"><i class="bi bi-calendar-check"></i></div>
                <div class="report-info">
                    <h3>Attendance Summary Report</h3>
                    <p>Present days, absent days, half days, late marks, and overtime</p>
                </div>
                <div class="report-arrow"><i class="bi bi-arrow-right"></i></div>
            </a>
        </div>

        <!-- Leave Report -->
        <div class="col-xl-4 col-md-6">
            <a href="{{ route('reports.leave') }}" class="report-card">
                <div class="report-icon"><i class="bi bi-calendar3"></i></div>
                <div class="report-info">
                    <h3>Leave Report</h3>
                    <p>Leave type, applied leaves, approved leaves, rejected leaves, and balance</p>
                </div>
                <div class="report-arrow"><i class="bi bi-arrow-right"></i></div>
            </a>
        </div>

        <!-- Payment History Report -->
        <div class="col-xl-4 col-md-6">
            <a href="{{ route('reports.payment.history') }}" class="report-card">
                <div class="report-icon"><i class="bi bi-receipt"></i></div>
                <div class="report-info">
                    <h3>Payment History Report</h3>
                    <p>Payment date, mode, transaction reference, amount, and status</p>
                </div>
                <div class="report-arrow"><i class="bi bi-arrow-right"></i></div>
            </a>
        </div>
    </div>
@endsection

@push('styles')
@endpush
