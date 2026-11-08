@extends('layouts.app')

@section('title', 'Edit Employee - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Edit Employee</h1>
            <p class="text-muted mb-0">Edit employee information: {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_code }})</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if(auth()->user()->role === 'admin')
                <form action="{{ route('employees.toggle.status', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to {{ $employee->employmentDetail->status == 'active' ? 'deactivate' : 'activate' }} this employee?')">
                    @csrf
                    <button type="submit" class="btn {{ $employee->employmentDetail->status == 'active' ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                        <i class="bi {{ $employee->employmentDetail->status == 'active' ? 'bi-toggle-on' : 'bi-toggle-off' }} me-2"></i>
                        {{ $employee->employmentDetail->status == 'active' ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
            @endif
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Personal Information Form -->
    <div class="card mb-4" style="display: {{ $step == 'personal' ? 'block' : 'none' }};">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-person me-2"></i>Personal Information</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employees.update.personal', $employee->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#personal">Personal Information</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#employment">Employment Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#salary">Salary Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bank">Bank Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents">Documents</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#emergency">Emergency Contact</button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Personal Information Tab -->
                    <div class="tab-pane fade show active" id="personal">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Enter first name" value="{{ $employee->first_name }}" required pattern="[A-Za-z\s]+" title="First name should contain only letters" oninput="validatePersonalForm()">
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Enter last name" value="{{ $employee->last_name }}" required pattern="[A-Za-z\s]+" title="Last name should contain only letters" oninput="validatePersonalForm()">
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select id="gender" name="gender" class="form-select" required onchange="validatePersonalForm()">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ $employee->gender == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ $employee->gender == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ $employee->gender == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="{{ $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : '' }}" required onchange="validatePersonalForm()">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address" value="{{ $employee->email }}" required oninput="validatePersonalForm()">
                            </div>
                            <div class="col-md-6">
                                <label for="aadhaar_number" class="form-label">Aadhaar Number</label>
                                <input type="text" id="aadhaar_number" name="aadhaar_number" class="form-control" placeholder="Enter Aadhaar number" value="{{ $employee->aadhaar_number }}" required pattern="[0-9]{12}" title="Aadhaar number must be 12 digits" oninput="validatePersonalForm()">
                            </div>
                            <div class="col-md-6">
                                <label for="pan_number" class="form-label">PAN Number</label>
                                <input type="text" id="pan_number" name="pan_number" class="form-control" placeholder="Enter PAN number" value="{{ $employee->pan_number }}" required pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" title="PAN format: ABCDE1234F" oninput="validatePersonalForm()">
                            </div>
                            <div class="col-md-6">
                                <label for="mobile_number" class="form-label">Mobile Number</label>
                                <input type="tel" id="mobile_number" name="mobile_number" class="form-control" placeholder="Enter mobile number" value="{{ $employee->mobile_number }}" required pattern="[0-9]{10}" title="Mobile number must be 10 digits" oninput="validatePersonalForm()">
                            </div>
                            <div class="col-md-6">
                                <label for="photo-upload" class="form-label">Photo Upload</label>
                                <input type="file" id="photo-upload" name="photo" class="form-control" accept=".jpg,.png">
                                <small class="form-text text-muted">JPG, PNG (Max. 2MB)</small>
                                @if($employee->photo)
                                    <div class="mt-2">
                                        <small class="text-muted">Current photo:</small><br>
                                        <img src="{{ asset('storage/' . $employee->photo) }}" class="mt-1" style="max-width: 100px; max-height: 100px; border-radius: 50%;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Employment Details Tab -->
                    <div class="tab-pane fade" id="employment">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="department" class="form-label">Department</label>
                                <select name="department" class="form-select" required>
                                    <option value="">Select Department</option>
                                    <option value="it" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->department)) === 'it') ? 'selected' : '' }}>IT</option>
                                    <option value="hr" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->department)) === 'hr') ? 'selected' : '' }}>HR</option>
                                    <option value="finance" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->department)) === 'finance') ? 'selected' : '' }}>Finance</option>
                                    <option value="marketing" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->department)) === 'marketing') ? 'selected' : '' }}>Marketing</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="designation" class="form-label">Designation</label>
                                <input type="text" name="designation" class="form-control" placeholder="Enter designation" value="{{ $employee->employmentDetail->designation ?? '' }}" pattern="[A-Za-z\s]+" title="Designation should contain only letters">
                            </div>
                            <div class="col-md-6">
                                <label for="employment_type" class="form-label">Employment Type</label>
                                <select name="employment_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="full-time" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->employment_type)) === 'full-time') ? 'selected' : '' }}>Full-time</option>
                                    <option value="part-time" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->employment_type)) === 'part-time') ? 'selected' : '' }}>Part-time</option>
                                    <option value="contract" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->employment_type)) === 'contract') ? 'selected' : '' }}>Contract</option>
                                    <option value="intern" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->employment_type)) === 'intern') ? 'selected' : '' }}>Intern</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="joining_date" class="form-label">Joining Date</label>
                                <input type="date" name="joining_date" class="form-control" value="{{ $employee->employmentDetail->joining_date ? $employee->employmentDetail->joining_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                    </div>

                    <!-- Salary Details Tab -->
                    <div class="tab-pane fade" id="salary">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="basic_salary" class="form-label">Basic Salary</label>
                                <input type="number" name="basic_salary" class="form-control" placeholder="Enter basic salary" value="{{ $employee->salaryDetail->basic_salary ?? '' }}" step="0.01" min="0">
                            </div>
                            <div class="col-md-6">
                                <label for="salary_type" class="form-label">Salary Type</label>
                                <select name="salary_type" class="form-select">
                                    <option value="">Select Type</option>
                                    <option value="monthly" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->salary_type)) === 'monthly') ? 'selected' : '' }}>Monthly</option>
                                    <option value="weekly" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->salary_type)) === 'weekly') ? 'selected' : '' }}>Weekly</option>
                                    <option value="bi-weekly" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->salary_type)) === 'bi-weekly') ? 'selected' : '' }}>Bi-weekly</option>
                                    <option value="annual" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->salary_type)) === 'annual') ? 'selected' : '' }}>Annual</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="currency" class="form-label">Currency</label>
                                <select name="currency" class="form-select">
                                    <option value="">Select Currency</option>
                                    <option value="inr" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->currency)) === 'inr') ? 'selected' : '' }}>INR</option>
                                    <option value="usd" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->currency)) === 'usd') ? 'selected' : '' }}>USD</option>
                                    <option value="eur" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->currency)) === 'eur') ? 'selected' : '' }}>EUR</option>
                                    <option value="gbp" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->currency)) === 'gbp') ? 'selected' : '' }}>GBP</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Details Tab -->
                    <div class="tab-pane fade" id="bank">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="bank_name" class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" placeholder="Enter bank name" value="{{ $employee->bankDetail->bank_name ?? '' }}" pattern="[A-Za-z\s]+" title="Bank name should contain only letters">
                            </div>
                            <div class="col-md-6">
                                <label for="account_number" class="form-label">Account Number</label>
                                <input type="text" name="account_number" class="form-control" placeholder="Enter account number" value="{{ $employee->bankDetail->account_number ?? '' }}" pattern="[0-9]{9,18}" title="Account number must be 9-18 digits">
                            </div>
                            <div class="col-md-6">
                                <label for="ifsc_code" class="form-label">IFSC Code</label>
                                <input type="text" name="ifsc_code" class="form-control" placeholder="Enter IFSC code" value="{{ $employee->bankDetail->ifsc_code ?? '' }}" pattern="[A-Z]{4}0[A-Z0-9]{6}" title="IFSC format: SBIN0001234">
                            </div>
                            <div class="col-md-6">
                                <label for="branch_name" class="form-label">Branch Name</label>
                                <input type="text" name="branch_name" class="form-control" placeholder="Enter branch name" value="{{ $employee->bankDetail->branch_name ?? '' }}" pattern="[A-Za-z\s]+" title="Branch name should contain only letters">
                            </div>
                        </div>
                    </div>

                    <!-- Documents Tab -->
                    <div class="tab-pane fade" id="documents">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="resume" class="form-label">Resume</label>
                                <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                                @if($employee->employeeDocument && $employee->employeeDocument->resume)
                                    <small class="text-muted">Current: File uploaded</small>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="aadhaar_card" class="form-label">Aadhaar Card</label>
                                <input type="file" name="aadhaar_card" class="form-control" accept=".pdf,.jpg,.png,.jpeg">
                                @if($employee->employeeDocument && $employee->employeeDocument->aadhaar_card)
                                    <small class="text-muted">Current: File uploaded</small>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="pan_card" class="form-label">PAN Card</label>
                                <input type="file" name="pan_card" class="form-control" accept=".pdf,.jpg,.png,.jpeg">
                                @if($employee->employeeDocument && $employee->employeeDocument->pan_card)
                                    <small class="text-muted">Current: File uploaded</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Emergency Contact Tab -->
                    <div class="tab-pane fade" id="emergency">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="emergency_contact_name" class="form-label">Contact Name</label>
                                <input type="text" name="emergency_contact_name" class="form-control" placeholder="Enter contact name" value="{{ $employee->emergencyContact->contact_name ?? '' }}" pattern="[A-Za-z\s]+" title="Contact name should contain only letters">
                            </div>
                            <div class="col-md-6">
                                <label for="emergency_contact_relationship" class="form-label">Relationship</label>
                                <select name="emergency_contact_relationship" class="form-select">
                                    <option value="">Select Relationship</option>
                                    <option value="spouse" {{ ($employee->emergencyContact && trim(strtolower($employee->emergencyContact->relationship)) === 'spouse') ? 'selected' : '' }}>Spouse</option>
                                    <option value="parent" {{ ($employee->emergencyContact && trim(strtolower($employee->emergencyContact->relationship)) === 'parent') ? 'selected' : '' }}>Parent</option>
                                    <option value="sibling" {{ ($employee->emergencyContact && trim(strtolower($employee->emergencyContact->relationship)) === 'sibling') ? 'selected' : '' }}>Sibling</option>
                                    <option value="friend" {{ ($employee->emergencyContact && trim(strtolower($employee->emergencyContact->relationship)) === 'friend') ? 'selected' : '' }}>Friend</option>
                                    <option value="other" {{ ($employee->emergencyContact && trim(strtolower($employee->emergencyContact->relationship)) === 'other') ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="emergency_contact_number" class="form-label">Contact Number</label>
                                <input type="tel" name="emergency_contact_number" class="form-control" placeholder="Enter contact number" value="{{ $employee->emergencyContact->contact_number ?? '' }}" pattern="[0-9]{10}" title="Contact number must be 10 digits">
                            </div>
                            <div class="col-12">
                                <label for="emergency_contact_address" class="form-label">Address</label>
                                <textarea name="emergency_contact_address" class="form-control" placeholder="Enter address" rows="3" maxlength="500">{{ $employee->emergencyContact->address ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('employees.index') }}'">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-right me-2"></i>Next: Employment Details
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Employment Details Form -->
    <div class="card mb-4" style="display: {{ $step == 'employment' ? 'block' : 'none' }};">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-briefcase me-2"></i>Employment Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employees.update.employment', $employee->id) }}">
                @csrf
                @method('PUT')
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#personal">Personal Information</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#employment">Employment Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#salary">Salary Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bank">Bank Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents">Documents</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#emergency">Emergency Contact</button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="employment">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="department" class="form-label">Department</label>
                                <select name="department" class="form-select" required>
                                    <option value="">Select Department</option>
                                    <option value="it" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->department)) === 'it') ? 'selected' : '' }}>IT</option>
                                    <option value="hr" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->department)) === 'hr') ? 'selected' : '' }}>HR</option>
                                    <option value="finance" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->department)) === 'finance') ? 'selected' : '' }}>Finance</option>
                                    <option value="marketing" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->department)) === 'marketing') ? 'selected' : '' }}>Marketing</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="designation" class="form-label">Designation</label>
                                <input type="text" name="designation" class="form-control" placeholder="Enter designation" value="{{ $employee->employmentDetail->designation ?? '' }}" required pattern="[A-Za-z\s]+" title="Designation should contain only letters">
                            </div>
                            <div class="col-md-6">
                                <label for="employment_type" class="form-label">Employment Type</label>
                                <select name="employment_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="full-time" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->employment_type)) === 'full-time') ? 'selected' : '' }}>Full-time</option>
                                    <option value="part-time" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->employment_type)) === 'part-time') ? 'selected' : '' }}>Part-time</option>
                                    <option value="contract" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->employment_type)) === 'contract') ? 'selected' : '' }}>Contract</option>
                                    <option value="intern" {{ ($employee->employmentDetail && trim(strtolower($employee->employmentDetail->employment_type)) === 'intern') ? 'selected' : '' }}>Intern</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="joining_date" class="form-label">Joining Date</label>
                                <input type="date" name="joining_date" class="form-control" value="{{ $employee->employmentDetail->joining_date ? $employee->employmentDetail->joining_date->format('Y-m-d') : '' }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('employees.index') }}'">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-right me-2"></i>Next: Salary Details
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Salary Details Form -->
    <div class="card mb-4" style="display: {{ $step == 'salary' ? 'block' : 'none' }};">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Salary Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employees.update.salary', $employee->id) }}">
                @csrf
                @method('PUT')
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#personal">Personal Information</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#employment">Employment Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#salary">Salary Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bank">Bank Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents">Documents</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#emergency">Emergency Contact</button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="salary">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="basic_salary" class="form-label">Basic Salary</label>
                                <input type="number" name="basic_salary" class="form-control" placeholder="Enter basic salary" value="{{ $employee->salaryDetail->basic_salary ?? '' }}" step="0.01" required min="0">
                            </div>
                            <div class="col-md-6">
                                <label for="salary_type" class="form-label">Salary Type</label>
                                <select name="salary_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="monthly" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->salary_type)) === 'monthly') ? 'selected' : '' }}>Monthly</option>
                                    <option value="weekly" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->salary_type)) === 'weekly') ? 'selected' : '' }}>Weekly</option>
                                    <option value="bi-weekly" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->salary_type)) === 'bi-weekly') ? 'selected' : '' }}>Bi-weekly</option>
                                    <option value="annual" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->salary_type)) === 'annual') ? 'selected' : '' }}>Annual</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="currency" class="form-label">Currency</label>
                                <select name="currency" class="form-select" required>
                                    <option value="">Select Currency</option>
                                    <option value="inr" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->currency)) === 'inr') ? 'selected' : '' }}>INR</option>
                                    <option value="usd" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->currency)) === 'usd') ? 'selected' : '' }}>USD</option>
                                    <option value="eur" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->currency)) === 'eur') ? 'selected' : '' }}>EUR</option>
                                    <option value="gbp" {{ ($employee->salaryDetail && trim(strtolower($employee->salaryDetail->currency)) === 'gbp') ? 'selected' : '' }}>GBP</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('employees.index') }}'">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-right me-2"></i>Next: Bank Details
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bank Details Form -->
    <div class="card mb-4" style="display: {{ $step == 'bank' ? 'block' : 'none' }};">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-bank me-2"></i>Bank Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employees.update.bank', $employee->id) }}">
                @csrf
                @method('PUT')
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#personal">Personal Information</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#employment">Employment Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#salary">Salary Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#bank">Bank Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents">Documents</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#emergency">Emergency Contact</button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="bank">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="bank_name" class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" placeholder="Enter bank name" value="{{ $employee->bankDetail->bank_name ?? '' }}" pattern="[A-Za-z\s]+" title="Bank name should contain only letters">
                            </div>
                            <div class="col-md-6">
                                <label for="account_number" class="form-label">Account Number</label>
                                <input type="text" name="account_number" class="form-control" placeholder="Enter account number" value="{{ $employee->bankDetail->account_number ?? '' }}" pattern="[0-9]{9,18}" title="Account number must be 9-18 digits">
                            </div>
                            <div class="col-md-6">
                                <label for="ifsc_code" class="form-label">IFSC Code</label>
                                <input type="text" name="ifsc_code" class="form-control" placeholder="Enter IFSC code" value="{{ $employee->bankDetail->ifsc_code ?? '' }}" pattern="[A-Z]{4}0[A-Z0-9]{6}" title="IFSC format: SBIN0001234">
                            </div>
                            <div class="col-md-6">
                                <label for="branch_name" class="form-label">Branch Name</label>
                                <input type="text" name="branch_name" class="form-control" placeholder="Enter branch name" value="{{ $employee->bankDetail->branch_name ?? '' }}" pattern="[A-Za-z\s]+" title="Branch name should contain only letters">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('employees.index') }}'">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-right me-2"></i>Next: Documents
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Documents Form -->
    <div class="card mb-4" style="display: {{ $step == 'documents' ? 'block' : 'none' }};">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-file-earmark me-2"></i>Documents</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employees.update.documents', $employee->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#personal">Personal Information</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#employment">Employment Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#salary">Salary Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bank">Bank Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#documents">Documents</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#emergency">Emergency Contact</button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="documents">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="resume" class="form-label">Resume</label>
                                <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                                @if($employee->employeeDocument && $employee->employeeDocument->resume)
                                    <small class="text-muted">Current: File uploaded</small>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="aadhaar_card" class="form-label">Aadhaar Card</label>
                                <input type="file" name="aadhaar_card" class="form-control" accept=".pdf,.jpg,.png,.jpeg">
                                @if($employee->employeeDocument && $employee->employeeDocument->aadhaar_card)
                                    <small class="text-muted">Current: File uploaded</small>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="pan_card" class="form-label">PAN Card</label>
                                <input type="file" name="pan_card" class="form-control" accept=".pdf,.jpg,.png,.jpeg">
                                @if($employee->employeeDocument && $employee->employeeDocument->pan_card)
                                    <small class="text-muted">Current: File uploaded</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('employees.index') }}'">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-right me-2"></i>Next: Emergency Contact
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Emergency Contact Form -->
    <div class="card mb-4" style="display: {{ $step == 'emergency' ? 'block' : 'none' }};">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Emergency Contact</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employees.update.emergency', $employee->id) }}">
                @csrf
                @method('PUT')
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#personal">Personal Information</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#employment">Employment Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#salary">Salary Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bank">Bank Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents">Documents</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#emergency">Emergency Contact</button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="emergency">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="emergency_contact_name" class="form-label">Contact Name</label>
                                <input type="text" name="emergency_contact_name" class="form-control" placeholder="Enter contact name" value="{{ $employee->emergencyContact->contact_name ?? '' }}" pattern="[A-Za-z\s]+" title="Contact name should contain only letters">
                            </div>
                            <div class="col-md-6">
                                <label for="emergency_contact_relationship" class="form-label">Relationship</label>
                                <select name="emergency_contact_relationship" class="form-select">
                                    <option value="">Select Relationship</option>
                                    <option value="spouse" {{ ($employee->emergencyContact && trim(strtolower($employee->emergencyContact->relationship)) === 'spouse') ? 'selected' : '' }}>Spouse</option>
                                    <option value="parent" {{ ($employee->emergencyContact && trim(strtolower($employee->emergencyContact->relationship)) === 'parent') ? 'selected' : '' }}>Parent</option>
                                    <option value="sibling" {{ ($employee->emergencyContact && trim(strtolower($employee->emergencyContact->relationship)) === 'sibling') ? 'selected' : '' }}>Sibling</option>
                                    <option value="friend" {{ ($employee->emergencyContact && trim(strtolower($employee->emergencyContact->relationship)) === 'friend') ? 'selected' : '' }}>Friend</option>
                                    <option value="other" {{ ($employee->emergencyContact && trim(strtolower($employee->emergencyContact->relationship)) === 'other') ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="emergency_contact_number" class="form-label">Contact Number</label>
                                <input type="tel" name="emergency_contact_number" class="form-control" placeholder="Enter contact number" value="{{ $employee->emergencyContact->contact_number ?? '' }}" pattern="[0-9]{10}" title="Contact number must be 10 digits">
                            </div>
                            <div class="col-12">
                                <label for="emergency_contact_address" class="form-label">Address</label>
                                <textarea name="emergency_contact_address" class="form-control" placeholder="Enter address" rows="3" maxlength="500">{{ $employee->emergencyContact->address ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('employees.index') }}'">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
