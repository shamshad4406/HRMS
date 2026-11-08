@extends('layouts.app')

@section('title', 'Reimbursement Details - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Reimbursement Details</h1>
            <p class="text-muted mb-0">View claim details and status</p>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('reimbursements.all') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to All Claims
                </a>
            @else
                <a href="{{ route('reimbursements.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to My Claims
                </a>
            @endif
        </div>
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
                            <div>{{ $claim->claim_date->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Category</label>
                            <div>{{ $claim->category->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Expense Date</label>
                            <div>{{ $claim->expense_date->format('Y-m-d') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Amount</label>
                            <div class="fw-bold fs-5 text-primary">₹{{ number_format($claim->amount, 2) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Status</label>
                            <div>
                                @if($claim->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($claim->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($claim->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @elseif($claim->status === 'paid')
                                    <span class="badge bg-primary">Paid</span>
                                @endif
                            </div>
                        </div>
                        @if($claim->items->isNotEmpty())
                            <div class="col-12">
                                <label class="text-muted small">Line items</label>
                                <table class="table table-sm mb-0">
                                    <thead><tr><th>Item</th><th class="text-end">Qty</th><th class="text-end">Unit price</th><th class="text-end">Total</th></tr></thead>
                                    <tbody>
                                        @foreach($claim->items as $item)
                                            <tr>
                                                <td>{{ $item->item_name }}</td>
                                                <td class="text-end">{{ $item->quantity }}</td>
                                                <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                                                <td class="text-end">₹{{ number_format($item->total_amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        <div class="col-12">
                            <label class="text-muted small">Description</label>
                            <div>{{ $claim->description }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($claim->receipt_path)
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Receipt</h5>
                        <a href="{{ route('reimbursements.receipt', $claim->id) }}" target="_blank" class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark me-2"></i>View Receipt
                        </a>
                    </div>
                </div>
            @endif

            @if($claim->rejection_reason)
                <div class="card bg-danger bg-opacity-10 mb-4">
                    <div class="card-body">
                        <h5 class="card-title text-danger">Rejection Reason</h5>
                        <p class="mb-0">{{ $claim->rejection_reason }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
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
                    </div>
                </div>
            </div>

            @if($claim->approved_by)
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Approval Information</h5>
                        <div class="g-3">
                            <div class="mb-2">
                                <label class="text-muted small">Approved By</label>
                                <div>{{ $claim->approvedBy->name }}</div>
                            </div>
                            <div class="mb-2">
                                <label class="text-muted small">Approved At</label>
                                <div>{{ $claim->approved_at->format('Y-m-d H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($claim->payroll_id)
                <div class="alert alert-info">Included in the {{ ucfirst($claim->payroll->month) }} {{ $claim->payroll->year }} payroll - it is paid with the salary.</div>
            @endif

            @if($claim->status === 'approved' && !$claim->payroll_id && auth()->user()->role === 'admin')
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Actions</h5>
                        <a href="{{ route('reimbursements.payment', $claim->id) }}" class="btn btn-primary w-100">
                            <i class="bi bi-cash me-2"></i>Process Payment
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($claim->status === 'paid')
        <div class="card bg-success bg-opacity-10">
            <div class="card-body">
                <h5 class="card-title text-success">Payment Information</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="text-muted small">Payment Method</label>
                        <div>{{ ucfirst(str_replace('_', ' ', $claim->payment_method)) }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Payment Date</label>
                        <div>{{ $claim->payment_date->format('Y-m-d') }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Payment Reference</label>
                        <div>{{ $claim->payment_reference ?? '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Processed By</label>
                        <div>{{ $claim->processedBy->name }}</div>
                    </div>
                    @if($claim->remarks)
                        <div class="col-12">
                            <label class="text-muted small">Remarks</label>
                            <div>{{ $claim->remarks }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endsection
