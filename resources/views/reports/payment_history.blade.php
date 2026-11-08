@extends('layouts.app')

@section('title', 'Payment History Report - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1>Payment History Report</h1>
            <p class="text-muted mb-0">Payment transaction details</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Reports
        </a>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.payment.history') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                </div>
                @if(auth()->user()->role === 'admin')
                <div class="col-md-3">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="">All Methods</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="cheque" {{ request('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="other" {{ request('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-2"></i>Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Amount Paid</h6>
                    <h3 class="mb-0 text-success">₹{{ number_format($totalAmountPaid, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Transactions</h6>
                    <h3 class="mb-0">{{ $payments->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods Summary -->
    @if($paymentMethods->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Payment Methods Summary</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Payment Method</th>
                            <th>Transaction Count</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentMethods as $method => $data)
                            <tr>
                                <td><strong>{{ ucfirst($method) }}</strong></td>
                                <td>{{ $data['count'] }}</td>
                                <td>₹{{ number_format($data['total'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Payment History Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Payment History Details</h5>
                <small class="text-muted">Period: {{ $startDate }} to {{ $endDate }}</small>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="bi bi-printer me-2"></i>Print
                </button>
                <button onclick="exportToCSV()" class="btn btn-success">
                    <i class="bi bi-download me-2"></i>Export CSV
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="paymentTable">
                    <thead class="table-light">
                        <tr>
                            <th>Payment Date</th>
                            <th>Employee Name</th>
                            <th>Payment Mode</th>
                            <th>Transaction Reference</th>
                            <th>Amount Paid</th>
                            <th>Processed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                                <td>
                                    @if($payment->payroll && $payment->payroll->personalInformation)
                                        {{ $payment->payroll->personalInformation->first_name }} {{ $payment->payroll->personalInformation->last_name }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td><span class="badge bg-info">{{ ucfirst($payment->payment_method) }}</span></td>
                                <td>{{ $payment->payment_reference ?? 'N/A' }}</td>
                                <td><strong>₹{{ number_format($payment->amount, 2) }}</strong></td>
                                <td>
                                    @if($payment->processedBy)
                                        {{ $payment->processedBy->name ?? 'Admin' }}
                                    @else
                                        System
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No payment records found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function exportToCSV() {
        const table = document.getElementById('paymentTable');
        const rows = table.querySelectorAll('tr');

        let csv = [];
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cols = row.querySelectorAll('th, td');
            let rowData = [];
            for (let j = 0; j < cols.length; j++) {
                let text = cols[j].innerText.replace(/₹/g, '').replace(/,/g, '').trim();
                rowData.push('"' + text + '"');
            }
            csv.push(rowData.join(','));
        }

        const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
        const downloadLink = document.createElement('a');
        downloadLink.download = 'payment_history_{{ $startDate }}_to_{{ $endDate }}.csv';
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
</script>
@endpush
