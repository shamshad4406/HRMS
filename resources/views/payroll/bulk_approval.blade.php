@extends('layouts.app')

@section('title', 'Bulk Payroll Approval - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Bulk Payroll Approval</h1>
            <p class="text-muted mb-0">Review and approve pending payroll records</p>
        </div>
        <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Payroll List
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Pending Approvals</h5>
        </div>
        <div class="card-body">
            @if($pendingPayrolls->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Period</th>
                                <th>Basic Salary</th>
                                <th>Deductions</th>
                                <th>Net Salary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingPayrolls as $payroll)
                                <tr>
                                    <td>
                                        @if($payroll->personalInformation)
                                            <div>
                                                <strong>{{ $payroll->personalInformation->first_name }} {{ $payroll->personalInformation->last_name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $payroll->personalInformation->employee_code }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Unknown Employee</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($payroll->month) }} {{ $payroll->year }}</td>
                                    <td class="text-end">₹{{ number_format($payroll->basic_salary, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($payroll->deductions, 2) }}</td>
                                    <td class="text-end"><strong>₹{{ number_format($payroll->net_salary, 2) }}</strong></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('payrolls.review.deductions', $payroll->id) }}" class="btn btn-outline-primary">Review</a>
                                            <a href="{{ route('payrolls.approve', $payroll->id) }}" class="btn btn-outline-success">Approve</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-3">No pending payroll approvals.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
