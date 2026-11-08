@extends('layouts.app')

@section('title', 'Review Deductions - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Review Deductions</h1>
            <p class="text-muted mb-0">Review and manage payroll deductions</p>
        </div>
        <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body bg-light">
            <div class="d-flex gap-2 flex-wrap">
                <div class="badge bg-success">1. Generate Salary</div>
                <div class="badge bg-primary">2. Review Deductions</div>
                <div class="badge bg-secondary">3. Approve Payroll</div>
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
                            <h4 class="mb-0" id="total-deductions">₹{{ number_format($payroll->deductions, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6 class="mb-2 text-uppercase tile-label">Net Salary</h6>
                            <h4 class="mb-0" id="net-salary">₹{{ number_format($payroll->net_salary, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Deduction Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('payrolls.store.deductions', $payroll->id) }}" id="deductions-form">
                @csrf
                <div id="deductions-container">
                    @if($payroll->deductionDetails->count() > 0)
                        @foreach($payroll->deductionDetails as $index => $deduction)
                            <div class="card mb-3 deduction-item">
                                <div class="card-body">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-3">
                                            <label class="form-label">Type</label>
                                            <select name="deductions[{{ $index }}][type]" class="form-select" required>
                                                <option value="tax" {{ $deduction->deduction_type === 'tax' ? 'selected' : '' }}>Tax</option>
                                                <option value="pf" {{ $deduction->deduction_type === 'pf' ? 'selected' : '' }}>PF</option>
                                                <option value="esi" {{ $deduction->deduction_type === 'esi' ? 'selected' : '' }}>ESI</option>
                                                <option value="leave" {{ $deduction->deduction_type === 'leave' ? 'selected' : '' }}>Leave Deduction</option>
                                                <option value="loan" {{ $deduction->deduction_type === 'loan' ? 'selected' : '' }}>Loan</option>
                                                <option value="advance" {{ $deduction->deduction_type === 'advance' ? 'selected' : '' }}>Advance</option>
                                                <option value="other" {{ $deduction->deduction_type === 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Description</label>
                                            <input type="text" name="deductions[{{ $index }}][description]" class="form-control" value="{{ $deduction->description ?? '' }}" placeholder="Description">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Amount (₹)</label>
                                            <input type="number" name="deductions[{{ $index }}][amount]" class="form-control deduction-amount" value="{{ $deduction->amount }}" step="0.01" min="0" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Status</label>
                                            <select name="deductions[{{ $index }}][status]" class="form-select">
                                                <option value="applied" {{ $deduction->status === 'applied' ? 'selected' : '' }}>Applied</option>
                                                <option value="pending" {{ $deduction->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="waived" {{ $deduction->status === 'waived' ? 'selected' : '' }}>Waived</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger w-100" onclick="removeDeduction(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5" id="empty-state">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-3">No deductions added yet. Click "Add Deduction" to add one.</p>
                        </div>
                    @endif
                </div>
                <button type="button" class="btn btn-outline-success mb-3" onclick="addDeduction()">
                    <i class="bi bi-plus-circle me-2"></i>Add Deduction
                </button>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save & Continue to Approval</button>
                    <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        let deductionCount = {{ $payroll->deductionDetails->count() }};

        function addDeduction() {
            const container = document.getElementById('deductions-container');
            const emptyState = document.getElementById('empty-state');
            if (emptyState) {
                emptyState.remove();
            }

            const newDeduction = document.createElement('div');
            newDeduction.className = 'card mb-3 deduction-item';
            newDeduction.innerHTML = `
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select name="deductions[${deductionCount}][type]" class="form-select" required>
                                <option value="tax">Tax</option>
                                <option value="pf">PF</option>
                                <option value="esi">ESI</option>
                                <option value="leave">Leave Deduction</option>
                                <option value="loan">Loan</option>
                                <option value="advance">Advance</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Description</label>
                            <input type="text" name="deductions[${deductionCount}][description]" class="form-control" placeholder="Description">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Amount (₹)</label>
                            <input type="number" name="deductions[${deductionCount}][amount]" class="form-control deduction-amount" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="deductions[${deductionCount}][status]" class="form-select">
                                <option value="applied">Applied</option>
                                <option value="pending">Pending</option>
                                <option value="waived">Waived</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger w-100" onclick="removeDeduction(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newDeduction);
            deductionCount++;
        }

        function removeDeduction(button) {
            const deductionItem = button.closest('.deduction-item');
            deductionItem.remove();
            updateTotals();

            const container = document.getElementById('deductions-container');
            if (container.querySelectorAll('.deduction-item').length === 0) {
                container.innerHTML = '<div class="text-center py-5" id="empty-state"><i class="bi bi-inbox fs-1 text-muted"></i><p class="text-muted mt-3">No deductions added yet. Click "Add Deduction" to add one.</p></div>';
            }
        }

        function updateTotals() {
            const amounts = document.querySelectorAll('.deduction-amount');
            let totalDeductions = 0;
            amounts.forEach(input => {
                totalDeductions += parseFloat(input.value) || 0;
            });

            const basicSalary = {{ $payroll->basic_salary }};
            const allowances = {{ $payroll->allowances }};
            const netSalary = basicSalary + allowances - totalDeductions;

            document.getElementById('total-deductions').textContent = '₹' + totalDeductions.toFixed(2);
            document.getElementById('net-salary').textContent = '₹' + netSalary.toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.deduction-amount').forEach(input => {
                input.addEventListener('input', updateTotals);
            });
        });
    </script>
@endsection
