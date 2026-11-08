@extends('layouts.app')

@section('title', 'Reimbursement Approvals - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Reimbursement Approvals</h1>
            <p class="text-muted mb-0">Review and approve pending reimbursement claims</p>
        </div>
        <a href="{{ route('reimbursements.all') }}" class="btn btn-outline-secondary">
            <i class="bi bi-list me-2"></i>View All Claims
        </a>
    </div>

    @if($pendingClaims->count() > 0)
        <form id="bulkForm" method="POST" action="{{ route('reimbursements.bulk.process') }}" class="card card-body mb-3">
            @csrf
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <div class="form-check me-2">
                    <input type="checkbox" class="form-check-input" id="selectAllClaims">
                    <label class="form-check-label" for="selectAllClaims">Select all ({{ $pendingClaims->count() }})</label>
                </div>
                <input type="text" name="rejection_reason" class="form-control" style="max-width: 320px;" maxlength="500" placeholder="Reason (needed only when rejecting)">
                <button type="submit" name="action" value="approve" class="btn btn-success">
                    <i class="bi bi-check-lg me-1"></i>Approve selected
                </button>
                <button type="submit" name="action" value="reject" class="btn btn-danger" onclick="return confirm('Reject all selected claims?');">
                    <i class="bi bi-x-lg me-1"></i>Reject selected
                </button>
            </div>
            @if($errors->any())
                <div class="text-danger small mt-2">{{ $errors->first() }}</div>
            @endif
        </form>
    @endif

    @forelse($pendingClaims as $claim)
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="card-title">
                            <input type="checkbox" class="form-check-input me-2 claim-check" name="claim_ids[]" value="{{ $claim->id }}" form="bulkForm">{{ $claim->personalInformation->first_name }} {{ $claim->personalInformation->last_name }}
                        </h5>
                        <p class="text-muted mb-2">{{ $claim->personalInformation->employee_code }}</p>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Category</small>
                                <span>{{ $claim->category->name }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Amount</small>
                                <span class="fw-bold">₹{{ number_format($claim->amount, 2) }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Expense Date</small>
                                <span>{{ $claim->expense_date->format('Y-m-d') }}</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Description</small>
                            <span>{{ $claim->description }}</span>
                        </div>
                        <div class="d-flex gap-2 flex-wrap mt-3">
                            @if($claim->receipt_path)
                                <a href="{{ route('reimbursements.receipt', $claim->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark me-1"></i>View Receipt
                                </a>
                            @endif
                            <form action="{{ route('reimbursements.approve', $claim->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-check-lg me-1"></i>Approve
                                </button>
                            </form>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $claim->id }}">
                                <i class="bi bi-x-lg me-1"></i>Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal{{ $claim->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Claim</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('reimbursements.approve', $claim->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="reject">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Rejection Reason *</label>
                                <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Please provide a reason for rejection..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Reject Claim</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-check-circle fs-1 mb-3"></i>
                <h4>No Pending Claims</h4>
                <p>All reimbursement claims have been processed.</p>
            </div>
        </div>
    @endforelse

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const all = document.getElementById('selectAllClaims');
        if (!all) return;
        all.addEventListener('change', function () {
            document.querySelectorAll('.claim-check').forEach(function (box) { box.checked = all.checked; });
        });
    });
    </script>
@endsection
