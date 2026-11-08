@extends('layouts.app')

@section('title', 'Generate Salary - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Generate Salary</h1>
            <p class="text-muted mb-0">Generate salary for multiple employees</p>
        </div>
        <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body bg-light">
            <div class="d-flex gap-2 flex-wrap">
                <div class="badge bg-primary">1. Generate Salary</div>
                <div class="badge bg-secondary">2. Review Deductions</div>
                <div class="badge bg-secondary">3. Approve Payroll</div>
                <div class="badge bg-secondary">4. Record Payment</div>
                <div class="badge bg-secondary">5. Generate Payslip</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Select Month and Year</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('payrolls.generate.salary') }}">
                @csrf
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="month" class="form-label">Month</label>
                        <select name="month" id="month" class="form-select" required>
                            <option value="">Select Month</option>
                            <option value="january">January</option>
                            <option value="february">February</option>
                            <option value="march">March</option>
                            <option value="april">April</option>
                            <option value="may">May</option>
                            <option value="june">June</option>
                            <option value="july">July</option>
                            <option value="august">August</option>
                            <option value="september">September</option>
                            <option value="october">October</option>
                            <option value="november">November</option>
                            <option value="december">December</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="year" class="form-label">Year</label>
                        <select name="year" id="year" class="form-select" required>
                            <option value="">Select Year</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                        </select>
                    </div>
                </div>

                <h6 class="mb-3">Select Employees</h6>
                <div class="row g-3" id="employee-grid">
                    @foreach($employees as $employee)
                        <div class="col-md-6 col-lg-4">
                            <label class="card employee-card h-100 cursor-pointer">
                                <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" class="employee-checkbox">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-placeholder">
                                            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $employee->first_name }} {{ $employee->last_name }}</h6>
                                            <small class="text-muted">{{ $employee->employee_code }}</small>
                                            @if($employee->salaryDetail)
                                                <div class="mt-2">
                                                    <small class="text-muted">Basic Salary: ₹{{ number_format($employee->salaryDetail->basic_salary, 2) }}</small>
                                                </div>
                                            @else
                                                <div class="mt-2">
                                                    <small class="text-danger">No salary information</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" name="auto_calculate" id="auto_calculate" value="1" checked>
                    <label class="form-check-label" for="auto_calculate">
                        Apply salary structure (allowances), statutory deductions (PF/ESI/PT/TDS) and unpaid-leave / absent / late deductions
                        - <a href="{{ route('payrolls.salary.structure') }}">edit structure &amp; rules</a>
                    </label>
                </div>

                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="include_reimbursements" id="include_reimbursements" value="1" checked>
                    <label class="form-check-label" for="include_reimbursements">
                        Include approved, unpaid reimbursement claims (added to allowances and marked paid with the salary)
                    </label>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-cash-coin me-2"></i>Generate Salary</button>
                    <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.employee-card').forEach(card => {
            card.addEventListener('click', function() {
                const checkbox = this.querySelector('.employee-checkbox');
                checkbox.checked = !checkbox.checked;
                this.classList.toggle('selected', checkbox.checked);
            });
        });
    </script>
@endsection
