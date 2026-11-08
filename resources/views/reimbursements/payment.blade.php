@extends('layouts.app')

@section('title', 'Process Payment - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Process Payment</h1>
            <p class="text-muted mb-0">Record payment for approved reimbursement</p>
        </div>
        <a href="{{ route('reimbursements.all') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to All Claims
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Claim Information</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Claim ID</label>
                            <div class="fw-bold">#{{ $claim->id }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Claim Date</label>
                            <div>{{ $claim->claim_date->format('Y-m-d') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Category</label>
                            <div>{{ $claim->category->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Expense Date</label>
                            <div>{{ $claim->expense_date->format('Y-m-d') }}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="text-muted small">Amount to Pay</label>
                            <div class="fw-bold fs-4 text-primary">₹{{ number_format($claim->amount, 2) }}</div>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small">Description</label>
                            <div>{{ $claim->description }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Payment Details</h5>
                    <form action="{{ route('reimbursements.record.payment', $claim->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Payment Method *</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="">Select Payment Method</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Date *</label>
                            <input type="date" name="payment_date" class="form-control" required max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Reference</label>
                            <input type="text" name="payment_reference" class="form-control" placeholder="Transaction ID, Cheque Number, etc.">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Any additional notes..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Record Payment
                            </button>
                            <a href="{{ route('reimbursements.all') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Employee Information</h5>
                    <div class="g-3">
                        <div class="mb-2">
                            <label class="text-muted small">Name</label>
                            <div>{{ $claim->personalInformation->first_name }} {{ $claim->personalInformation->last_name }}</div>
                        </div>
                        <div class="mb-2">
                            <label class="text-muted small">Employee Code</label>
                            <div>{{ $claim->personalInformation->employee_code }}</div>
                        </div>
                        @if($claim->personalInformation->employmentDetail)
                            <div class="mb-2">
                                <label class="text-muted small">Department</label>
                                <div>{{ $claim->personalInformation->employmentDetail->department ?? '-' }}</div>
                            </div>
                            <div class="mb-2">
                                <label class="text-muted small">Designation</label>
                                <div>{{ $claim->personalInformation->employmentDetail->designation ?? '-' }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($claim->receipt_path)
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Receipt</h5>
                        <a href="{{ route('reimbursements.receipt', $claim->id) }}" target="_blank" class="btn btn-outline-primary w-100">
                            <i class="bi bi-file-earmark me-2"></i>View Receipt
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
