@extends('layouts.app')

@section('title', 'My Reimbursements - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">My Reimbursements</h1>
            <p class="text-muted mb-0">View and manage your reimbursement claims</p>
        </div>
        <a href="{{ route('reimbursements.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>New Claim
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Claim Date</th>
                            <th>Category</th>
                            <th>Expense Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claims as $claim)
                            <tr>
                                <td>{{ $claim->claim_date->format('Y-m-d') }}</td>
                                <td>{{ $claim->category->name }}</td>
                                <td>{{ $claim->expense_date->format('Y-m-d') }}</td>
                                <td>₹{{ number_format($claim->amount, 2) }}</td>
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
                                    @if($claim->status === 'pending')
                                        <form action="{{ route('reimbursements.destroy', $claim->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this claim?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No reimbursement claims found. Click "New Claim" to submit one.</td>
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
