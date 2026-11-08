@extends('layouts.app')

@section('title', 'All Reimbursements - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">All Reimbursements</h1>
            <p class="text-muted mb-0">View and manage all reimbursement claims</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reimbursements.approvals') }}" class="btn btn-warning">
                <i class="bi bi-clock me-2"></i>Pending Approvals
            </a>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('reimbursements.reports') }}" class="btn btn-outline-primary">
                    <i class="bi bi-bar-chart me-2"></i>Reports
                </a>
                <a href="{{ route('reimbursements.settings') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-gear me-2"></i>Settings
                </a>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Claim ID</th>
                            <th>Employee</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Expense Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claims as $claim)
                            <tr>
                                <td>#{{ $claim->id }}</td>
                                <td>
                                    <div>{{ $claim->personalInformation->first_name }} {{ $claim->personalInformation->last_name }}</div>
                                    <small class="text-muted">{{ $claim->personalInformation->employee_code }}</small>
                                </td>
                                <td>{{ $claim->category->name }}</td>
                                <td>₹{{ number_format($claim->amount, 2) }}</td>
                                <td>{{ $claim->expense_date->format('Y-m-d') }}</td>
                                <td>
                                    @if($claim->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($claim->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($claim->status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @elseif($claim->status === 'paid')
                                        <span class="badge bg-primary">Paid</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('reimbursements.show', $claim->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    @if($claim->status === 'approved' && !$claim->payroll_id)
                                        <a href="{{ route('reimbursements.payment', $claim->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-cash"></i> Pay
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No reimbursement claims found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($claims->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $claims->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
