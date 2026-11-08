@extends('layouts.app')

@section('title', 'Approve Payroll - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Approve Payroll</h1>
            <p class="text-muted mb-0">Review and approve payroll record</p>
        </div>
        <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body bg-light">
            <div class="d-flex gap-2 flex-wrap">
                <div class="badge bg-success">1. Generate Salary</div>
                <div class="badge bg-success">2. Review Deductions</div>
                <div class="badge bg-primary">3. Approve Payroll</div>
                <div class="badge bg-secondary">4. Record Payment</div>
                <div class="badge bg-secondary">5. Generate Payslip</div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-person me-2"></i>Employee Information</h5>
        </div>
        <div class="card-body">
            @if($payroll->personalInformation)
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Name:</strong> {{ $payroll->personalInformation->first_name }} {{ $payroll->personalInformation->last_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Employee Code:</strong> {{ $payroll->personalInformation->employee_code }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Period:</strong> {{ ucfirst($payroll->month) }} {{ $payroll->year }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Approval Status:</strong>
                            @php
                                $approvalClass = match($payroll->approval_status) {
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $approvalClass }}">{{ ucfirst($payroll->approval_status) }}</span>
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Salary Summary</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2 text-uppercase tile-label">Basic Salary</h6>
                            <h4 class="mb-0">₹{{ number_format($payroll->basic_salary, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2 text-uppercase tile-label">Allowances</h6>
                            <h4 class="mb-0">₹{{ number_format($payroll->allowances, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2 text-uppercase tile-label">Total Deductions</h6>
                            <h4 class="mb-0">₹{{ number_format($payroll->deductions, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6 class="mb-2 text-uppercase tile-label">Net Salary</h6>
                            <h4 class="mb-0">₹{{ number_format($payroll->net_salary, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Deduction Details</h5>
        </div>
        <div class="card-body">
            @if($payroll->deductionDetails->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payroll->deductionDetails as $deduction)
                                <tr>
                                    <td>{{ ucfirst($deduction->deduction_type) }}</td>
                                    <td>{{ $deduction->description ?? '-' }}</td>
                                    <td>₹{{ number_format($deduction->amount, 2) }}</td>
                                    <td>
                                        @php
                                            $statusClass = match($deduction->status) {
                                                'applied' => 'success',
                                                'pending' => 'warning',
                                                'waived' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ ucfirst($deduction->status) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No deductions applied.</p>
            @endif
        </div>
    </div>

    @if($payroll->approval_status === 'pending')
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-check2-circle me-2"></i>Approval Action</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('payrolls.approve', $payroll->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add any notes regarding this approval..."></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="action" value="approve" class="btn btn-success"><i class="bi bi-check-circle me-2"></i>Approve Payroll</button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger"><i class="bi bi-x-circle me-2"></i>Reject Payroll</button>
                        <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Approval Information</h5>
            </div>
            <div class="card-body">
                @if($payroll->approvedBy)
                    <p class="mb-1"><strong>Approved By:</strong> {{ $payroll->approvedBy->name }}</p>
                    <p class="mb-3"><strong>Approved At:</strong> {{ $payroll->approved_at ? $payroll->approved_at->format('Y-m-d H:i:s') : '-' }}</p>
                @endif
                <div class="d-flex gap-2">
                    @if($payroll->approval_status === 'approved')
                        <a href="{{ route('payrolls.record.payment', $payroll->id) }}" class="btn btn-success"><i class="bi bi-cash me-2"></i>Proceed to Payment</a>
                    @endif
                    <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary">Back to List</a>
                </div>
            </div>
        </div>
    @endif
@endsection
