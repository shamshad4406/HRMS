@extends('layouts.app')

@section('title', 'Leave Calendar - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Leave Calendar</h1>
            <p class="text-muted mb-0">View leave schedule and holidays</p>
        </div>
        <a href="{{ route('leave.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Leave Management
        </a>
    </div>

    <!-- Calendar Controls -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('leave.calendar') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="month" class="form-label">Month:</label>
                    <select id="month" name="month" class="form-select">
                        @for($i = 1; $i <= 12; $i++)
                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
                                {{ $month == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                        </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="year" class="form-label">Year:</label>
                    <select id="year" name="year" class="form-select">
                        <option value="2024" {{ $year == '2024' ? 'selected' : '' }}>2024</option>
                        <option value="2025" {{ $year == '2025' ? 'selected' : '' }}>2025</option>
                        <option value="2026" {{ $year == '2026' ? 'selected' : '' }}>2026</option>
                        <option value="2027" {{ $year == '2027' ? 'selected' : '' }}>2027</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-clockwise me-2"></i>Update Calendar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</h5>
        </div>
        <div class="card-body">
            <div class="cal-grid">
                <!-- Day Headers -->
                <div class="text-center fw-bold text-muted py-2 small">Sun</div>
                <div class="text-center fw-bold text-muted py-2 small">Mon</div>
                <div class="text-center fw-bold text-muted py-2 small">Tue</div>
                <div class="text-center fw-bold text-muted py-2 small">Wed</div>
                <div class="text-center fw-bold text-muted py-2 small">Thu</div>
                <div class="text-center fw-bold text-muted py-2 small">Fri</div>
                <div class="text-center fw-bold text-muted py-2 small">Sat</div>

                @php
                    $firstDay = date('w', mktime(0, 0, 0, $month, 1, $year));
                    $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
                    $today = date('Y-m-d');

                    // Create arrays for leaves and holidays
                    $leaveDates = [];
                    foreach($leaves as $leave) {
                        $period = \Carbon\CarbonPeriod::create($leave->from_date, $leave->to_date);
                        foreach($period as $date) {
                            $dateStr = $date->format('Y-m-d');
                            if(!isset($leaveDates[$dateStr])) {
                                $leaveDates[$dateStr] = [];
                            }
                            $leaveDates[$dateStr][] = $leave;
                        }
                    }

                    $holidayDates = [];
                    foreach($holidays as $holiday) {
                        $holidayDates[$holiday->date->format('Y-m-d')] = $holiday;
                    }
                @endphp

                <!-- Empty cells before first day -->
                @for($i = 0; $i < $firstDay; $i++)
                <div></div>
                @endfor

                <!-- Calendar days -->
                @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateStr = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
                    $isToday = $dateStr === $today;
                    $isWeekend = date('w', mktime(0, 0, 0, $month, $day, $year)) == 0 ||
                                 date('w', mktime(0, 0, 0, $month, $day, $year)) == 6;
                    $hasLeave = isset($leaveDates[$dateStr]);
                    $hasHoliday = isset($holidayDates[$dateStr]);
                @endphp
                <div class="py-2 px-1 border rounded {{ $isToday ? 'border-primary border-2 bg-light' : '' }} {{ $isWeekend ? 'bg-secondary bg-opacity-10' : '' }} {{ $hasLeave ? 'bg-warning bg-opacity-10' : '' }} {{ $hasHoliday ? 'bg-info bg-opacity-10' : '' }}">
                    <div class="fw-bold text-center mb-1">{{ $day }}</div>

                    @if($hasHoliday)
                    <div class="badge bg-primary w-100 mb-1">
                        <small><i class="bi bi-star me-1"></i>{{ $holidayDates[$dateStr]->name }}</small>
                    </div>
                    @endif

                    @if($hasLeave)
                    @foreach($leaveDates[$dateStr] as $leave)
                    <div class="badge bg-warning text-dark w-100 mb-1">
                        @if(auth()->user()->role === 'admin')
                            <small>{{ $leave->personalInformation->first_name }} {{ substr($leave->personalInformation->last_name, 0, 1) }}.</small>
                        @else
                            <small>{{ $leave->leaveType->name }}</small>
                        @endif
                    </div>
                    @endforeach
                    @endif
                </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Legend</h5>
        </div>
        <div class="card-body">
            <div class="d-flex gap-3 gap-md-4 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <div class="cal-legend-box border-2 border-primary"></div>
                    <span>Today</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="cal-legend-box" style="background:#e2e8f0;"></div>
                    <span>Weekend</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="cal-legend-box" style="background:#fef3c7;"></div>
                    <span>Leave Day</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="cal-legend-box" style="background:#cffafe;"></div>
                    <span>Holiday</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Events -->
    @if($holidays->count() > 0)
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Holidays in {{ date('F', mktime(0, 0, 0, $month, 1)) }}</h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-column gap-3">
                @foreach($holidays as $holiday)
                <div class="d-flex gap-3 p-3 bg-light rounded border-start border-4 border-primary">
                    <div class="text-center flex-shrink-0" style="min-width: 56px;">
                        <div class="display-6 fw-bold text-primary">{{ $holiday->date->format('d') }}</div>
                        <div class="text-uppercase text-muted small">{{ $holiday->date->format('M') }}</div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $holiday->name }}</h6>
                        <p class="mb-1 text-muted small">{{ ucfirst($holiday->type) }}</p>
                        @if($holiday->description)
                        <p class="mb-0 text-muted small fst-italic">{{ $holiday->description }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
@endsection
