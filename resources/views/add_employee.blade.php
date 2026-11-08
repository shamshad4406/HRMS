@extends('layouts.app')

@section('title', 'Add Employee - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Add Employee</h1>
            <p class="text-muted mb-0">Add a new employee to the system</p>
        </div>
        <div class="d-flex gap-2">
            @if($draft)
                <a href="{{ route('employees.create', ['step' => 'personal', 'clear' => 'true']) }}" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-2"></i>Clear Draft
                </a>
            @endif
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    @if($draft)
        <div class="alert alert-info alert-dismissible fade show" id="draft-alert" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            Draft data has been restored. Your progress was saved automatically.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Personal Information Form -->
    <div class="card mb-4" style="display: {{ $step == 'personal' ? 'block' : 'none' }};">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-person me-2"></i>Personal Information</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employees.store.personal') }}" enctype="multipart/form-data">
                @csrf
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
                                <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Enter first name" required pattern="[A-Za-z\s]+" title="First name should contain only letters" oninput="validatePersonalForm()" value="{{ $draft['personal']['first_name'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Enter last name" required pattern="[A-Za-z\s]+" title="Last name should contain only letters" oninput="validatePersonalForm()" value="{{ $draft['personal']['last_name'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select id="gender" name="gender" class="form-select" required onchange="validatePersonalForm()">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ ($draft['personal']['gender'] ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ ($draft['personal']['gender'] ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ ($draft['personal']['gender'] ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" required onchange="validatePersonalForm()" value="{{ $draft['personal']['date_of_birth'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address" required oninput="validatePersonalForm()" value="{{ $draft['personal']['email'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="aadhaar_number" class="form-label">Aadhaar Number</label>
                                <input type="text" id="aadhaar_number" name="aadhaar_number" class="form-control" placeholder="Enter Aadhaar number" required pattern="[0-9]{12}" title="Aadhaar number must be 12 digits" oninput="validatePersonalForm()" value="{{ $draft['personal']['aadhaar_number'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="pan_number" class="form-label">PAN Number</label>
                                <input type="text" id="pan_number" name="pan_number" class="form-control" placeholder="Enter PAN number" required pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" title="PAN format: ABCDE1234F" oninput="validatePersonalForm()" value="{{ $draft['personal']['pan_number'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="mobile_number" class="form-label">Mobile Number</label>
                                <input type="tel" id="mobile_number" name="mobile_number" class="form-control" placeholder="Enter mobile number" required pattern="[0-9]{10}" title="Mobile number must be 10 digits" oninput="validatePersonalForm()" value="{{ $draft['personal']['mobile_number'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="photo-upload" class="form-label">Photo Upload</label>
                                <input type="file" id="photo-upload" name="photo" class="form-control" accept=".jpg,.png">
                                <small class="form-text text-muted">JPG, PNG (Max. 2MB)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Details Tab -->
                    <div class="tab-pane fade" id="employment">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="department" class="form-label">Department</label>
                                <select name="department" class="form-select">
                                    <option value="">Select Department</option>
                                    <option value="it">IT</option>
                                    <option value="hr">HR</option>
                                    <option value="finance">Finance</option>
                                    <option value="marketing">Marketing</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="designation" class="form-label">Designation</label>
                                <input type="text" name="designation" class="form-control" placeholder="Enter designation" pattern="[A-Za-z\s]+" title="Designation should contain only letters">
                            </div>
                            <div class="col-md-6">
                                <label for="employment_type" class="form-label">Employment Type</label>
                                <select name="employment_type" class="form-select">
                                    <option value="">Select Type</option>
                                    <option value="full-time">Full-time</option>
                                    <option value="part-time">Part-time</option>
                                    <option value="contract">Contract</option>
                                    <option value="intern">Intern</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="joining_date" class="form-label">Joining Date</label>
                                <input type="date" name="joining_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on-leave">On Leave</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Salary Details Tab -->
                    <div class="tab-pane fade" id="salary">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="basic_salary" class="form-label">Basic Salary</label>
                                <input type="number" name="basic_salary" class="form-control" placeholder="Enter basic salary" step="0.01" min="0">
                            </div>
                            <div class="col-md-6">
                                <label for="salary_type" class="form-label">Salary Type</label>
                                <select name="salary_type" class="form-select">
                                    <option value="">Select Type</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="bi-weekly">Bi-weekly</option>
                                    <option value="annual">Annual</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="currency" class="form-label">Currency</label>
                                <select name="currency" class="form-select">
                                    <option value="">Select Currency</option>
                                    <option value="inr">INR</option>
                                    <option value="usd">USD</option>
                                    <option value="eur">EUR</option>
                                    <option value="gbp">GBP</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Details Tab -->
                    <div class="tab-pane fade" id="bank">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="bank_name" class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" placeholder="Enter bank name" pattern="[A-Za-z\s]+" title="Bank name should contain only letters" value="{{ $draft['bank']['bank_name'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="account_number" class="form-label">Account Number</label>
                                <input type="text" name="account_number" class="form-control" placeholder="Enter account number" pattern="[0-9]{9,18}" title="Account number must be 9-18 digits" value="{{ $draft['bank']['account_number'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="ifsc_code" class="form-label">IFSC Code</label>
                                <input type="text" name="ifsc_code" class="form-control" placeholder="Enter IFSC code" pattern="[A-Z]{4}0[A-Z0-9]{6}" title="IFSC format: SBIN0001234" value="{{ $draft['bank']['ifsc_code'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="branch_name" class="form-label">Branch Name</label>
                                <input type="text" name="branch_name" class="form-control" placeholder="Enter branch name" pattern="[A-Za-z\s]+" title="Branch name should contain only letters" value="{{ $draft['bank']['branch_name'] ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <!-- Documents Tab -->
                    <div class="tab-pane fade" id="documents">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="resume" class="form-label">Resume</label>
                                <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                            </div>
                            <div class="col-md-6">
                                <label for="aadhaar_card" class="form-label">Aadhaar Card</label>
                                <input type="file" name="aadhaar_card" class="form-control" accept=".pdf,.jpg,.png,.jpeg">
                            </div>
                            <div class="col-md-6">
                                <label for="pan_card" class="form-label">PAN Card</label>
                                <input type="file" name="pan_card" class="form-control" accept=".pdf,.jpg,.png,.jpeg">
                            </div>
                        </div>
                    </div>

                    <!-- Emergency Contact Tab -->
                    <div class="tab-pane fade" id="emergency">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="emergency_contact_name" class="form-label">Contact Name</label>
                                <input type="text" name="emergency_contact_name" class="form-control" placeholder="Enter contact name" pattern="[A-Za-z\s]+" title="Contact name should contain only letters">
                            </div>
                            <div class="col-md-6">
                                <label for="emergency_contact_relationship" class="form-label">Relationship</label>
                                <select name="emergency_contact_relationship" class="form-select">
                                    <option value="">Select Relationship</option>
                                    <option value="spouse">Spouse</option>
                                    <option value="parent">Parent</option>
                                    <option value="sibling">Sibling</option>
                                    <option value="friend">Friend</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="emergency_contact_number" class="form-label">Contact Number</label>
                                <input type="tel" name="emergency_contact_number" class="form-control" placeholder="Enter contact number" pattern="[0-9]{10}" title="Contact number must be 10 digits">
                            </div>
                            <div class="col-12">
                                <label for="emergency_contact_address" class="form-label">Address</label>
                                <textarea name="emergency_contact_address" class="form-control" placeholder="Enter address" rows="3" maxlength="500"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('employees.index') }}'">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearLocalStorageData(); location.reload()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
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
            <form method="POST" action="{{ route('employees.store.employment') }}">
                @csrf
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
                                    <option value="it" {{ ($draft['employment']['department'] ?? '') === 'it' ? 'selected' : '' }}>IT</option>
                                    <option value="hr" {{ ($draft['employment']['department'] ?? '') === 'hr' ? 'selected' : '' }}>HR</option>
                                    <option value="finance" {{ ($draft['employment']['department'] ?? '') === 'finance' ? 'selected' : '' }}>Finance</option>
                                    <option value="marketing" {{ ($draft['employment']['department'] ?? '') === 'marketing' ? 'selected' : '' }}>Marketing</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="designation" class="form-label">Designation</label>
                                <input type="text" name="designation" class="form-control" placeholder="Enter designation" required pattern="[A-Za-z\s]+" title="Designation should contain only letters" value="{{ $draft['employment']['designation'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="employment_type" class="form-label">Employment Type</label>
                                <select name="employment_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="full-time" {{ ($draft['employment']['employment_type'] ?? '') === 'full-time' ? 'selected' : '' }}>Full-time</option>
                                    <option value="part-time" {{ ($draft['employment']['employment_type'] ?? '') === 'part-time' ? 'selected' : '' }}>Part-time</option>
                                    <option value="contract" {{ ($draft['employment']['employment_type'] ?? '') === 'contract' ? 'selected' : '' }}>Contract</option>
                                    <option value="intern" {{ ($draft['employment']['employment_type'] ?? '') === 'intern' ? 'selected' : '' }}>Intern</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="joining_date" class="form-label">Joining Date</label>
                                <input type="date" name="joining_date" class="form-control" required value="{{ $draft['employment']['joining_date'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="">Select Status</option>
                                    <option value="active" {{ ($draft['employment']['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ ($draft['employment']['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="on-leave" {{ ($draft['employment']['status'] ?? '') === 'on-leave' ? 'selected' : '' }}>On Leave</option>
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
                    <button type="button" class="btn btn-outline-secondary" onclick="clearLocalStorageData(); location.reload()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
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
            <form method="POST" action="{{ route('employees.store.salary') }}">
                @csrf
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
                                <input type="number" name="basic_salary" class="form-control" placeholder="Enter basic salary" step="0.01" required min="0" value="{{ $draft['salary']['basic_salary'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="salary_type" class="form-label">Salary Type</label>
                                <select name="salary_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="monthly" {{ ($draft['salary']['salary_type'] ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="weekly" {{ ($draft['salary']['salary_type'] ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="bi-weekly" {{ ($draft['salary']['salary_type'] ?? '') === 'bi-weekly' ? 'selected' : '' }}>Bi-weekly</option>
                                    <option value="annual" {{ ($draft['salary']['salary_type'] ?? '') === 'annual' ? 'selected' : '' }}>Annual</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="currency" class="form-label">Currency</label>
                                <select name="currency" class="form-select" required>
                                    <option value="">Select Currency</option>
                                    <option value="inr" {{ ($draft['salary']['currency'] ?? '') === 'inr' ? 'selected' : '' }}>INR</option>
                                    <option value="usd" {{ ($draft['salary']['currency'] ?? '') === 'usd' ? 'selected' : '' }}>USD</option>
                                    <option value="eur" {{ ($draft['salary']['currency'] ?? '') === 'eur' ? 'selected' : '' }}>EUR</option>
                                    <option value="gbp" {{ ($draft['salary']['currency'] ?? '') === 'gbp' ? 'selected' : '' }}>GBP</option>
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
                    <button type="button" class="btn btn-outline-secondary" onclick="clearLocalStorageData(); location.reload()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
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
            <form method="POST" action="{{ route('employees.store.bank') }}">
                @csrf
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
                                <input type="text" name="bank_name" class="form-control" placeholder="Enter bank name" pattern="[A-Za-z\s]+" title="Bank name should contain only letters" value="{{ $draft['bank']['bank_name'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="account_number" class="form-label">Account Number</label>
                                <input type="text" name="account_number" class="form-control" placeholder="Enter account number" pattern="[0-9]{9,18}" title="Account number must be 9-18 digits" value="{{ $draft['bank']['account_number'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="ifsc_code" class="form-label">IFSC Code</label>
                                <input type="text" name="ifsc_code" class="form-control" placeholder="Enter IFSC code" pattern="[A-Z]{4}0[A-Z0-9]{6}" title="IFSC format: SBIN0001234" value="{{ $draft['bank']['ifsc_code'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="branch_name" class="form-label">Branch Name</label>
                                <input type="text" name="branch_name" class="form-control" placeholder="Enter branch name" pattern="[A-Za-z\s]+" title="Branch name should contain only letters" value="{{ $draft['bank']['branch_name'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('employees.index') }}'">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearLocalStorageData(); location.reload()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
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
            <form method="POST" action="{{ route('employees.store.documents') }}" enctype="multipart/form-data">
                @csrf
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
                            </div>
                            <div class="col-md-6">
                                <label for="aadhaar_card" class="form-label">Aadhaar Card</label>
                                <input type="file" name="aadhaar_card" class="form-control" accept=".pdf,.jpg,.png,.jpeg">
                            </div>
                            <div class="col-md-6">
                                <label for="pan_card" class="form-label">PAN Card</label>
                                <input type="file" name="pan_card" class="form-control" accept=".pdf,.jpg,.png,.jpeg">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('employees.index') }}'">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearLocalStorageData(); location.reload()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
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
            <form method="POST" action="{{ route('employees.store.emergency') }}">
                @csrf
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
                                <input type="text" name="emergency_contact_name" class="form-control" placeholder="Enter contact name" pattern="[A-Za-z\s]+" title="Contact name should contain only letters" value="{{ $draft['emergency']['emergency_contact_name'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="emergency_contact_relationship" class="form-label">Relationship</label>
                                <select name="emergency_contact_relationship" class="form-select">
                                    <option value="">Select Relationship</option>
                                    <option value="spouse" {{ ($draft['emergency']['emergency_contact_relationship'] ?? '') === 'spouse' ? 'selected' : '' }}>Spouse</option>
                                    <option value="parent" {{ ($draft['emergency']['emergency_contact_relationship'] ?? '') === 'parent' ? 'selected' : '' }}>Parent</option>
                                    <option value="sibling" {{ ($draft['emergency']['emergency_contact_relationship'] ?? '') === 'sibling' ? 'selected' : '' }}>Sibling</option>
                                    <option value="friend" {{ ($draft['emergency']['emergency_contact_relationship'] ?? '') === 'friend' ? 'selected' : '' }}>Friend</option>
                                    <option value="other" {{ ($draft['emergency']['emergency_contact_relationship'] ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="emergency_contact_number" class="form-label">Contact Number</label>
                                <input type="tel" name="emergency_contact_number" class="form-control" placeholder="Enter contact number" pattern="[0-9]{10}" title="Contact number must be 10 digits" value="{{ $draft['emergency']['emergency_contact_number'] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label for="emergency_contact_address" class="form-label">Address</label>
                                <textarea name="emergency_contact_address" class="form-control" placeholder="Enter address" rows="3" maxlength="500">{{ $draft['emergency']['emergency_contact_address'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('employees.index') }}'">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearLocalStorageData(); location.reload()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Save Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/add-employee.js') }}"></script>
<script>
    // LocalStorage key for employee draft data
    const LOCAL_STORAGE_KEY = 'employee_draft_data';

    // Save form data to localStorage
    function saveFormDataToLocalStorage(form, step) {
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        // Get existing data
        let existingData = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY) || '{}');
        existingData[step] = data;
        existingData.lastStep = step;
        existingData.timestamp = new Date().toISOString();

        // Save to localStorage
        localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(existingData));
    }

    // Restore form data from localStorage
    function restoreFormDataFromLocalStorage(step) {
        const data = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY) || '{}');
        if (data[step]) {
            const stepData = data[step];
            for (const [key, value] of Object.entries(stepData)) {
                const input = document.querySelector(`[name="${key}"]`);
                if (input) {
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        if (input.value === value) {
                            input.checked = true;
                        }
                    } else {
                        input.value = value;
                    }
                }
            }
        }
    }

    // Clear localStorage data
    function clearLocalStorageData() {
        localStorage.removeItem(LOCAL_STORAGE_KEY);
    }

    // Check if we're on the credentials page (employee successfully saved)
    if (window.location.pathname.includes('/employees/credentials')) {
        clearLocalStorageData();
    }

    function validatePersonalForm() {
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const gender = document.getElementById('gender').value;
        const dob = document.getElementById('date_of_birth').value;
        const email = document.getElementById('email').value.trim();
        const aadhaar = document.getElementById('aadhaar_number').value.trim();
        const pan = document.getElementById('pan_number').value.trim();
        const mobile = document.getElementById('mobile_number').value.trim();

        // Check if all required fields are filled
        const isValid = firstName && lastName && gender && dob && email && aadhaar && pan && mobile;

        // Validate patterns
        const aadhaarPattern = /^[0-9]{12}$/;
        const panPattern = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
        const mobilePattern = /^[0-9]{10}$/;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        const patternsValid = aadhaarPattern.test(aadhaar) &&
                            panPattern.test(pan.toUpperCase()) &&
                            mobilePattern.test(mobile) &&
                            emailPattern.test(email);

        // Button is always enabled, validation just checks data
        console.log('Form valid:', isValid && patternsValid);
    }

    // Initialize validation and form restoration on page load
    document.addEventListener('DOMContentLoaded', function() {
        validatePersonalForm();

        // If no draft data exists in session, clear localStorage
        @if(!$draft)
            clearLocalStorageData();
        @endif

        // Auto-hide draft alert after 2 seconds
        const draftAlert = document.getElementById('draft-alert');
        if (draftAlert) {
            setTimeout(function() {
                draftAlert.classList.remove('show');
                setTimeout(function() {
                    draftAlert.remove();
                }, 150);
            }, 2000);
        }

        // Restore data based on current step
        const currentStep = '{{ $step }}';
        if (currentStep) {
            restoreFormDataFromLocalStorage(currentStep);
        }

        // Add event listeners to forms to save data on input change
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                saveFormDataToLocalStorage(form, currentStep);
            });

            // Also save on input change for better data retention
            form.addEventListener('input', function(e) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
                    saveFormDataToLocalStorage(form, currentStep);
                }
            });
        });

        // Clear localStorage when cancel button is clicked
        const cancelButtons = document.querySelectorAll('button[onclick*="employees.index"]');
        cancelButtons.forEach(button => {
            button.addEventListener('click', function() {
                clearLocalStorageData();
            });
        });

        // Clear localStorage when Clear Draft button is clicked
        const clearDraftButton = document.querySelector('a[href*="clear=true"]');
        if (clearDraftButton) {
            clearDraftButton.addEventListener('click', function() {
                clearLocalStorageData();
            });
        }
    });
</script>
@endpush
