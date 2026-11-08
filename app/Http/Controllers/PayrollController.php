<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\PersonalInformation;
use App\Models\PayrollDeductionDetail;
use App\Models\PayrollPayment;
use App\Models\SalaryDetail;
use App\Models\PayrollSetting;
use App\Services\PayrollCalculator;
use App\Models\Notification;
use App\Http\Controllers\NotificationController;

class PayrollController extends Controller
{
    /**
     * Display a listing of payrolls.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        
        // Role-based access control
        if ($user->role === 'admin') {
            $payrolls = Payroll::with('personalInformation')->orderBy('created_at', 'desc')->paginate(10);
        } else {
            // HR and Employee can only see their own payroll data
            if ($user->personal_information_id) {
                $payrolls = Payroll::with('personalInformation')
                    ->where('personal_information_id', $user->personal_information_id)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
            } else {
                // User not linked to any employee record
                $payrolls = Payroll::where('id', 0)->paginate(10); // Empty result
            }
        }
        
        return view('payroll_list', compact('payrolls'));
    }

    /**
     * Show the form for creating a new payroll.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $employees = PersonalInformation::with('salaryDetail')->get();
        return view('add_payroll', compact('employees'));
    }

    /**
     * Store a newly created payroll in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'personal_information_id' => 'required|exists:personal_information,id',
            'month' => 'required|string|max:20',
            'year' => 'required|digits:4',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'payment_date' => 'nullable|date',
            'status' => 'required|in:pending,processed,paid',
        ]);

        $validated['month'] = strtolower($validated['month']);

        // One payroll per employee per month
        if (Payroll::where('personal_information_id', $validated['personal_information_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->exists()) {
            return redirect()->back()->withInput()->with('error', 'A payroll already exists for this employee for the selected month and year.');
        }

        $netSalary = $validated['basic_salary'] + ($validated['allowances'] ?? 0) - ($validated['deductions'] ?? 0);

        Payroll::create([
            'personal_information_id' => $validated['personal_information_id'],
            'month' => $validated['month'],
            'year' => $validated['year'],
            'basic_salary' => $validated['basic_salary'],
            'allowances' => $validated['allowances'] ?? 0,
            'deductions' => $validated['deductions'] ?? 0,
            'net_salary' => $netSalary,
            'payment_date' => $validated['payment_date'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('payrolls.index')->with('success', 'Payroll created successfully!');
    }

    /**
     * Remove the specified payroll from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $payroll = Payroll::find($id);
        if ($payroll) {
            // Approved / paid payrolls are financial records and must not be deleted
            if ($payroll->approval_status === 'approved' || $payroll->status === 'paid') {
                return redirect()->route('payrolls.index')->with('error', 'Approved or paid payrolls cannot be deleted.');
            }
            $payroll->delete();
            return redirect()->route('payrolls.index')->with('success', 'Payroll deleted successfully!');
        }
        return redirect()->route('payrolls.index')->with('error', 'Payroll not found.');
    }

    /**
     * Show form to generate salary for a specific month/year.
     *
     * @return \Illuminate\View\View
     */
    public function showGenerateSalary()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $employees = PersonalInformation::with('salaryDetail')->get();
        return view('payroll.generate_salary', compact('employees'));
    }

    /**
     * Generate salary for selected employees.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateSalary(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'month' => 'required|string',
            'year' => 'required|string',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:personal_information,id',
            'include_reimbursements' => 'nullable|boolean',
            'auto_calculate' => 'nullable|boolean',
        ]);

        $includeReimbursements = $request->boolean('include_reimbursements');
        $autoCalculate = $request->boolean('auto_calculate');
        $generatedCount = 0;
        foreach ($validated['employee_ids'] as $employeeId) {
            $employee = PersonalInformation::with('salaryDetail')->find($employeeId);
            if ($employee && $employee->salaryDetail) {
                // Check if payroll already exists for this employee/month/year
                $existingPayroll = Payroll::where('personal_information_id', $employeeId)
                    ->where('month', $validated['month'])
                    ->where('year', $validated['year'])
                    ->first();

                if (!$existingPayroll) {
                    $payroll = \Illuminate\Support\Facades\DB::transaction(function () use ($employee, $employeeId, $validated, $includeReimbursements, $autoCalculate) {
                        // Approved claims that are not paid yet and not in another payroll
                        $claims = $includeReimbursements
                            ? \App\Models\ReimbursementClaim::where('personal_information_id', $employeeId)
                                ->where('status', 'approved')
                                ->whereNull('payroll_id')
                                ->lockForUpdate()
                                ->get()
                            : collect();

                        $reimbursementTotal = (float) $claims->sum('amount');
                        $basic = (float) $employee->salaryDetail->basic_salary;

                        // Allowances from the salary structure + statutory and attendance/leave deductions
                        $auto = $autoCalculate
                            ? app(PayrollCalculator::class)->calculate($employee, $employee->salaryDetail, $validated['month'], $validated['year'])
                            : ['allowances' => 0.0, 'lines' => [], 'total_deductions' => 0.0];

                        $allowances = $auto['allowances'] + $reimbursementTotal; // reimbursements are paid as part of allowances
                        $deductions = $auto['total_deductions'];
                        $net = max(0, $basic + $allowances - $deductions);

                        $created = Payroll::create([
                            'personal_information_id' => $employeeId,
                            'month' => $validated['month'],
                            'year' => $validated['year'],
                            'basic_salary' => $basic,
                            'allowances' => $allowances,
                            'deductions' => $deductions,
                            'net_salary' => $net,
                            'status' => 'pending',
                            'approval_status' => 'pending',
                            'generated_at' => now(),
                        ]);

                        foreach ($auto['lines'] as $line) {
                            PayrollDeductionDetail::create([
                                'payroll_id' => $created->id,
                                'deduction_type' => $line['type'],
                                'amount' => $line['amount'],
                                'description' => $line['description'],
                                'status' => 'applied',
                            ]);
                        }

                        // Link the claims so they are not picked up again
                        if ($claims->isNotEmpty()) {
                            \App\Models\ReimbursementClaim::whereIn('id', $claims->pluck('id'))
                                ->update(['payroll_id' => $created->id]);
                        }

                        return $created;
                    });
                    $generatedCount++;

                    // Create notification for admin users
                    NotificationController::createForAdmins(
                        'payroll_generated',
                        'Payroll Generated',
                        "Payroll for {$employee->first_name} {$employee->last_name} for {$validated['month']} {$validated['year']} has been generated and is pending approval.",
                        route('payrolls.approve', $payroll->id),
                        ['payroll_id' => $payroll->id, 'employee_id' => $employeeId]
                    );
                }
            }
        }

        return redirect()->route('payrolls.index')->with('success', "Generated {$generatedCount} payroll records successfully!");
    }

    /**
     * Show form to review deductions for a payroll.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function showReviewDeductions($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $payroll = Payroll::with(['personalInformation', 'deductionDetails'])->findOrFail($id);
        return view('payroll.review_deductions', compact('payroll'));
    }

    /**
     * Store/update deductions for a payroll.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDeductions(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'deductions' => 'required|array',
            'deductions.*.type' => 'required|in:tax,pf,esi,leave,loan,advance,other',
            'deductions.*.amount' => 'required|numeric|min:0',
            'deductions.*.description' => 'nullable|string',
            'deductions.*.status' => 'nullable|in:applied,pending,waived',
        ]);

        $payroll = Payroll::findOrFail($id);

        if ($payroll->approval_status === 'approved' || $payroll->status === 'paid') {
            return redirect()->route('payrolls.index')->with('error', 'Deductions cannot be changed after a payroll is approved or paid.');
        }

        // Clear existing deductions
        $payroll->deductionDetails()->delete();

        // Add new deductions
        $totalDeductions = 0;
        foreach ($validated['deductions'] as $deduction) {
            if ($deduction['amount'] > 0) {
                PayrollDeductionDetail::create([
                    'payroll_id' => $payroll->id,
                    'deduction_type' => $deduction['type'],
                    'amount' => $deduction['amount'],
                    'description' => $deduction['description'] ?? null,
                    'status' => $deduction['status'] ?? 'applied',
                ]);
                if (($deduction['status'] ?? 'applied') === 'applied') {
                    $totalDeductions += $deduction['amount'];
                }
            }
        }

        // Update payroll
        $payroll->deductions = $totalDeductions;
        $payroll->net_salary = max(0, $payroll->basic_salary + $payroll->allowances - $totalDeductions);
        $payroll->status = 'processed';
        $payroll->save();

        return redirect()->route('payrolls.approve', $payroll->id)->with('success', 'Deductions updated successfully!');
    }

    /**
     * Show form to approve payroll.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function showApprovePayroll($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $payroll = Payroll::with(['personalInformation', 'deductionDetails'])->findOrFail($id);
        return view('payroll.approve', compact('payroll'));
    }

    /**
     * Approve or reject payroll.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approvePayroll(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string',
        ]);

        $payroll = Payroll::findOrFail($id);

        if ($payroll->approval_status !== 'pending' || $payroll->status === 'paid') {
            return redirect()->route('payrolls.index')->with('error', 'This payroll has already been processed and cannot be approved or rejected again.');
        }

        if ($validated['action'] === 'approve') {
            $payroll->approval_status = 'approved';
            $payroll->approved_by = auth()->id();
            $payroll->approved_at = now();
            $payroll->status = 'processed';
            $message = 'Payroll approved successfully!';
        } else {
            $payroll->approval_status = 'rejected';
            $payroll->status = 'pending';
            // A rejected payroll is never paid: free its reimbursement claims for the next payroll
            \App\Models\ReimbursementClaim::where('payroll_id', $payroll->id)->update(['payroll_id' => null]);
            $message = 'Payroll rejected.';
        }

        $payroll->save();

        return redirect()->route('payrolls.index')->with('success', $message);
    }

    /**
     * Show form to record payment.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function showRecordPayment($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $payroll = Payroll::with(['personalInformation', 'payments'])->findOrFail($id);
        return view('payroll.record_payment', compact('payroll'));
    }

    /**
     * Record payment for payroll.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recordPayment(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'payment_method' => 'required|in:bank_transfer,cash,cheque,other',
            'payment_reference' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'notes' => 'nullable|string',
        ]);

        $error = null;

        \Illuminate\Support\Facades\DB::transaction(function () use ($id, $validated, &$error) {
            // Lock the row so two admins cannot record the same payment twice
            $payroll = Payroll::lockForUpdate()->findOrFail($id);

            if ($payroll->approval_status !== 'approved') {
                $error = 'Only approved payrolls can be paid.';
                return;
            }

            if ($payroll->status === 'paid') {
                $error = 'This payroll is already fully paid.';
                return;
            }

            $alreadyPaid = (float) PayrollPayment::where('payroll_id', $payroll->id)->sum('amount');
            $remaining = round((float) $payroll->net_salary - $alreadyPaid, 2);

            if ((float) $validated['amount'] > $remaining + 0.001) {
                $error = 'Amount exceeds the remaining payable balance of ' . number_format($remaining, 2) . '.';
                return;
            }

            PayrollPayment::create([
                'payroll_id' => $payroll->id,
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'] ?? null,
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'notes' => $validated['notes'] ?? null,
                'processed_by' => auth()->id(),
            ]);

            // Mark as paid once the cumulative payments cover the net salary
            if (($alreadyPaid + (float) $validated['amount']) >= (float) $payroll->net_salary - 0.001) {
                $payroll->status = 'paid';
                $payroll->payment_date = $validated['payment_date'];
                $payroll->save();

                // Reimbursements included in this payroll are now paid too
                $claims = \App\Models\ReimbursementClaim::with('category')
                    ->where('payroll_id', $payroll->id)
                    ->where('status', 'approved')
                    ->get();

                $employeeUser = $claims->isNotEmpty()
                    ? \App\Models\User::where('personal_information_id', $payroll->personal_information_id)->first()
                    : null;

                foreach ($claims as $claim) {
                    $claim->status = 'paid';
                    $claim->payment_method = $validated['payment_method'];
                    $claim->payment_date = $validated['payment_date'];
                    $claim->payment_reference = 'Payroll ' . ucfirst($payroll->month) . ' ' . $payroll->year;
                    $claim->processed_by = auth()->id();
                    $claim->processed_at = now();
                    $claim->save();

                    if ($employeeUser) {
                        NotificationController::createNotification(
                            $employeeUser->id,
                            'reimbursement_paid',
                            'Reimbursement Paid',
                            "Your reimbursement of ₹{$claim->amount} for {$claim->category->name} was paid with your " . ucfirst($payroll->month) . ' ' . $payroll->year . ' salary.',
                            route('reimbursements.show', $claim->id),
                            ['claim_id' => $claim->id]
                        );
                    }
                }
            }
        });

        if ($error) {
            return redirect()->back()->withInput()->with('error', $error);
        }

        return redirect()->route('payrolls.history', $id)->with('success', 'Payment recorded successfully!');
    }

    /**
     * Generate payslip for payroll.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function generatePayslip($id)
    {
        $payroll = Payroll::with(['personalInformation.employmentDetail', 'deductionDetails', 'payments', 'approvedBy'])->findOrFail($id);
        $this->authorizePayrollView($payroll);
        return view('payroll.payslip', compact('payroll'));
    }

    /**
     * Show payment history for a payroll.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function showPaymentHistory($id)
    {
        $payroll = Payroll::with(['personalInformation', 'payments.processedBy', 'deductionDetails'])->findOrFail($id);
        $this->authorizePayrollView($payroll);
        return view('payroll.payment_history', compact('payroll'));
    }

    /**
     * Salary structure per employee + company payroll rules.
     */
    public function salaryStructure()
    {
        $this->requireAdmin();

        $employees = PersonalInformation::with('salaryDetail')
            ->whereHas('salaryDetail')
            ->orderBy('first_name')
            ->get();

        return view('payroll.salary_structure', [
            'employees' => $employees,
            'settings' => PayrollSetting::current(),
        ]);
    }

    public function updateSalaryStructure(Request $request, $id)
    {
        $this->requireAdmin();

        $validated = $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'hra' => 'nullable|numeric|min:0',
            'special_allowance' => 'nullable|numeric|min:0',
            'other_allowance' => 'nullable|numeric|min:0',
            'tds_monthly' => 'nullable|numeric|min:0',
        ]);

        $salary = SalaryDetail::where('personal_information_id', $id)->firstOrFail();

        $salary->update([
            'basic_salary' => $validated['basic_salary'],
            'hra' => $validated['hra'] ?? 0,
            'special_allowance' => $validated['special_allowance'] ?? 0,
            'other_allowance' => $validated['other_allowance'] ?? 0,
            'tds_monthly' => $validated['tds_monthly'] ?? 0,
            'pf_applicable' => $request->boolean('pf_applicable'),
            'esi_applicable' => $request->boolean('esi_applicable'),
            'pt_applicable' => $request->boolean('pt_applicable'),
        ]);

        return redirect()->route('payrolls.salary.structure')->with('success', 'Salary structure updated. It applies to payrolls generated from now on.');
    }

    public function updatePayrollSettings(Request $request)
    {
        $this->requireAdmin();

        $validated = $request->validate([
            'salary_divisor' => 'required|in:calendar_days,working_days',
            'weekly_off_days' => 'nullable|array',
            'weekly_off_days.*' => 'in:sun,mon,tue,wed,thu,fri,sat',
            'late_marks_per_deduction' => 'required|integer|min:0|max:31',
            'late_deduction_days' => 'required|numeric|min:0|max:5',
            'pf_rate' => 'required|numeric|min:0|max:100',
            'pf_wage_ceiling' => 'required|numeric|min:0',
            'esi_rate' => 'required|numeric|min:0|max:100',
            'esi_gross_limit' => 'required|numeric|min:0',
            'professional_tax' => 'required|numeric|min:0',
        ]);

        PayrollSetting::current()->update([
            'salary_divisor' => $validated['salary_divisor'],
            'weekly_off_days' => implode(',', $validated['weekly_off_days'] ?? []),
            'unpaid_leave_deduction' => $request->boolean('unpaid_leave_deduction'),
            'absent_deduction' => $request->boolean('absent_deduction'),
            'late_marks_per_deduction' => $validated['late_marks_per_deduction'],
            'late_deduction_days' => $validated['late_deduction_days'],
            'pf_rate' => $validated['pf_rate'],
            'pf_wage_ceiling' => $validated['pf_wage_ceiling'],
            'esi_rate' => $validated['esi_rate'],
            'esi_gross_limit' => $validated['esi_gross_limit'],
            'professional_tax' => $validated['professional_tax'],
        ]);

        return redirect()->route('payrolls.salary.structure')->with('success', 'Payroll rules saved.');
    }

    private function requireAdmin(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Admins can see any payroll; employees only their own.
     */
    private function authorizePayrollView(Payroll $payroll): void
    {
        $user = auth()->user();

        if ($user->role !== 'admin' && (int) $payroll->personal_information_id !== (int) $user->personal_information_id) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Show bulk approval page for pending payrolls.
     *
     * @return \Illuminate\View\View
     */
    public function showBulkApproval()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $pendingPayrolls = Payroll::with(['personalInformation', 'deductionDetails'])
            ->where('approval_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('payroll.bulk_approval', compact('pendingPayrolls'));
    }
}
