@extends('layouts.app')

@section('title', 'Payslip - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Payslip</h1>
            <p class="text-muted mb-0">View and print employee payslip</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payrolls.history', $payroll->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to History
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer me-2"></i>Print Payslip
            </button>
        </div>
    </div>

    <div class="card" id="payslip-card">
        <div class="card-body">
            <div class="payslip-container">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
                    <div class="company-info">
                        <img src="{{ asset('images/innobles_logo.png') }}" alt="Innobles" class="mb-2" style="max-width: 200px; height: auto;">
                        <p class="text-muted mb-0">Integrated Employee Payroll Management System</p>
                    </div>
                    <div class="text-end">
                        <h4 class="mb-1">Payslip</h4>
                        <p class="text-muted mb-0">{{ ucfirst($payroll->month) }} {{ $payroll->year }}</p>
                        <p class="text-muted mb-0">Generated: {{ $payroll->generated_at ? $payroll->generated_at->format('Y-m-d') : '-' }}</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="mb-3 pb-2 border-bottom">Employee Information</h6>
                        @if($payroll->personalInformation)
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted d-block">Name</small>
                                    <span>{{ $payroll->personalInformation->first_name }} {{ $payroll->personalInformation->last_name }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Employee Code</small>
                                    <span>{{ $payroll->personalInformation->employee_code }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Department</small>
                                    <span>{{ $payroll->personalInformation->employmentDetail->department ?? '-' }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Designation</small>
                                    <span>{{ $payroll->personalInformation->employmentDetail->designation ?? '-' }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3 pb-2 border-bottom">Payroll Information</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="text-muted d-block">Pay Period</small>
                                <span>{{ ucfirst($payroll->month) }} {{ $payroll->year }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Pay Date</small>
                                <span>{{ $payroll->payment_date ? $payroll->payment_date->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Status</small>
                                <span>{{ ucfirst($payroll->status) }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Approved By</small>
                                <span>{{ $payroll->approvedBy ? $payroll->approvedBy->name : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="mb-3 pb-2 border-bottom">Earnings</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered no-stack">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Basic Salary</td>
                                    <td class="text-end">₹{{ number_format($payroll->basic_salary, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Allowances</td>
                                    <td class="text-end">₹{{ number_format($payroll->allowances, 2) }}</td>
                                </tr>
                                @if($payroll->reimbursementClaims->isNotEmpty())
                                    <tr>
                                        <td class="text-muted ps-4"><small>of which reimbursements</small></td>
                                        <td class="text-end text-muted"><small>₹{{ number_format($payroll->reimbursementClaims->sum('amount'), 2) }}</small></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="mb-3 pb-2 border-bottom">Deductions</h6>
                    @if($payroll->deductionDetails->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered no-stack">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payroll->deductionDetails as $deduction)
                                        <tr>
                                            <td>{{ ucfirst($deduction->deduction_type) }}</td>
                                            <td>{{ $deduction->description ?? '-' }}</td>
                                            <td class="text-end">₹{{ number_format($deduction->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No deductions applied.</p>
                    @endif
                </div>

                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span>Total Earnings</span>
                            <strong>₹{{ number_format($payroll->basic_salary + $payroll->allowances, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span>Total Deductions</span>
                            <strong>₹{{ number_format($payroll->deductions, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-top border-2">
                            <span class="fs-5">Net Salary</span>
                            <strong class="fs-4 text-primary">₹{{ number_format($payroll->net_salary, 2) }}</strong>
                        </div>
                    </div>
                </div>

                @if($payroll->payments->count() > 0)
                    <div class="card bg-info bg-opacity-10 mb-4">
                        <div class="card-body">
                            <h6 class="mb-3">Payment Information</h6>
                            @foreach($payroll->payments as $payment)
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Payment Method</small>
                                        <span>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Payment Reference</small>
                                        <span>{{ $payment->payment_reference ?? '-' }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Payment Date</small>
                                        <span>{{ $payment->payment_date->format('Y-m-d') }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Amount Paid</small>
                                        <span>₹{{ number_format($payment->amount, 2) }}</span>
                                    </div>
                                    @if($payment->notes)
                                        <div class="col-12">
                                            <small class="text-muted d-block">Notes</small>
                                            <span>{{ $payment->notes }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted mb-1">This is a computer-generated payslip. No signature required.</p>
                    <p class="text-muted mb-0">Generated by Innobles - Integrated Employee Payroll Management System</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .payslip-container {
            background: white;
            padding: 30px;
        }
        @media (max-width: 575.98px) { .payslip-container { padding: 4px; } }

        @media print {
            .sidebar,
            .top-header,
            .page-header,
            #payslip-card {
                box-shadow: none !important;
                border: none !important;
            }
            .sidebar,
            .top-header,
            .page-header {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
            }
            .page-wrapper {
                padding: 0 !important;
            }
            body {
                background: white !important;
            }
            #payslip-card {
                border-radius: 0 !important;
            }
        }
    </style>
@endsection
