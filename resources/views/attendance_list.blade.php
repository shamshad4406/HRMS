@extends('layouts.app')

@section('title', 'Attendance List - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Attendance List</h1>
            <p class="text-muted mb-0">View and manage employee attendance records</p>
        </div>
        <a href="{{ route('attendances.create') }}" class="btn btn-primary">
            <i class="bi bi-calendar-plus me-2"></i>Mark Attendance
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Total Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances ?? [] as $attendance)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($attendance->personalInformation && $attendance->personalInformation->photo)
                                        <img src="{{ asset('storage/' . $attendance->personalInformation->photo) }}" class="avatar" alt="{{ $attendance->personalInformation->first_name }}">
                                    @else
                                        <div class="avatar-placeholder">
                                            {{ substr($attendance->personalInformation->first_name ?? 'U', 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $attendance->personalInformation->first_name ?? 'N/A' }} {{ $attendance->personalInformation->last_name ?? '' }}</div>
                                        <small class="text-muted">{{ $attendance->personalInformation->employee_code ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d-m-Y') }}</td>
                            <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : 'N/A' }}</td>
                            <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : 'N/A' }}</td>
                            <td>
                                @if($attendance->check_in && $attendance->check_out)
                                    <?php
                                        $checkIn = \Carbon\Carbon::parse($attendance->check_in);
                                        $checkOut = \Carbon\Carbon::parse($attendance->check_out);
                                        $duration = $checkIn->diff($checkOut);
                                        echo $duration->format('%h hours %i minutes');
                                    ?>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($attendance->check_out)
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-warning text-dark">Checked In</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    @if(!$attendance->check_out)
                                        <form action="{{ route('attendances.checkout', $attendance->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Check out this employee?')">
                                                <i class="bi bi-box-arrow-right me-1"></i>Check Out
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('attendances.destroy', $attendance->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($attendances->count() == 0)
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No attendance records found</p>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
