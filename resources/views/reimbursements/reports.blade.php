@extends('layouts.app')

@section('title', 'Reimbursement Reports - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Reimbursement Reports</h1>
            <p class="text-muted mb-0">{{ $types[$type] }}</p>
        </div>
        <a href="{{ route('reimbursements.all') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>All Claims
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reimbursements.reports') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Report</label>
                    <select name="type" class="form-select">
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ $type === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ $type === 'payment' ? 'Paid from' : 'Expense from' }}</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ $type === 'payment' ? 'Paid to' : 'Expense to' }}</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select">
                        <option value="">All employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (string) ($filters['employee_id'] ?? '') === (string) $employee->id ? 'selected' : '' }}>
                                {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) ($filters['category_id'] ?? '') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!in_array($type, ['pending', 'payment']))
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            @foreach(['pending', 'approved', 'rejected', 'paid'] as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Show</button>
                    <button type="submit" name="export" value="csv" class="btn btn-outline-success"><i class="bi bi-download me-1"></i>CSV</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Claims</small><h4 class="mb-0">{{ $totals['count'] }}</h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Total amount</small><h4 class="mb-0">₹{{ number_format($totals['amount'], 2) }}</h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Paid</small><h4 class="mb-0 text-success">₹{{ number_format($totals['paid'], 2) }}</h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Pending approval</small><h4 class="mb-0 text-warning">₹{{ number_format($totals['pending'], 2) }}</h4></div></div></div>
    </div>

    @if(count($summary) > 0)
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Summary</h5></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ $type === 'employee' ? 'Employee' : ($type === 'category' ? 'Category' : 'Month') }}</th>
                            <th class="text-end">Claims</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Pending</th>
                            <th class="text-end">Approved</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Rejected</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td class="text-end">{{ $row['count'] }}</td>
                                <td class="text-end">₹{{ number_format($row['total'], 2) }}</td>
                                <td class="text-end">₹{{ number_format($row['pending'], 2) }}</td>
                                <td class="text-end">₹{{ number_format($row['approved'], 2) }}</td>
                                <td class="text-end">₹{{ number_format($row['paid'], 2) }}</td>
                                <td class="text-end">₹{{ number_format($row['rejected'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Claims</h5></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee</th>
                        <th>Category</th>
                        <th>Expense date</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($claims as $claim)
                        <tr>
                            <td>{{ $claim->id }}</td>
                            <td>{{ $claim->personalInformation->first_name ?? '' }} {{ $claim->personalInformation->last_name ?? '' }}</td>
                            <td>{{ $claim->category->name ?? '-' }}</td>
                            <td>{{ optional($claim->expense_date)->format('d M Y') }}</td>
                            <td class="text-end">₹{{ number_format($claim->amount, 2) }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($claim->status) }}</span></td>
                            <td>
                                @if($claim->status === 'paid')
                                    {{ optional($claim->payment_date)->format('d M Y') }}
                                    <small class="text-muted d-block">{{ str_replace('_', ' ', $claim->payment_method) }}{{ $claim->payment_reference ? ' - ' . $claim->payment_reference : '' }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td><a href="{{ route('reimbursements.show', $claim->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No claims match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
