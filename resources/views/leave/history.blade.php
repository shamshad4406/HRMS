@extends('layouts.app')

@section('title', 'Leave History - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Leave History</h1>
            <p class="text-muted mb-0">View your leave application history</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('leave.create') }}" class="btn btn-primary">
                <i class="bi bi-calendar-plus me-2"></i>Apply for Leave
            </a>
            <a href="{{ route('leave.balance') }}" class="btn btn-outline-secondary">
                <i class="bi bi-piggy-bank me-2"></i>Check Balance
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="display-6 text-primary"><i class="bi bi-list-check"></i></div>
                    <div>
                        <h6 class="mb-1 text-muted">Total Applications</h6>
                        <h4 class="mb-0">{{ $leaves->total() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="display-6 text-success"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <h6 class="mb-1 text-muted">Approved</h6>
                        <h4 class="mb-0">{{ $leaves->where('status', 'approved')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="display-6 text-warning"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <h6 class="mb-1 text-muted">Pending</h6>
                        <h4 class="mb-0">{{ $leaves->where('status', 'pending')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="display-6 text-danger"><i class="bi bi-x-circle"></i></div>
                    <div>
                        <h6 class="mb-1 text-muted">Rejected</h6>
                        <h4 class="mb-0">{{ $leaves->where('status', 'rejected')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave History Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Your Leave Applications</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Applied Date</th>
                            <th>Leave Type</th>
                            <th>Duration</th>
                            <th>Total Days</th>
                            <th>Status</th>
                            <th>Approver</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                        <tr>
                            <td>{{ $leave->created_at->format('M d, Y') }}</td>
                            <td>
                                <strong>{{ $leave->leaveType->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $leave->leaveType->code }}</small>
                            </td>
                            <td>
                                {{ $leave->from_date->format('M d, Y') }}
                                <br>
                                <small class="text-muted">to {{ $leave->to_date->format('M d, Y') }}</small>
                            </td>
                            <td><strong>{{ $leave->total_days }} days</strong></td>
                            <td>
                                @if($leave->status == 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($leave->status == 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @elseif($leave->status == 'cancelled')
                                    <span class="badge bg-secondary">Cancelled</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($leave->approvedBy)
                                    {{ $leave->approvedBy->name ?? 'Admin' }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($leave->rejection_reason)
                                    <span class="text-danger fst-italic">{{ $leave->rejection_reason }}</span>
                                @elseif($leave->remarks)
                                    {{ $leave->remarks }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
                                <p class="text-muted mb-2">No leave applications found.</p>
                                <a href="{{ route('leave.create') }}" class="btn btn-primary">
                                    <i class="bi bi-calendar-plus me-2"></i>Apply for Leave
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($leaves->hasPages())
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-end mb-0">
                        {{-- Previous Page Link --}}
                        @if($leaves->onFirstPage())
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $leaves->appends(request()->query())->previousPageUrl() }}">Previous</a>
                            </li>
                        @endif

                        {{-- Page Numbers --}}
                        @for($i = 1; $i <= $leaves->lastPage(); $i++)
                            @if($i == $leaves->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <a class="page-link" href="#">{{ $i }}</a>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $leaves->appends(request()->query())->url($i) }}">{{ $i }}</a>
                                </li>
                            @endif
                        @endfor

                        {{-- Next Page Link --}}
                        @if($leaves->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $leaves->appends(request()->query())->nextPageUrl() }}">Next</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Next</a>
                            </li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Recent Activity</h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-column gap-3">
                @foreach($leaves->take(5) as $leave)
                <div class="d-flex gap-3 p-3 bg-light rounded border-start border-4 border-primary">
                    <div class="fs-4">
                        @if($leave->status == 'approved')
                            <i class="bi bi-check-circle text-success"></i>
                        @elseif($leave->status == 'rejected')
                            <i class="bi bi-x-circle text-danger"></i>
                        @elseif($leave->status == 'cancelled')
                            <i class="bi bi-x-circle text-secondary"></i>
                        @else
                            <i class="bi bi-clock-history text-warning"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-1">
                            <strong>{{ $leave->leaveType->name }}</strong> application
                            @if($leave->status == 'approved')
                                <span class="text-success fw-bold">Approved</span>
                            @elseif($leave->status == 'rejected')
                                <span class="text-danger fw-bold">Rejected</span>
                            @elseif($leave->status == 'cancelled')
                                <span class="text-secondary fw-bold">Cancelled</span>
                            @else
                                <span class="text-warning fw-bold">Pending</span>
                            @endif
                        </p>
                        <p class="mb-1 text-muted small">
                            {{ $leave->from_date->format('M d, Y') }} - {{ $leave->to_date->format('M d, Y') }}
                            ({{ $leave->total_days }} days)
                        </p>
                        @if($leave->approved_at)
                        <p class="mb-0 text-muted small">Approved on {{ $leave->approved_at->format('M d, Y g:i A') }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
