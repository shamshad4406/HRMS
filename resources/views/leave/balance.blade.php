@extends('layouts.app')

@section('title', 'Leave Balance - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Leave Balance</h1>
            <p class="text-muted mb-0">View your leave balance for the year</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('leave.create') }}" class="btn btn-primary">
                <i class="bi bi-calendar-plus me-2"></i>Apply for Leave
            </a>
            <a href="{{ route('leave.history') }}" class="btn btn-outline-secondary">
                <i class="bi bi-clock-history me-2"></i>Leave History
            </a>
        </div>
    </div>

    <!-- Year Selector -->
    <div class="card mb-4">
        <div class="card-body d-flex align-items-center gap-3">
            <label for="year" class="form-label mb-0 fw-semibold">Year:</label>
            <select id="year" class="form-select" style="width: auto;" onchange="changeYear(this.value)">
                <option value="2024" {{ $year == '2024' ? 'selected' : '' }}>2024</option>
                <option value="2025" {{ $year == '2025' ? 'selected' : '' }}>2025</option>
                <option value="2026" {{ $year == '2026' ? 'selected' : '' }}>2026</option>
                <option value="2027" {{ $year == '2027' ? 'selected' : '' }}>2027</option>
            </select>
        </div>
    </div>

    <!-- Leave Balance Cards -->
    <div class="row g-3 mb-4">
        @forelse($leaveBalances as $balance)
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-6 mb-2">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <h5 class="card-title">{{ $balance->leaveType->name }}</h5>
                        <span class="badge bg-light text-dark">{{ $balance->leaveType->code }}</span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-4 text-center">
                            <small class="text-muted d-block">Total</small>
                            <strong>{{ $balance->total_days }}</strong>
                        </div>
                        <div class="col-4 text-center">
                            <small class="text-muted d-block">Used</small>
                            <strong class="text-danger">{{ $balance->used_days }}</strong>
                        </div>
                        <div class="col-4 text-center">
                            <small class="text-muted d-block">Carried</small>
                            <strong>{{ $balance->carried_forward_days }}</strong>
                        </div>
                    </div>

                    <div class="bg-success bg-opacity-10 p-3 rounded text-center mb-3">
                        <small class="text-success d-block">Remaining Balance</small>
                        <h3 class="text-success mb-0">{{ $balance->remaining_days }}</h3>
                    </div>

                    <div class="mb-3">
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ $balance->total_days > 0 ? ($balance->used_days / $balance->total_days) * 100 : 0 }}%"></div>
                        </div>
                        <small class="text-muted d-block text-end mt-1">
                            {{ $balance->total_days > 0 ? round(($balance->used_days / $balance->total_days) * 100) : 0 }}% used
                        </small>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('leave.create') }}?leave_type={{ $balance->leaveType->id }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-calendar-plus me-1"></i>Apply Leave
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card text-center py-5">
                <div class="card-body">
                    <i class="bi bi-balance-scale fs-1 text-muted mb-3"></i>
                    <h5 class="mb-2">No Leave Balance Found</h5>
                    <p class="text-muted mb-0">Your leave balance for this year has not been initialized yet. Please contact HR for assistance.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Leave Policy Info -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Leave Policy Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border-start border-4 border-primary">
                        <h6 class="fw-bold">Carry Forward Policy</h6>
                        <p class="mb-0 text-muted small">Unused leaves can be carried forward to the next year as per company policy.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border-start border-4 border-primary">
                        <h6 class="fw-bold">Leave Encashment</h6>
                        <p class="mb-0 text-muted small">Some leave types are eligible for encashment at the end of the year.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border-start border-4 border-primary">
                        <h6 class="fw-bold">Document Requirements</h6>
                        <p class="mb-0 text-muted small">Medical certificates may be required for sick leaves exceeding 3 days.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border-start border-4 border-primary">
                        <h6 class="fw-bold">Approval Process</h6>
                        <p class="mb-0 text-muted small">All leave requests require manager approval except in emergency situations.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function changeYear(year) {
        // In a real application, this would reload the page with the selected year
        window.location.href = `{{ route('leave.balance') }}?year=${year}`;
    }
</script>
@endpush
