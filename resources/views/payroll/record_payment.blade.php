@extends('layouts.app')

@section('title', 'Record Payment - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Record Payment</h1>
            <p class="text-muted mb-0">Record payroll payment for employee</p>
        </div>
        <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body bg-light">
            <div class="d-flex gap-2 flex-wrap">
                <div class="badge bg-success">1. Generate Salary</div>
                <div class="badge bg-success">2. Review Deductions</div>
                <div class="badge bg-success">3. Approve Payroll</div>
                <div class="badge bg-primary">4. Record Payment</div>
                <div class="badge bg-secondary">5. Generate Payslip</div>
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
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Payment Summary</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6 class="mb-2 text-uppercase tile-label">Net Salary Due</h6>
                            <h4 class="mb-0">₹{{ number_format($payroll->net_salary, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2 text-uppercase tile-label">Total Paid</h6>
                            <h4 class="mb-0">₹{{ number_format($payroll->payments->sum('amount'), 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2 text-uppercase tile-label">Remaining Balance</h6>
                            <h4 class="mb-0">₹{{ number_format($payroll->net_salary - $payroll->payments->sum('amount'), 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2 text-uppercase tile-label">Payment Status</h6>
                            <h4 class="mb-0" style="font-size: 18px;">{{ ucfirst($payroll->status) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Record New Payment</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('payrolls.record.payment', $payroll->id) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="">Select Method</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="payment_reference" class="form-label">Payment Reference</label>
                        <input type="text" name="payment_reference" id="payment_reference" class="form-control" placeholder="Transaction ID / Cheque Number">
                    </div>
                    <div class="col-md-6">
                        <label for="payment_date" class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" id="payment_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="amount" class="form-label">Amount (₹)</label>
                        <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0" required value="{{ $payroll->net_salary - $payroll->payments->sum('amount') }}">
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label for="notes" class="form-label">Notes (Optional)</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add any notes regarding this payment..."></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-cash me-2"></i>Record Payment</button>
                    <a href="{{ route('payrolls.history', $payroll->id) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
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
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payroll->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                    <td>{{ $payment->payment_reference ?? '-' }}</td>
                                    <td>₹{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->processedBy ? $payment->processedBy->name : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-3">No payments recorded yet.</p>
                </div>
            @endif
        </div>
    </div>

    @if($payroll->status === 'paid')
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Payment Complete</h5>
            </div>
            <div class="card-body">
                <p>This payroll has been fully paid. You can now generate the payslip.</p>
                <div class="d-flex gap-2">
                    <a href="{{ route('payrolls.payslip', $payroll->id) }}" class="btn btn-success"><i class="bi bi-file-earmark-text me-2"></i>Generate Payslip</a>
                    <a href="{{ route('payrolls.history', $payroll->id) }}" class="btn btn-outline-secondary">View Payment History</a>
                </div>
            </div>
        </div>
    @endif
@endsection
