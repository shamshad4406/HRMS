@extends('layouts.app')

@section('title', 'Leave Approvals - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Leave Approvals</h1>
            <p class="text-muted mb-0">Review and approve pending leave requests</p>
        </div>
        <a href="{{ route('leave.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Leave Management
        </a>
    </div>

    <!-- Pending Approvals -->
    <div class="d-flex flex-column gap-3">
        @forelse($pendingLeaves as $leave)
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">{{ $leave->personalInformation->first_name }} {{ $leave->personalInformation->last_name }}</h5>
                    <small class="text-muted">ID: {{ $leave->personalInformation->employee_code ?? 'N/A' }}</small>
                </div>
                <span class="badge bg-warning text-dark">Pending</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Leave Type:</small>
                        <strong>{{ $leave->leaveType->name }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Duration:</small>
                        <strong>{{ $leave->from_date->format('M d, Y') }} - {{ $leave->to_date->format('M d, Y') }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Total Days:</small>
                        <strong>{{ $leave->total_days }} days</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Applied On:</small>
                        <strong>{{ $leave->created_at->format('M d, Y g:i A') }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Paid Leave:</small>
                        <strong>{{ $leave->is_paid ? 'Yes' : 'No' }}</strong>
                    </div>
                </div>

                <div class="bg-light p-3 rounded mb-3">
                    <small class="text-muted d-block">Reason:</small>
                    <p class="mb-0">{{ $leave->reason }}</p>
                </div>

                @if($leave->attachment)
                <div class="mb-3">
                    <small class="text-muted d-block">Attachment:</small>
                    <a href="{{ route('leave.attachment', $leave->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark me-1"></i>View Document
                    </a>
                </div>
                @endif

                <form method="POST" action="{{ route('leave.update.status', $leave->id) }}">
                    @csrf
                    <input type="hidden" name="status" id="status-{{ $leave->id }}">

                    <div class="mb-3" id="rejection-section-{{ $leave->id }}" style="display: none;">
                        <label for="rejection_reason-{{ $leave->id }}" class="form-label">Rejection Reason:</label>
                        <textarea id="rejection_reason-{{ $leave->id }}" name="rejection_reason"
                                  class="form-control" rows="3"
                                  placeholder="Please provide a reason for rejection..."></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" onclick="approveLeave({{ $leave->id }}, this)"
                                class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>Approve
                        </button>
                        <button type="button" onclick="showRejection({{ $leave->id }})"
                                class="btn btn-danger">
                            <i class="bi bi-x-circle me-1"></i>Reject
                        </button>
                        <button type="submit" id="submit-reject-{{ $leave->id }}"
                                class="btn btn-danger" style="display: none;">
                            <i class="bi bi-exclamation-circle me-1"></i>Confirm Rejection
                        </button>
                        <button type="button" onclick="cancelRejection({{ $leave->id }})"
                                class="btn btn-outline-secondary" id="cancel-reject-{{ $leave->id }}" style="display: none;">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <div class="card text-center py-5">
            <div class="card-body">
                <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                <h5 class="mb-2">No Pending Approvals</h5>
                <p class="text-muted mb-3">All leave requests have been processed.</p>
                <a href="{{ route('leave.index') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
        @endforelse
    </div>
@endsection

@push('scripts')
<script>
    function approveLeave(leaveId, button) {
        document.getElementById('status-' + leaveId).value = 'approved';
        button.closest('form').submit();
    }

    function showRejection(leaveId) {
        document.getElementById('rejection-section-' + leaveId).style.display = 'block';
        document.getElementById('submit-reject-' + leaveId).style.display = 'inline-block';
        document.getElementById('cancel-reject-' + leaveId).style.display = 'inline-block';
    }

    function cancelRejection(leaveId) {
        document.getElementById('rejection-section-' + leaveId).style.display = 'none';
        document.getElementById('submit-reject-' + leaveId).style.display = 'none';
        document.getElementById('cancel-reject-' + leaveId).style.display = 'none';
        document.getElementById('rejection_reason-' + leaveId).value = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const rejectButtons = document.querySelectorAll('.btn-danger[type="submit"]');
        rejectButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const leaveId = this.id.replace('submit-reject-', '');
                const rejectionReason = document.getElementById('rejection_reason-' + leaveId).value;

                if (!rejectionReason.trim()) {
                    e.preventDefault();
                    alert('Please provide a rejection reason.');
                    document.getElementById('rejection_reason-' + leaveId).focus();
                } else {
                    document.getElementById('status-' + leaveId).value = 'rejected';
                }
            });
        });
    });
</script>
@endpush
