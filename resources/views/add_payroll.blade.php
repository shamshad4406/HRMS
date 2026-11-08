@extends('layouts.app')

@section('title', 'Add Payroll - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Add Payroll</h1>
            <p class="text-muted mb-0">Create a new payroll record</p>
        </div>
        <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-cash me-2"></i>Payroll Information</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('payrolls.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="employee_select" class="form-label">Employee</label>
                        <select name="personal_information_id" id="employee_select" class="form-select" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" data-salary="{{ $employee->salaryDetail->basic_salary ?? 0 }}">
                                    {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
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
                    <div class="col-md-6">
                        <label for="basic_salary" class="form-label">Basic Salary (₹)</label>
                        <input type="number" name="basic_salary" id="basic_salary" class="form-control" placeholder="Enter basic salary" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label for="allowances" class="form-label">Allowances (₹)</label>
                        <input type="number" name="allowances" id="allowances" class="form-control" placeholder="Enter allowances" step="0.01" min="0">
                    </div>
                    <div class="col-md-6">
                        <label for="deductions" class="form-label">Deductions (₹)</label>
                        <input type="number" name="deductions" id="deductions" class="form-control" placeholder="Enter deductions" step="0.01" min="0">
                    </div>
                    <div class="col-md-6">
                        <label for="payment_date" class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" id="payment_date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="pending">Pending</option>
                            <!-- <option value="processed">Processed</option>
                            <option value="paid">Paid</option> -->
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('payrolls.index') }}'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Payroll</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('employee_select').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const salary = selectedOption.getAttribute('data-salary');
            const basicSalaryInput = document.getElementById('basic_salary');

            if (salary && parseFloat(salary) > 0) {
                basicSalaryInput.value = salary;
            } else {
                basicSalaryInput.value = '';
            }
        });
    </script>
@endsection
