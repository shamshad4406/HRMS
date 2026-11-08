@extends('layouts.app')

@section('title', 'Salary Structure & Payroll Rules - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Salary Structure &amp; Payroll Rules</h1>
            <p class="text-muted mb-0">Allowances, statutory deductions and attendance/leave rules used when salary is generated</p>
        </div>
        <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Payrolls
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===== Company wide rules ===== --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0"><i class="bi bi-sliders me-2"></i>Payroll rules (company wide)</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('payrolls.salary.settings') }}">
                @csrf
                @php $offDays = explode(',', $settings->weekly_off_days ?? ''); @endphp
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Per-day salary is calculated on</label>
                        <select name="salary_divisor" class="form-select">
                            <option value="calendar_days" {{ $settings->salary_divisor === 'calendar_days' ? 'selected' : '' }}>Calendar days of the month</option>
                            <option value="working_days" {{ $settings->salary_divisor === 'working_days' ? 'selected' : '' }}>Working days of the month</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label d-block">Weekly off days</label>
                        @foreach(['sun' => 'Sun', 'mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat'] as $key => $label)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="weekly_off_days[]" value="{{ $key }}" id="off_{{ $key }}" {{ in_array($key, $offDays) ? 'checked' : '' }}>
                                <label class="form-check-label" for="off_{{ $key }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="unpaid_leave_deduction" id="unpaid_leave_deduction" value="1" {{ $settings->unpaid_leave_deduction ? 'checked' : '' }}>
                            <label class="form-check-label" for="unpaid_leave_deduction">Deduct approved <strong>unpaid leave</strong></label>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="absent_deduction" id="absent_deduction" value="1" {{ $settings->absent_deduction ? 'checked' : '' }}>
                            <label class="form-check-label" for="absent_deduction">
                                Deduct <strong>absent days</strong> (working day with no attendance and no approved leave)
                                <small class="text-muted d-block">Turn on only if attendance is marked reliably for everyone.</small>
                            </label>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Late marks per deduction (0 = off)</label>
                        <input type="number" name="late_marks_per_deduction" class="form-control" min="0" max="31" value="{{ old('late_marks_per_deduction', $settings->late_marks_per_deduction) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Days deducted per block of late marks</label>
                        <input type="number" name="late_deduction_days" class="form-control" step="0.25" min="0" max="5" value="{{ old('late_deduction_days', $settings->late_deduction_days) }}">
                    </div>
                    <div class="col-md-4"></div>

                    <div class="col-md-3">
                        <label class="form-label">PF rate (%)</label>
                        <input type="number" name="pf_rate" class="form-control" step="0.01" min="0" max="100" value="{{ old('pf_rate', $settings->pf_rate) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">PF wage ceiling (₹, 0 = none)</label>
                        <input type="number" name="pf_wage_ceiling" class="form-control" step="0.01" min="0" value="{{ old('pf_wage_ceiling', $settings->pf_wage_ceiling) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">ESI rate (%)</label>
                        <input type="number" name="esi_rate" class="form-control" step="0.01" min="0" max="100" value="{{ old('esi_rate', $settings->esi_rate) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">ESI gross limit (₹)</label>
                        <input type="number" name="esi_gross_limit" class="form-control" step="0.01" min="0" value="{{ old('esi_gross_limit', $settings->esi_gross_limit) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Professional tax (₹/month)</label>
                        <input type="number" name="professional_tax" class="form-control" step="0.01" min="0" value="{{ old('professional_tax', $settings->professional_tax) }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-save me-2"></i>Save rules</button>
            </form>
        </div>
    </div>

    {{-- ===== Per employee structure ===== --}}
    <div class="card">
        <div class="card-header"><h5 class="mb-0"><i class="bi bi-people me-2"></i>Employee salary structure</h5></div>
        <div class="card-body">
            <p class="text-muted">Changes apply to payrolls generated afterwards. Existing payrolls are not changed.</p>

            @forelse($employees as $employee)
                @php $s = $employee->salaryDetail; @endphp
                <form method="POST" action="{{ route('payrolls.salary.structure.update', $employee->id) }}" class="border rounded p-3 mb-3">
                    @csrf
                    <div class="d-flex justify-content-between flex-wrap mb-2">
                        <strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong>
                        <small class="text-muted">{{ $employee->employee_code }}</small>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-2">
                            <label class="form-label small">Basic (₹)</label>
                            <input type="number" name="basic_salary" class="form-control form-control-sm" step="0.01" min="0" required value="{{ $s->basic_salary }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">HRA (₹)</label>
                            <input type="number" name="hra" class="form-control form-control-sm" step="0.01" min="0" value="{{ $s->hra }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Special allowance (₹)</label>
                            <input type="number" name="special_allowance" class="form-control form-control-sm" step="0.01" min="0" value="{{ $s->special_allowance }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Other allowance (₹)</label>
                            <input type="number" name="other_allowance" class="form-control form-control-sm" step="0.01" min="0" value="{{ $s->other_allowance }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">TDS / month (₹)</label>
                            <input type="number" name="tds_monthly" class="form-control form-control-sm" step="0.01" min="0" value="{{ $s->tds_monthly }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="pf_applicable" value="1" id="pf_{{ $employee->id }}" {{ $s->pf_applicable ? 'checked' : '' }}>
                            <label class="form-check-label small" for="pf_{{ $employee->id }}">PF applicable</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="esi_applicable" value="1" id="esi_{{ $employee->id }}" {{ $s->esi_applicable ? 'checked' : '' }}>
                            <label class="form-check-label small" for="esi_{{ $employee->id }}">ESI applicable</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="pt_applicable" value="1" id="pt_{{ $employee->id }}" {{ $s->pt_applicable ? 'checked' : '' }}>
                            <label class="form-check-label small" for="pt_{{ $employee->id }}">Professional tax</label>
                        </div>
                    </div>
                </form>
            @empty
                <p class="text-muted mb-0">No employees with salary details yet.</p>
            @endforelse
        </div>
    </div>
@endsection
