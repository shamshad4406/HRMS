@extends('layouts.app')

@section('title', 'Leave Settings - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Leave Settings</h1>
            <p class="text-muted mb-0">Configure leave types, holidays, and balances</p>
        </div>
        <a href="{{ route('leave.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Leave Management
        </a>
    </div>

    <!-- Settings Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#leave-types">Leave Types</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#holidays">Holidays</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#balances">Leave Balances</button>
        </li>
    </ul>

    <!-- Leave Types Tab -->
    <div class="tab-content">
        <div class="tab-pane fade show active" id="leave-types">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Leave Types Management</h5>
                    <button class="btn btn-primary btn-sm" onclick="showLeaveTypeForm()">
                        <i class="bi bi-plus me-1"></i>Add Leave Type
                    </button>
                </div>
                <div class="card-body">
                    <!-- Add Leave Type Form -->
                    <div id="leave-type-form" class="bg-light p-3 rounded mb-3" style="display: none;">
                        <h6 class="mb-3" id="form-title">Add New Leave Type</h6>
                        <form method="POST" action="{{ route('leave.store.leave.type') }}" id="leave-type-form-element">
                            @csrf
                            <input type="hidden" name="leave_type_id" id="leave_type_id" value="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Leave Type Name *</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="code" class="form-label">Code *</label>
                                    <input type="text" id="code" name="code" class="form-control" placeholder="e.g., CL, SL, EL" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="days_per_year" class="form-label">Days Per Year *</label>
                                    <input type="number" id="days_per_year" name="days_per_year" class="form-control" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="max_carry_forward_days" class="form-label">Max Carry Forward Days</label>
                                    <input type="number" id="max_carry_forward_days" name="max_carry_forward_days" class="form-control" min="0" step="0.5">
                                </div>
                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_paid" value="1" id="is_paid" checked>
                                                <label class="form-check-label" for="is_paid">Paid Leave</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="requires_approval" value="1" id="requires_approval" checked>
                                                <label class="form-check-label" for="requires_approval">Requires Approval</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="requires_document" value="1" id="requires_document">
                                                <label class="form-check-label" for="requires_document">Requires Document</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_carry_forward" value="1" id="is_carry_forward">
                                                <label class="form-check-label" for="is_carry_forward">Allow Carry Forward</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_encashable" value="1" id="is_encashable">
                                                <label class="form-check-label" for="is_encashable">Allow Encashment</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Save Leave Type
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="hideLeaveTypeForm()">
                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Leave Types List -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Days/Year</th>
                                    <th>Paid</th>
                                    <th>Approval</th>
                                    <th>Document</th>
                                    <th>Carry Forward</th>
                                    <th>Encashable</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaveTypes as $type)
                                <tr>
                                    <td><strong>{{ $type->name }}</strong></td>
                                    <td>{{ $type->code }}</td>
                                    <td>{{ $type->days_per_year }}</td>
                                    <td>{{ $type->is_paid ? 'Yes' : 'No' }}</td>
                                    <td>{{ $type->requires_approval ? 'Yes' : 'No' }}</td>
                                    <td>{{ $type->requires_document ? 'Yes' : 'No' }}</td>
                                    <td>{{ $type->is_carry_forward ? 'Yes (' . $type->max_carry_forward_days . ')' : 'No' }}</td>
                                    <td>{{ $type->is_encashable ? 'Yes' : 'No' }}</td>
                                    <td>
                                        <span class="badge {{ $type->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $type->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editLeaveType({{ $type->id }})" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteLeaveType({{ $type->id }})" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No leave types configured yet.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Holidays Tab -->
        <div class="tab-pane fade" id="holidays">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Holidays Management</h5>
                    <button class="btn btn-primary btn-sm" onclick="showHolidayForm()">
                        <i class="bi bi-plus me-1"></i>Add Holiday
                    </button>
                </div>
                <div class="card-body">
                    <!-- Add Holiday Form -->
                    <div id="holiday-form" class="bg-light p-3 rounded mb-3" style="display: none;">
                        <h6 class="mb-3">Add New Holiday</h6>
                        <form method="POST" action="{{ route('leave.store.holiday') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="holiday_name" class="form-label">Holiday Name *</label>
                                    <input type="text" id="holiday_name" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="holiday_date" class="form-label">Date *</label>
                                    <input type="date" id="holiday_date" name="date" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="holiday_type" class="form-label">Holiday Type *</label>
                                    <select id="holiday_type" name="type" class="form-select" required>
                                        <option value="public_holiday">Public Holiday</option>
                                        <option value="company_holiday">Company Holiday</option>
                                        <option value="optional_holiday">Optional Holiday</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="is_recurring">
                                        <label class="form-check-label" for="is_recurring">Recurring Holiday (Every Year)</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="holiday_description" class="form-label">Description</label>
                                    <textarea id="holiday_description" name="description" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Save Holiday
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="hideHolidayForm()">
                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Holidays List -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Recurring</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($holidays as $holiday)
                                <tr>
                                    <td><strong>{{ $holiday->date->format('M d, Y') }}</strong></td>
                                    <td>{{ $holiday->name }}</td>
                                    <td>
                                        <span class="badge bg-info text-dark">{{ ucfirst(str_replace('_', ' ', $holiday->type)) }}</span>
                                    </td>
                                    <td>{{ $holiday->description ?? '-' }}</td>
                                    <td>{{ $holiday->is_recurring ? 'Yes' : 'No' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No holidays configured yet.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leave Balances Tab -->
        <div class="tab-pane fade" id="balances">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-balance-scale me-2"></i>Leave Balances Initialization</h5>
                    <form method="POST" action="{{ route('leave.initialize.balances') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary" onclick="return confirm('This will initialize leave balances for all employees based on configured leave types. Continue?')">
                            <i class="bi bi-gear me-1"></i>Initialize Balances for {{ date('Y') }}
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <h6 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Important Information</h6>
                        <ul class="mb-0 ps-3">
                            <li>This will create leave balance records for all employees for the current year.</li>
                            <li>Existing balances will not be overwritten.</li>
                            <li>Leave balances are calculated based on the configured leave types.</li>
                            <li>Run this initialization at the beginning of each year or when adding new employees.</li>
                        </ul>
                    </div>

                    <div class="alert alert-secondary mb-0">
                        <h6 class="alert-heading"><i class="bi bi-list-ol me-2"></i>Initialization Process</h6>
                        <ol class="mb-0 ps-3">
                            <li>System retrieves all active employees from the database.</li>
                            <li>For each employee, creates balance records for all active leave types.</li>
                            <li>Sets total days and remaining days based on leave type configuration.</li>
                            <li>Initializes used days and carried forward days to zero.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showLeaveTypeForm() {
        document.getElementById('leave-type-form').style.display = 'block';
        document.getElementById('form-title').textContent = 'Add New Leave Type';
        document.getElementById('leave-type-form-element').action = '{{ route('leave.store.leave.type') }}';
        document.getElementById('leave_type_id').value = '';
        document.getElementById('leave-type-form-element').reset();

        // Remove _method input if exists
        let methodInput = document.getElementById('_method');
        if (methodInput) {
            methodInput.remove();
        }
    }

    function hideLeaveTypeForm() {
        document.getElementById('leave-type-form').style.display = 'none';
    }

    function editLeaveType(id) {
        // Fetch leave type data via AJAX
        fetch(`{{ route('leave.get.leave.type', ':id') }}`.replace(':id', id))
            .then(response => response.json())
            .then(data => {
                // Populate form with data
                document.getElementById('leave_type_id').value = data.id;
                document.getElementById('name').value = data.name;
                document.getElementById('code').value = data.code;
                document.getElementById('days_per_year').value = data.days_per_year;
                document.getElementById('max_carry_forward_days').value = data.max_carry_forward_days;
                document.getElementById('description').value = data.description || '';
                document.getElementById('is_paid').checked = data.is_paid == 1;
                document.getElementById('requires_approval').checked = data.requires_approval == 1;
                document.getElementById('requires_document').checked = data.requires_document == 1;
                document.getElementById('is_carry_forward').checked = data.is_carry_forward == 1;
                document.getElementById('is_encashable').checked = data.is_encashable == 1;

                // Update form title and action
                document.getElementById('form-title').textContent = 'Edit Leave Type';
                document.getElementById('leave-type-form-element').action = `{{ route('leave.update.leave.type', ':id') }}`.replace(':id', id);
                document.getElementById('leave-type-form-element').method = 'POST';

                // Add hidden method override for PUT
                let methodInput = document.getElementById('_method');
                if (!methodInput) {
                    methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.id = '_method';
                    methodInput.value = 'PUT';
                    document.getElementById('leave-type-form-element').appendChild(methodInput);
                } else {
                    methodInput.value = 'PUT';
                }

                // Show form
                document.getElementById('leave-type-form').style.display = 'block';
            })
            .catch(error => console.error('Error:', error));
    }

    function deleteLeaveType(id) {
        if (confirm('Are you sure you want to delete this leave type?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('leave.delete.leave.type', ':id') }}`.replace(':id', id);
            form.innerHTML = '<input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function showHolidayForm() {
        document.getElementById('holiday-form').style.display = 'block';
    }

    function hideHolidayForm() {
        document.getElementById('holiday-form').style.display = 'none';
    }
</script>
@endpush
