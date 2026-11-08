<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\PayrollPayment;
use App\Models\Attendance;
use App\Models\PersonalInformation;
use App\Models\EmploymentDetail;
use App\Models\SalaryDetail;

class ReportsController extends Controller
{
    /**
     * Display reports index page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        return view('reports.index');
    }

    /**
     * Display Monthly Payroll Report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function monthlyPayroll(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $month = $request->get('month', date('F'));
        $year = $request->get('year', date('Y'));

        $payrolls = Payroll::with(['personalInformation', 'personalInformation.employmentDetail'])
            ->where('month', $month)
            ->where('year', $year)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalGrossSalary = $payrolls->sum(function ($payroll) {
            return $payroll->basic_salary + $payroll->allowances;
        });
        $totalDeductions = $payrolls->sum('deductions');
        $totalNetSalary = $payrolls->sum('net_salary');

        return view('reports.monthly_payroll', compact(
            'payrolls',
            'month',
            'year',
            'totalGrossSalary',
            'totalDeductions',
            'totalNetSalary'
        ));
    }

    /**
     * Display Department-wise Salary Report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function departmentSalary(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $month = $request->get('month', date('F'));
        $year = $request->get('year', date('Y'));

        $departments = EmploymentDetail::select('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        $departmentData = [];
        foreach ($departments as $department) {
            $employees = PersonalInformation::whereHas('employmentDetail', function ($query) use ($department) {
                $query->where('department', $department);
            })->pluck('id');

            $payrolls = Payroll::whereIn('personal_information_id', $employees)
                ->where('month', $month)
                ->where('year', $year)
                ->get();

            $totalSalary = $payrolls->sum('net_salary');
            $employeeCount = $payrolls->count();
            $averageSalary = $employeeCount > 0 ? $totalSalary / $employeeCount : 0;

            $departmentData[] = [
                'department' => $department,
                'employee_count' => $employeeCount,
                'total_salary' => $totalSalary,
                'average_salary' => $averageSalary,
            ];
        }

        return view('reports.department_salary', compact(
            'departmentData',
            'month',
            'year'
        ));
    }

    /**
     * Display Attendance Summary Report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function attendanceSummary(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-t'));

        $attendances = Attendance::with('personalInformation')
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        $summary = [
            'present_days' => $attendances->where('status', 'present')->count(),
            'absent_days' => $attendances->where('status', 'absent')->count(),
            'half_days' => $attendances->where('status', 'half_day')->count(),
            'late_marks' => $attendances->where('status', 'late')->count(),
            'overtime' => $attendances->where('status', 'overtime')->count(),
        ];

        $employeeWiseSummary = [];
        foreach ($attendances->groupBy('personal_information_id') as $employeeId => $employeeAttendances) {
            $employee = PersonalInformation::find($employeeId);
            if ($employee) {
                $employeeWiseSummary[] = [
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'present_days' => $employeeAttendances->where('status', 'present')->count(),
                    'absent_days' => $employeeAttendances->where('status', 'absent')->count(),
                    'half_days' => $employeeAttendances->where('status', 'half_day')->count(),
                    'late_marks' => $employeeAttendances->where('status', 'late')->count(),
                    'overtime' => $employeeAttendances->where('status', 'overtime')->count(),
                ];
            }
        }

        return view('reports.attendance_summary', compact(
            'summary',
            'employeeWiseSummary',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display Leave Report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function leaveReport(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-t'));

        // Since there's no leave model, we'll use attendance with 'leave' status
        $leaves = Attendance::with('personalInformation')
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('status', 'leave')
            ->get();

        $leaveTypes = ['sick_leave', 'casual_leave', 'earned_leave', 'compensatory_leave'];
        $leaveSummary = [];

        foreach ($leaveTypes as $leaveType) {
            $leaveSummary[$leaveType] = [
                'applied' => 0,
                'approved' => 0,
                'rejected' => 0,
                'balance' => 0,
            ];
        }

        // Calculate per employee leave summary
        $employeeLeaveSummary = [];
        foreach ($leaves->groupBy('personal_information_id') as $employeeId => $employeeLeaves) {
            $employee = PersonalInformation::find($employeeId);
            if ($employee) {
                $employeeLeaveSummary[] = [
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'total_leaves' => $employeeLeaves->count(),
                    'approved_leaves' => $employeeLeaves->count(), // Assuming all are approved for now
                    'rejected_leaves' => 0,
                    'leave_balance' => 12 - $employeeLeaves->count(), // Assuming 12 days annual leave
                ];
            }
        }

        return view('reports.leave_report', compact(
            'leaveSummary',
            'employeeLeaveSummary',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display Payment History Report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function paymentHistory(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-t'));
        $paymentMethod = $request->get('payment_method');

        $user = auth()->user();

        // Role-based access control
        if ($user->role === 'admin') {
            // Admin can see all payments
            $query = PayrollPayment::with(['payroll.personalInformation', 'processedBy'])
                ->whereBetween('payment_date', [$startDate, $endDate]);

            // Filter by payment method if specified
            if ($paymentMethod) {
                $query->where('payment_method', $paymentMethod);
            }

            $payments = $query->orderBy('payment_date', 'desc')->get();
        } else {
            // Employees can only see their own payments
            if ($user->personal_information_id) {
                $query = PayrollPayment::with(['payroll.personalInformation', 'processedBy'])
                    ->whereHas('payroll', function ($query) use ($user) {
                        $query->where('personal_information_id', $user->personal_information_id);
                    })
                    ->whereBetween('payment_date', [$startDate, $endDate]);

                $payments = $query->orderBy('payment_date', 'desc')->get();
            } else {
                $payments = collect(); // Empty collection if not linked
            }
        }

        $totalAmountPaid = $payments->sum('amount');

        $paymentMethods = $payments->groupBy('payment_method')->map(function ($payments) {
            return [
                'count' => $payments->count(),
                'total' => $payments->sum('amount'),
            ];
        });

        return view('reports.payment_history', compact(
            'payments',
            'totalAmountPaid',
            'paymentMethods',
            'startDate',
            'endDate',
            'paymentMethod'
        ));
    }
}
