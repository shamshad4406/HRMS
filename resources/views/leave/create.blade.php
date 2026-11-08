@extends('layouts.app')

@section('title', 'Apply for Leave - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Apply for Leave</h1>
            <p class="text-muted mb-0">Submit a leave request</p>
        </div>
        <a href="{{ route('leave.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Leave Management
        </a>
    </div>

    <!-- Leave Balance Overview -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-piggy-bank me-2"></i>Your Leave Balance</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @forelse($leaveBalances as $balance)
                <div class="col-md-4">
                    <div class="card h-100 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title">{{ $balance->leaveType->name }}</h6>
                            <div class="d-flex flex-column gap-1">
                                <small class="text-muted">Total: {{ $balance->total_days }}</small>
                                <small class="text-muted">Used: {{ $balance->used_days }}</small>
                                <strong class="text-success">Remaining: {{ $balance->remaining_days }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>No leave balance found. Please contact HR.
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Leave Application Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Leave Application Details</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('leave.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="leave_type_id" class="form-label">Leave Type *</label>
                        <select id="leave_type_id" name="leave_type_id" class="form-select" required>
                            <option value="">Select Leave Type</option>
                            @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}"
                                    data-paid="{{ $type->is_paid }}"
                                    data-approval="{{ $type->requires_approval }}"
                                    data-document="{{ $type->requires_document }}">
                                {{ $type->name }} ({{ $type->code }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="duration" class="form-label">Duration *</label>
                        <select id="duration" name="duration" class="form-select" required>
                            <option value="full_day">Full Day</option>
                            <option value="half_day">Half Day</option>
                            <option value="multiple_days">Multiple Days</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="from_date" class="form-label">From Date *</label>
                        <input type="date" id="from_date" name="from_date" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label for="to_date" class="form-label">To Date *</label>
                        <input type="date" id="to_date" name="to_date" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label for="reason" class="form-label">Reason for Leave *</label>
                        <textarea id="reason" name="reason" class="form-control" rows="4"
                                  placeholder="Please provide a detailed reason for your leave application..." required></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="attachment" class="form-label">Attachment (Medical Certificate if required)</label>
                        <input type="file" id="attachment" name="attachment" class="form-control"
                               accept=".pdf,.jpg,.jpeg,.png">
                        <small class="form-text text-muted">Accepted formats: PDF, JPG, JPEG, PNG (Max 2MB)</small>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <small class="text-muted d-block">Paid Leave:</small>
                                <strong id="paid-status">Yes</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <small class="text-muted d-block">Requires Approval:</small>
                                <strong id="approval-status">Yes</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <small class="text-muted d-block">Document Required:</small>
                                <strong id="document-status">No</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Submit Application
                    </button>
                    <a href="{{ route('leave.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const leaveTypeSelect = document.getElementById('leave_type_id');
        const paidStatus = document.getElementById('paid-status');
        const approvalStatus = document.getElementById('approval-status');
        const documentStatus = document.getElementById('document-status');
        const durationSelect = document.getElementById('duration');
        const toDateInput = document.getElementById('to_date');

        leaveTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const isPaid = selectedOption.dataset.paid === '1' ? 'Yes' : 'No';
            const requiresApproval = selectedOption.dataset.approval === '1' ? 'Yes' : 'No';
            const requiresDocument = selectedOption.dataset.document === '1' ? 'Yes' : 'No';

            paidStatus.textContent = isPaid;
            approvalStatus.textContent = requiresApproval;
            documentStatus.textContent = requiresDocument;

            // Update color based on status
            paidStatus.style.color = isPaid === 'Yes' ? '#10b981' : '#ef4444';
            documentStatus.style.color = requiresDocument === 'Yes' ? '#f59e0b' : '#64748b';
        });

        durationSelect.addEventListener('change', function() {
            if (this.value === 'half_day' || this.value === 'full_day') {
                toDateInput.value = document.getElementById('from_date').value;
                toDateInput.readOnly = true;
            } else {
                toDateInput.readOnly = false;
            }
        });

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('from_date').setAttribute('min', today);
        document.getElementById('to_date').setAttribute('min', today);
    });
</script>
@endpush
