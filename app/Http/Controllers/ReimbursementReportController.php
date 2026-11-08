<?php

namespace App\Http\Controllers;

use App\Models\PersonalInformation;
use App\Models\ReimbursementCategory;
use App\Models\ReimbursementClaim;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Reimbursement reports (admin only), as listed in REIMBURSEMENT_SYSTEM_PLAN.md section 10:
 * employee-wise, category-wise, monthly, pending claims and payment report. CSV export included.
 */
class ReimbursementReportController extends Controller
{
    private const TYPES = [
        'monthly' => 'Monthly report',
        'employee' => 'Employee-wise report',
        'category' => 'Category-wise report',
        'pending' => 'Pending claims report',
        'payment' => 'Payment report',
    ];

    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $filters = $request->validate([
            'type' => 'nullable|in:' . implode(',', array_keys(self::TYPES)),
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'employee_id' => 'nullable|integer|exists:personal_information,id',
            'category_id' => 'nullable|integer|exists:reimbursement_categories,id',
            'status' => 'nullable|in:pending,approved,rejected,paid',
            'export' => 'nullable|in:csv',
        ]);

        $type = $filters['type'] ?? 'monthly';

        // Monthly report defaults to the current month
        $from = $filters['from'] ?? ($type === 'monthly' ? Carbon::now()->startOfMonth()->format('Y-m-d') : null);
        $to = $filters['to'] ?? ($type === 'monthly' ? Carbon::now()->endOfMonth()->format('Y-m-d') : null);

        $query = ReimbursementClaim::with(['category', 'personalInformation', 'approvedBy', 'processedBy']);

        // The payment report is about the payment date, every other report about the expense date
        $dateColumn = $type === 'payment' ? 'payment_date' : 'expense_date';
        if ($from) {
            $query->whereDate($dateColumn, '>=', $from);
        }
        if ($to) {
            $query->whereDate($dateColumn, '<=', $to);
        }
        if (!empty($filters['employee_id'])) {
            $query->where('personal_information_id', $filters['employee_id']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if ($type === 'pending') {
            $query->where('status', 'pending');
        } elseif ($type === 'payment') {
            $query->where('status', 'paid');
        } elseif (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $claims = $query->orderBy($dateColumn, 'desc')->orderBy('id', 'desc')->get();

        if (($filters['export'] ?? null) === 'csv') {
            return $this->exportCsv($claims, $type);
        }

        return view('reimbursements.reports', [
            'types' => self::TYPES,
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'filters' => $filters,
            'claims' => $claims,
            'summary' => $this->summarise($claims, $type),
            'totals' => [
                'count' => $claims->count(),
                'amount' => (float) $claims->sum('amount'),
                'paid' => (float) $claims->where('status', 'paid')->sum('amount'),
                'pending' => (float) $claims->where('status', 'pending')->sum('amount'),
            ],
            'employees' => PersonalInformation::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'employee_code']),
            'categories' => ReimbursementCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** Group totals for the employee / category / monthly reports. */
    private function summarise($claims, string $type): array
    {
        $groupKey = match ($type) {
            'employee' => fn ($c) => $c->personal_information_id,
            'category' => fn ($c) => $c->category_id,
            'monthly' => fn ($c) => $c->expense_date->format('Y-m'),
            default => null,
        };

        if (!$groupKey) {
            return [];
        }

        return $claims->groupBy($groupKey)->map(function ($group) use ($type) {
            $first = $group->first();

            $label = match ($type) {
                'employee' => trim(($first->personalInformation->first_name ?? '') . ' ' . ($first->personalInformation->last_name ?? '')) . ' (' . ($first->personalInformation->employee_code ?? '-') . ')',
                'category' => $first->category->name ?? '-',
                default => $first->expense_date->format('F Y'),
            };

            return [
                'label' => $label,
                'count' => $group->count(),
                'total' => (float) $group->sum('amount'),
                'approved' => (float) $group->where('status', 'approved')->sum('amount'),
                'paid' => (float) $group->where('status', 'paid')->sum('amount'),
                'pending' => (float) $group->where('status', 'pending')->sum('amount'),
                'rejected' => (float) $group->where('status', 'rejected')->sum('amount'),
            ];
        })->values()->all();
    }

    private function exportCsv($claims, string $type)
    {
        $filename = 'reimbursement_' . $type . '_report_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($claims) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Claim ID', 'Employee', 'Employee code', 'Category', 'Expense date', 'Claim date', 'Amount', 'Status', 'Approved by', 'Payment date', 'Payment method', 'Payment reference', 'Description']);

            foreach ($claims as $claim) {
                fputcsv($out, array_map([$this, 'safeCell'], [
                    $claim->id,
                    trim(($claim->personalInformation->first_name ?? '') . ' ' . ($claim->personalInformation->last_name ?? '')),
                    $claim->personalInformation->employee_code ?? '',
                    $claim->category->name ?? '',
                    optional($claim->expense_date)->format('Y-m-d'),
                    optional($claim->claim_date)->format('Y-m-d'),
                    number_format((float) $claim->amount, 2, '.', ''),
                    $claim->status,
                    $claim->approvedBy->name ?? '',
                    optional($claim->payment_date)->format('Y-m-d'),
                    $claim->payment_method,
                    $claim->payment_reference,
                    $claim->description,
                ]));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Stop spreadsheet formula injection: text starting with = + - @ is prefixed with an apostrophe. */
    private function safeCell($value)
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }
}
