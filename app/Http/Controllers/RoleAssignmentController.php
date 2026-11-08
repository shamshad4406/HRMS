<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersonalInformation;
use App\Models\User;
use App\Models\EmploymentDetail;
use Illuminate\Support\Facades\DB;

class RoleAssignmentController extends Controller
{
    /**
     * Display a listing of all employees with their roles and managers.
     */
    public function index()
    {
        // Only admin can access this
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Only admin can access role management.');
        }

        $employees = PersonalInformation::with(['user', 'employmentDetail.reportingManager'])->get();

        return view('role_assignments.index', compact('employees'));
    }

    /**
     * Show the form for editing employee role and manager.
     */
    public function edit($id)
    {
        // Only admin can access this
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Only admin can access role management.');
        }

        $employee = PersonalInformation::with(['user', 'employmentDetail'])->find($id);

        if (!$employee) {
            return redirect()->route('role-assignments.index')->with('error', 'Employee not found');
        }

        // Get all employees who can be managers (all employees)
        $managers = PersonalInformation::all();

        return view('role_assignments.edit', compact('employee', 'managers'));
    }

    /**
     * Update employee role and manager.
     */
    public function update(Request $request, $id)
    {
        // Only admin can access this
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Only admin can access role management.');
        }

        $employee = PersonalInformation::find($id);

        if (!$employee) {
            return redirect()->route('role-assignments.index')->with('error', 'Employee not found');
        }

        $validated = $request->validate([
            'role' => 'required|in:admin,hr,cto,manager,employee',
            'reporting_manager_id' => 'nullable|exists:personal_information,id',
        ]);

        // Prevent assigning the same employee as their own manager
        if ($validated['reporting_manager_id'] == $id) {
            return redirect()->back()->with('error', 'An employee cannot be their own manager.');
        }

        DB::transaction(function () use ($employee, $validated) {
            // Update user role
            if ($employee->user) {
                $employee->user->update(['role' => $validated['role']]);
            }

            // Update reporting manager
            if ($employee->employmentDetail) {
                $employee->employmentDetail->update([
                    'reporting_manager_id' => $validated['reporting_manager_id'] ?? null
                ]);
            } else {
                // Create employment detail if it doesn't exist
                EmploymentDetail::create([
                    'personal_information_id' => $employee->id,
                    'department' => 'unassigned',
                    'designation' => 'unassigned',
                    'employment_type' => 'full-time',
                    'joining_date' => now(),
                    'reporting_manager_id' => $validated['reporting_manager_id'] ?? null,
                    'status' => 'active',
                ]);
            }
        });

        return redirect()->route('role-assignments.index')->with('success', 'Role and manager updated successfully!');
    }

    /**
     * Show the organization hierarchy.
     */
    public function hierarchy()
    {
        // Only admin can access this
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Only admin can view hierarchy.');
        }

        $employees = PersonalInformation::with(['user', 'employmentDetail.reportingManager.user'])->get();

        // Build hierarchy tree
        $hierarchy = $this->buildHierarchy($employees);

        return view('role_assignments.hierarchy', compact('hierarchy'));
    }

    /**
     * Build hierarchy tree from employees.
     */
    private function buildHierarchy($employees)
    {
        $tree = [];
        $employeeMap = [];

        // Create a map of employees
        foreach ($employees as $employee) {
            $employeeMap[$employee->id] = [
                'employee' => $employee,
                'subordinates' => []
            ];
        }

        // Build the tree
        foreach ($employees as $employee) {
            $managerId = $employee->employmentDetail->reporting_manager_id ?? null;

            if ($managerId && isset($employeeMap[$managerId])) {
                $employeeMap[$managerId]['subordinates'][] = &$employeeMap[$employee->id];
            } else {
                // Top level (no manager or manager not in list)
                $tree[] = &$employeeMap[$employee->id];
            }
        }

        return $tree;
    }
}
