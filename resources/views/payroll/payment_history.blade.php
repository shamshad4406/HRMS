@extends('layouts.app')

@section('title', 'Payment History - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Payment History</h1>
            <p class="text-muted mb-0">View complete payment history for payroll</p>
        </div>
        <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Payroll List
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body bg-light">
            <div class="d-flex gap-2 flex-wrap">
                <div class="badge bg-success">1. Generate Salary</div>
                <div class="badge bg-success">2. Review Deductions</div>
                <div class="badge bg-success">3. Approve Payroll</div>
                <div class="badge bg-success">4. Record Payment</div>
                <div class="badge bg-success">5. Generate Payslip</div>
                <div class="badge bg-primary">6. View History</div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-person me-2"></i>Employee Information</h5>
        </div>
        <div class="card-body">
            @if($payroll->personalInformation)
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Name:</strong> {{ $payroll->personalInformation->first_name }} {{ $payroll->personalInformation->last_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Employee Code:</strong> {{ $payroll->personalInformation->employee_code }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Period:</strong> {{ ucfirst($payroll->month) }} {{ $payroll->year }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Status:</strong>
                            @php
                                $statusClass = match($payroll->status) {
                                    'pending' => 'warning',
                                    'processed' => 'info',
                                    'paid' => 'success',
                                    'rejected' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst($payroll->status) }}</span>
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Salary Summary</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2 text-uppercase tile-label">Basic Salary</h6>
                            <h4 class="mb-0">₹{{ number_format($payroll->basic_salary, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2 text-uppercase tile-label">Allowances</h6>
                            <h4 class="mb-0">₹{{ number_format($payroll->allowances, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2 text-uppercase tile-label">Total Deductions</h6>
                            <h4 class="mb-0">₹{{ number_format($payroll->deductions, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6 class="mb-2 text-uppercase tile-label">Net Salary</h6>
                            <h4 class="mb-0">₹{{ number_format($payroll->net_salary, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Deduction Details</h5>
        </div>
        <div class="card-body">
            @if($payroll->deductionDetails->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payroll->deductionDetails as $deduction)
                                <tr>
                                    <td>{{ ucfirst($deduction->deduction_type) }}</td>
                                    <td>{{ $deduction->description ?? '-' }}</td>
                                    <td class="text-end">₹{{ number_format($deduction->amount, 2) }}</td>
                                    <td>{{ ucfirst($deduction->status) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-3">No deductions applied.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Payment History</h5>
        </div>
        <div class="card-body">
            @if($payroll->payments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Processed By</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payroll->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                    <td>{{ $payment->payment_reference ?? '-' }}</td>
                                    <td class="text-end">₹{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->processedBy ? $payment->processedBy->name : '-' }}</td>
                                    <td>{{ $payment->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-end">
                    <h5>Total Paid: ₹{{ number_format($payroll->payments->sum('amount'), 2) }}</h5>
                    <h5 class="text-muted">Remaining Balance: ₹{{ number_format($payroll->net_salary - $payroll->payments->sum('amount'), 2) }}</h5>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-3">No payments recorded yet.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex gap-2">
                @if($payroll->status !== 'paid')
                    <a href="{{ route('payrolls.record.payment', $payroll->id) }}" class="btn btn-primary">
                        <i class="bi bi-cash me-2"></i>Record Payment
                    </a>
                @endif
                @if($payroll->status === 'paid')
                    <a href="{{ route('payrolls.payslip', $payroll->id) }}" class="btn btn-success">
                        <i class="bi bi-file-earmark-text me-2"></i>Generate Payslip
                    </a>
                @endif
                <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary">Back to Payroll List</a>
            </div>
        </div>
    </div>
@endsection
