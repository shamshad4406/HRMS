@extends('layouts.app')

@section('title', 'Employee List - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Employee List</h1>
            <p class="text-muted mb-0">Manage and view all employees</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Search -->
            <form method="GET" action="{{ route('employees.index') }}" class="d-flex">
                <div class="input-group">
                
                    <input
                        type="text"
                        class="form-control search-input"
                        name="search"
                        placeholder="Search by INN00001, Name, Email..."
                        value="{{ $search ?? '' }}"
                       
                    >
                    @if($search ?? null)
                        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary">
                         <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('employees.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-2"></i>Add Employee
                </a>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Emp Code</th>
                            <th>Employee</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees ?? [] as $employee)
                        <tr>
                            <td>
                                @if($employee->photo)
                                    <img src="{{ asset('storage/' . $employee->photo) }}" class="avatar" alt="{{ $employee->first_name }} {{ $employee->last_name }}">
                                @else
                                    <div class="avatar-placeholder">
                                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                    </div>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $employee->employee_code }}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <div class="fw-semibold">{{ $employee->first_name }} {{ $employee->last_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->employmentDetail->department ?? 'N/A' }}</td>
                            <td>{{ $employee->employmentDetail->designation ?? 'N/A' }}</td>
                            <td>
                                @if($employee->employmentDetail && $employee->employmentDetail->status)
                                    @if($employee->employmentDetail->status == 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if(auth()->user()->role === 'admin')
                                        @if($employee->employmentDetail)
                                            <form action="{{ route('employees.toggle.status', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to {{ $employee->employmentDetail->status == 'active' ? 'deactivate' : 'activate' }} this employee?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $employee->employmentDetail->status == 'active' ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $employee->employmentDetail->status == 'active' ? 'Deactivate' : 'Activate' }}">
                                                    <i class="bi {{ $employee->employmentDetail->status == 'active' ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this employee?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($employees->count() == 0)
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">
                                    @if($search ?? null)
                                        No employees found matching "{{ $search }}"
                                    @else
                                        No employees found
                                    @endif
                                </p>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($employees->hasPages())
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-end mb-0">
                        {{-- Previous Page Link --}}
                        @if($employees->onFirstPage())
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $employees->appends(['search' => $search ?? ''])->previousPageUrl() }}">Previous</a>
                            </li>
                        @endif

                        {{-- Page Numbers --}}
                        @for($i = 1; $i <= $employees->lastPage(); $i++)
                            @if($i == $employees->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <a class="page-link" href="#">{{ $i }}</a>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $employees->appends(['search' => $search ?? ''])->url($i) }}">{{ $i }}</a>
                                </li>
                            @endif
                        @endfor

                        {{-- Next Page Link --}}
                        @if($employees->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $employees->appends(['search' => $search ?? ''])->nextPageUrl() }}">Next</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Next</a>
                            </li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    </div>
@endsection
