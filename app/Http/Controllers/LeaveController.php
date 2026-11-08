<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\Holiday;
use App\Models\PersonalInformation;
use App\Models\Notification;
use App\Http\Controllers\NotificationController;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Display leave management dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $pendingLeaves = Leave::with(['personalInformation', 'leaveType'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            $recentLeaves = Leave::with(['personalInformation', 'leaveType'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        } elseif ($user->isManager()) {
            // Managers can see pending leaves of their subordinates
            $subordinateIds = $user->subordinates()->pluck('personal_information_id')->toArray();

            $pendingLeaves = Leave::with(['personalInformation', 'leaveType'])
                ->where('status', 'pending')
                ->whereIn('personal_information_id', $subordinateIds)
                ->orderBy('created_at', 'desc')
                ->get();

            $recentLeaves = Leave::with(['personalInformation', 'leaveType'])
                ->whereIn('personal_information_id', $subordinateIds)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        } else {
            // Employees can only see their own leaves
            $pendingLeaves = collect();
            $recentLeaves = Leave::with(['leaveType'])
                ->where('personal_information_id', $user->personal_information_id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        // Get user's own leaves for all roles
        $myLeaves = Leave::with(['leaveType'])
            ->where('personal_information_id', $user->personal_information_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get user's leave balances
        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('personal_information_id', $user->personal_information_id)
            ->where('year', date('Y'))
            ->get();

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $upcomingHolidays = Holiday::where('date', '>=', Carbon::today())
            ->where('is_active', true)
            ->orderBy('date')
            ->limit(5)
            ->get();

        return view('leave.index', compact('pendingLeaves', 'recentLeaves', 'myLeaves', 'leaveBalances', 'leaveTypes', 'upcomingHolidays'));
    }

    /**
     * Show form to apply for leave.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $user = auth()->user();
        
        if (!$user->personal_information_id) {
            return redirect()->route('leave.index')->with('error', 'Your profile is not linked to any employee record. Please contact HR.');
        }

        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('personal_information_id', $user->personal_information_id)
            ->where('year', date('Y'))
            ->get();

        return view('leave.create', compact('leaveTypes', 'leaveBalances'));
    }

    /**
     * Store leave application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'duration' => 'required|in:full_day,half_day,multiple_days',
            'reason' => 'required|string|min:10',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
        
        // Calculate total days
        $fromDate = Carbon::parse($validated['from_date']);
        $toDate = Carbon::parse($validated['to_date']);
        
        if ($validated['duration'] === 'half_day') {
            $totalDays = 0.5;
        } elseif ($validated['duration'] === 'full_day') {
            $totalDays = 1;
        } else {
            $totalDays = $fromDate->diffInDays($toDate) + 1;
            
            // Exclude weekends and holidays
            $period = \Carbon\CarbonPeriod::create($fromDate, $toDate);
            $holidays = Holiday::where('is_active', true)
                ->whereBetween('date', [$fromDate, $toDate])
                ->pluck('date')
                ->toArray();
            
            foreach ($period as $date) {
                if ($date->isWeekend() || in_array($date->format('Y-m-d'), $holidays)) {
                    $totalDays--;
                }
            }
        }

        // Check leave balance
        $user = auth()->user();
        if (!$user->personal_information_id) {
            return redirect()->back()->with('error', 'Your profile is not linked to any employee record. Please contact HR.');
        }

        $personalInfo = PersonalInformation::find($user->personal_information_id);

        $leaveBalance = LeaveBalance::where('personal_information_id', $user->personal_information_id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', date('Y'))
            ->first();

        if (!$leaveType->is_active) {
            return redirect()->back()->withInput()->with('error', 'This leave type is not active.');
        }

        // A new year starts without balances: create this employee's balance from the leave type default
        // instead of failing with "Insufficient leave balance" on 1 January.
        if (!$leaveBalance) {
            $leaveBalance = LeaveBalance::firstOrCreate(
                [
                    'personal_information_id' => $user->personal_information_id,
                    'leave_type_id' => $validated['leave_type_id'],
                    'year' => date('Y'),
                ],
                [
                    'total_days' => $leaveType->days_per_year,
                    'used_days' => 0,
                    'remaining_days' => $leaveType->days_per_year,
                    'carried_forward_days' => 0,
                ]
            );
        }

        if ($totalDays <= 0) {
            return redirect()->back()->withInput()->with('error', 'The selected dates contain no working days (weekends and holidays are excluded).');
        }

        // Days already requested but not yet approved are reserved, so several pending
        // requests cannot together exceed the balance.
        $pendingDays = (float) Leave::where('personal_information_id', $user->personal_information_id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('status', 'pending')
            ->whereYear('from_date', date('Y'))
            ->sum('total_days');

        if (!$leaveBalance || ($leaveBalance->remaining_days - $pendingDays) < $totalDays) {
            return redirect()->back()->withInput()->with('error', 'Insufficient leave balance (including your pending requests).');
        }

        // Reject overlapping leave requests
        $overlap = Leave::where('personal_information_id', $user->personal_information_id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('from_date', '<=', $validated['to_date'])
            ->where('to_date', '>=', $validated['from_date'])
            ->exists();

        if ($overlap) {
            return redirect()->back()->withInput()->with('error', 'You already have a pending or approved leave overlapping these dates.');
        }

        // Handle file upload (private storage - attachments can contain medical documents)
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave_attachments', 'local');
        }

        $leave = Leave::create([
            'personal_information_id' => $user->personal_information_id,
            'leave_type_id' => $validated['leave_type_id'],
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
            'duration' => $validated['duration'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'attachment' => $attachmentPath,
            'status' => $leaveType->requires_approval ? 'pending' : 'approved',
            'is_paid' => $leaveType->is_paid,
        ]);

        // Leave types that need no approval are approved immediately, so deduct the balance now
        if ($leave->status === 'approved') {
            $leaveBalance->used_days += $totalDays;
            $leaveBalance->remaining_days -= $totalDays;
            $leaveBalance->save();
        }

        // Create notification for reporting manager if leave requires approval
        if ($leave->status === 'pending') {
            // Get reporting manager
            $employmentDetail = \App\Models\EmploymentDetail::where('personal_information_id', $user->personal_information_id)->first();
            $reportingManagerId = $employmentDetail->reporting_manager_id ?? null;

            if ($reportingManagerId) {
                // Send notification to reporting manager
                $reportingManagerUser = \App\Models\User::where('personal_information_id', $reportingManagerId)->first();
                if ($reportingManagerUser) {
                    NotificationController::createNotification(
                        $reportingManagerUser->id,
                        'leave_request',
                        'Leave Request Pending',
                        "Leave request from {$personalInfo->first_name} {$personalInfo->last_name} for {$totalDays} day(s) from {$validated['from_date']} to {$validated['to_date']} is pending approval.",
                        route('leave.approvals'),
                        ['leave_id' => $leave->id, 'employee_id' => $user->personal_information_id]
                    );
                }
            } else {
                // If no reporting manager, notify admins
                NotificationController::createForAdmins(
                    'leave_request',
                    'Leave Request Pending',
                    "Leave request from {$personalInfo->first_name} {$personalInfo->last_name} for {$totalDays} day(s) from {$validated['from_date']} to {$validated['to_date']} is pending approval.",
                    route('leave.approvals'),
                    ['leave_id' => $leave->id, 'employee_id' => $user->personal_information_id]
                );
            }
        }

        return redirect()->route('leave.index')->with('success', 'Leave application submitted successfully!');
    }

    /**
     * Show leave approval page for admin.
     *
     * @return \Illuminate\View\View
     */
    public function approvals()
    {
        $user = auth()->user();

        if ($user->isEmployee()) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->isAdmin()) {
            $pendingLeaves = Leave::with(['personalInformation', 'leaveType'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($user->isManager()) {
            $subordinateIds = $user->subordinates()->pluck('personal_information_id')->toArray();

            $pendingLeaves = Leave::with(['personalInformation', 'leaveType'])
                ->where('status', 'pending')
                ->whereIn('personal_information_id', $subordinateIds)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $pendingLeaves = collect();
        }

        return view('leave.approvals', compact('pendingLeaves'));
    }

    /**
     * Approve or reject leave application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string',
        ]);

        $user = auth()->user();
        $leave = Leave::findOrFail($id);

        // Check if user has permission to approve this leave
        if (!$user->canApproveLeave($leave)) {
            abort(403, 'Unauthorized action. You can only approve leave requests of your subordinates.');
        }

        $error = null;

        \Illuminate\Support\Facades\DB::transaction(function () use ($id, $validated, &$error, &$leave) {
            $leave = Leave::lockForUpdate()->findOrFail($id);

            if ($leave->status !== 'pending') {
                $error = 'This leave has already been processed.';
                return;
            }

            if ($validated['status'] === 'approved') {
                // The balance of the year the leave actually falls in (not simply the current year)
                $leaveBalance = LeaveBalance::where('personal_information_id', $leave->personal_information_id)
                    ->where('leave_type_id', $leave->leave_type_id)
                    ->where('year', \Carbon\Carbon::parse($leave->from_date)->year)
                    ->lockForUpdate()
                    ->first();

                if (!$leaveBalance || $leaveBalance->remaining_days < $leave->total_days) {
                    $error = 'Cannot approve: the employee does not have enough leave balance.';
                    return;
                }

                $leaveBalance->used_days += $leave->total_days;
                $leaveBalance->remaining_days -= $leave->total_days;
                $leaveBalance->save();
            } else {
                $leave->rejection_reason = $validated['rejection_reason'];
            }

            $leave->status = $validated['status'];
            $leave->approved_by = auth()->id();
            $leave->approved_at = now();
            $leave->save();
        });

        if ($error) {
            return redirect()->back()->with('error', $error);
        }

        // Tell the employee about the decision
        $employeeUser = \App\Models\User::where('personal_information_id', $leave->personal_information_id)->first();
        if ($employeeUser) {
            $isApproved = $validated['status'] === 'approved';
            NotificationController::createNotification(
                $employeeUser->id,
                $isApproved ? 'leave_approved' : 'leave_rejected',
                $isApproved ? 'Leave Approved' : 'Leave Rejected',
                $isApproved
                    ? "Your leave from {$leave->from_date->format('d M Y')} to {$leave->to_date->format('d M Y')} has been approved."
                    : "Your leave from {$leave->from_date->format('d M Y')} to {$leave->to_date->format('d M Y')} has been rejected. Reason: {$validated['rejection_reason']}",
                route('leave.index'),
                ['leave_id' => $leave->id]
            );
        }

        return redirect()->route('leave.approvals')->with('success', "Leave {$validated['status']} successfully!");
    }

    /**
     * Show leave balance for current user.
     *
     * @return \Illuminate\View\View
     */
    public function balance()
    {
        $user = auth()->user();

        if (!$user->personal_information_id) {
            return redirect()->route('leave.index')->with('error', 'Your profile is not linked to any employee record. Please contact HR.');
        }

        $year = request()->get('year', date('Y'));

        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('personal_information_id', $user->personal_information_id)
            ->where('year', $year)
            ->get();

        return view('leave.balance', compact('leaveBalances', 'year'));
    }

    /**
     * Show leave calendar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function calendar(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $user = auth()->user();

        if ($user->isAdmin()) {
            $leaves = Leave::with(['personalInformation', 'leaveType'])
                ->whereYear('from_date', $year)
                ->whereMonth('from_date', $month)
                ->where('status', 'approved')
                ->get();
        } elseif ($user->isManager()) {
            $subordinateIds = $user->subordinates()->pluck('personal_information_id')->toArray();

            $leaves = Leave::with(['personalInformation', 'leaveType'])
                ->whereYear('from_date', $year)
                ->whereMonth('from_date', $month)
                ->where('status', 'approved')
                ->whereIn('personal_information_id', $subordinateIds)
                ->get();
        } else {
            if (!$user->personal_information_id) {
                $leaves = collect(); // Empty collection for users without employee records
            } else {
                $leaves = Leave::with('leaveType')
                    ->where('personal_information_id', $user->personal_information_id)
                    ->whereYear('from_date', $year)
                    ->whereMonth('from_date', $month)
                    ->get();
            }
        }

        $holidays = Holiday::where('is_active', true)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        return view('leave.calendar', compact('leaves', 'holidays', 'month', 'year'));
    }

    /**
     * Show leave history.
     *
     * @return \Illuminate\View\View
     */
    public function history()
    {
        $user = auth()->user();
        
        if (!$user->personal_information_id) {
            return redirect()->route('leave.index')->with('error', 'Your profile is not linked to any employee record. Please contact HR.');
        }

        $leaves = Leave::with(['leaveType', 'approvedBy'])
            ->where('personal_information_id', $user->personal_information_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('leave.history', compact('leaves'));
    }

    /**
     * Show admin settings for leave management.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $leaveTypes = LeaveType::all();
        $holidays = Holiday::where('is_active', true)
            ->where('date', '>=', Carbon::today())
            ->orderBy('date')
            ->get();

        return view('leave.settings', compact('leaveTypes', 'holidays'));
    }

    /**
     * Store new leave type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeLeaveType(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:leave_types,code',
            'description' => 'nullable|string',
            'days_per_year' => 'required|integer|min:0',
            'is_paid' => 'boolean',
            'requires_approval' => 'boolean',
            'requires_document' => 'boolean',
            'is_carry_forward' => 'boolean',
            'max_carry_forward_days' => 'nullable|numeric|min:0',
            'is_encashable' => 'boolean',
        ]);

        // Convert checkbox values to boolean
        $validated['is_paid'] = $request->has('is_paid');
        $validated['requires_approval'] = $request->has('requires_approval');
        $validated['requires_document'] = $request->has('requires_document');
        $validated['is_carry_forward'] = $request->has('is_carry_forward');
        $validated['is_encashable'] = $request->has('is_encashable');

        // Set max_carry_forward_days to 0 if is_carry_forward is false or not provided
        if (!$validated['is_carry_forward'] || !isset($validated['max_carry_forward_days'])) {
            $validated['max_carry_forward_days'] = 0;
        }

        LeaveType::create($validated);

        return redirect()->route('leave.settings')->with('success', 'Leave type created successfully!');
    }

    /**
     * Get leave type data for editing.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLeaveType($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $leaveType = LeaveType::findOrFail($id);
        return response()->json($leaveType);
    }

    /**
     * Update leave type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateLeaveType(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $leaveType = LeaveType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:leave_types,code,' . $id,
            'description' => 'nullable|string',
            'days_per_year' => 'required|integer|min:0',
            'is_paid' => 'boolean',
            'requires_approval' => 'boolean',
            'requires_document' => 'boolean',
            'is_carry_forward' => 'boolean',
            'max_carry_forward_days' => 'nullable|numeric|min:0',
            'is_encashable' => 'boolean',
        ]);

        // Convert checkbox values to boolean
        $validated['is_paid'] = $request->has('is_paid');
        $validated['requires_approval'] = $request->has('requires_approval');
        $validated['requires_document'] = $request->has('requires_document');
        $validated['is_carry_forward'] = $request->has('is_carry_forward');
        $validated['is_encashable'] = $request->has('is_encashable');

        // Set max_carry_forward_days to 0 if is_carry_forward is false or not provided
        if (!$validated['is_carry_forward'] || !isset($validated['max_carry_forward_days'])) {
            $validated['max_carry_forward_days'] = 0;
        }

        $leaveType->update($validated);

        return redirect()->route('leave.settings')->with('success', 'Leave type updated successfully!');
    }

    /**
     * Delete leave type.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteLeaveType($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $leaveType = LeaveType::findOrFail($id);
        $leaveType->delete();

        return redirect()->route('leave.settings')->with('success', 'Leave type deleted successfully!');
    }

    /**
     * Store new holiday.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeHoliday(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:public_holiday,company_holiday,optional_holiday',
            'description' => 'nullable|string',
            'is_recurring' => 'boolean',
        ]);

        Holiday::create($validated);

        return redirect()->route('leave.settings')->with('success', 'Holiday added successfully!');
    }

    /**
     * Initialize leave balances for all employees.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initializeBalances()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $employees = PersonalInformation::all();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $year = date('Y');

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                $existingBalance = LeaveBalance::where('personal_information_id', $employee->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('year', $year)
                    ->first();

                if (!$existingBalance) {
                    LeaveBalance::create([
                        'personal_information_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $year,
                        'total_days' => $leaveType->days_per_year,
                        'used_days' => 0,
                        'remaining_days' => $leaveType->days_per_year,
                        'carried_forward_days' => 0,
                    ]);
                }
            }
        }

        return redirect()->route('leave.settings')->with('success', 'Leave balances initialized successfully!');
    }
}
