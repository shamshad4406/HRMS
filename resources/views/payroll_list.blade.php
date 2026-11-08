@extends('layouts.app')

@section('title', 'Payroll List - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Payroll List</h1>
            <p class="text-muted mb-0">View and manage all payroll records</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('payrolls.salary.structure') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-sliders me-2"></i>Salary Structure &amp; Rules
                </a>
            @endif
            <a href="{{ route('payrolls.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Add Payroll
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Payrolls</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="min-width: 1200px;">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 200px;">Employee</th>
                            <th style="min-width: 120px;">Month/Year</th>
                            <th style="min-width: 110px;">Basic Salary</th>
                            <th style="min-width: 100px;">Allowances</th>
                            <th style="min-width: 100px;">Deductions</th>
                            <th style="min-width: 110px;">Net Salary</th>
                            <th style="min-width: 90px;">Status</th>
                            <th style="min-width: 90px;">Approval</th>
                            <th style="min-width: 250px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payrolls ?? [] as $payroll)
                        <tr>
                            <td>
                                @if($payroll->personalInformation)
                                    <div class="d-flex align-items-center">
                                        @if($payroll->personalInformation->photo)
                                            <img src="{{ asset('storage/' . $payroll->personalInformation->photo) }}" class="avatar me-2">
                                        @else
                                            <div class="avatar-placeholder me-2">
                                                {{ substr($payroll->personalInformation->first_name, 0, 1) }}{{ substr($payroll->personalInformation->last_name, 0, 1) }}
                                            </div>
                                        @endif
                                        <span>{{ $payroll->personalInformation->first_name }} {{ $payroll->personalInformation->last_name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ ucfirst($payroll->month) }} {{ $payroll->year }}</td>
                            <td>₹{{ number_format($payroll->basic_salary, 2) }}</td>
                            <td>₹{{ number_format($payroll->allowances, 2) }}</td>
                            <td>₹{{ number_format($payroll->deductions, 2) }}</td>
                            <td><strong>₹{{ number_format($payroll->net_salary, 2) }}</strong></td>
                            <td>
                                @php
                                    $statusClass = match($payroll->status) {
                                        'pending' => 'warning',
                                        'processed' => 'info',
                                        'paid' => 'success',
                                        'rejected' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">{{ ucfirst($payroll->status) }}</span>
                            </td>
                            <td>
                                @php
                                    $approvalClass = match($payroll->approval_status) {
                                        'pending' => 'warning',
                                        'processed' => 'info',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $approvalClass }}">{{ ucfirst($payroll->approval_status) }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if(auth()->user()->role === 'admin')
                                        @if($payroll->approval_status === 'pending')
                                            <a href="{{ route('payrolls.review.deductions', $payroll->id) }}" class="btn btn-outline-primary">Review</a>
                                        @endif
                                        @if($payroll->approval_status === 'processed' || $payroll->approval_status === 'pending')
                                            <a href="{{ route('payrolls.approve', $payroll->id) }}" class="btn btn-outline-success">Approve</a>
                                        @endif
                                        @if($payroll->approval_status === 'approved' && $payroll->status !== 'paid')
                                            <a href="{{ route('payrolls.record.payment', $payroll->id) }}" class="btn btn-outline-info">Pay</a>
                                        @endif
                                    @endif
                                    <a href="{{ route('payrolls.history', $payroll->id) }}" class="btn btn-outline-secondary">History</a>
                                    @if($payroll->status === 'paid')
                                        <a href="{{ route('payrolls.payslip', $payroll->id) }}" class="btn btn-outline-success">Payslip</a>
                                    @endif
                                    <form action="{{ route('payrolls.destroy', $payroll->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payroll record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($payrolls->count() == 0)
                        <tr>
                            <td colspan="9" class="text-center text-muted">No payroll records found</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if($payrolls->hasPages())
            <nav aria-label="Page navigation" class="mt-4">
                {{ $payrolls->links() }}
            </nav>
            @endif
        </div>
    </div>
@endsection
