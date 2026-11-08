<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersonalInformation;
use App\Models\EmploymentDetail;
use App\Models\SalaryDetail;
use App\Models\BankDetail;
use App\Models\EmployeeDocument;
use App\Models\EmergencyContact;
use App\Models\User;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Show the form for creating a new employee.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        // Only admin can create employees
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can add employees.');
        }

        $step = $request->query('step', 'personal');

        // Clear draft if requested
        if ($request->query('clear') === 'true') {
            session()->forget('employee_draft');
            return redirect()->route('employees.create', ['step' => 'personal'])->with('success', 'Draft cleared successfully.');
        }

        $draft = session('employee_draft', []);

        return view('add_employee', compact('step', 'draft'));
    }

    /**
     * Store personal information step.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storePersonalInfo(Request $request)
    {
        // Only admin can create employees
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can add employees.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'email' => 'required|email|unique:personal_information,email',
            'aadhaar_number' => 'required|string|size:12',
            'pan_number' => 'required|string|size:10',
            'mobile_number' => 'required|string|size:10',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
            // Normalize path to use forward slashes
            $photoPath = str_replace('\\', '/', $photoPath);
        }

        // Store personal information in session (temporary storage)
        session([
            'employee_draft' => array_merge(session('employee_draft', []), [
                'personal' => array_merge($validated, ['photo' => $photoPath])
            ])
        ]);

        return redirect()->route('employees.create', ['step' => 'employment']);
    }

    /**
     * Store employment details step.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeEmploymentDetails(Request $request)
    {
        // Only admin can create employees
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can add employees.');
        }

        $validated = $request->validate([
            'department' => 'required|string',
            'designation' => 'required|string',
            'employment_type' => 'required|in:full-time,part-time,contract,intern',
            'joining_date' => 'required|date',
            'status' => 'nullable|in:active,inactive,on-leave',
        ]);

        // Check if personal information was filled
        if (!session('employee_draft.personal')) {
            return redirect()->route('employees.create')->with('error', 'Please start from personal information');
        }

        // Store employment details in session (temporary storage)
        session([
            'employee_draft' => array_merge(session('employee_draft', []), [
                'employment' => $validated
            ])
        ]);

        return redirect()->route('employees.create', ['step' => 'salary']);
    }

    /**
     * Store salary details step.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeSalaryDetails(Request $request)
    {
        // Only admin can create employees
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can add employees.');
        }

        $validated = $request->validate([
            'basic_salary' => 'required|numeric',
            'salary_type' => 'required|in:monthly,weekly,bi-weekly,annual',
            'currency' => 'required|in:inr,usd,eur,gbp',
        ]);

        // Check if previous steps were filled
        if (!session('employee_draft.personal') || !session('employee_draft.employment')) {
            return redirect()->route('employees.create')->with('error', 'Please start from personal information');
        }

        // Store salary details in session (temporary storage)
        session([
            'employee_draft' => array_merge(session('employee_draft', []), [
                'salary' => $validated
            ])
        ]);

        return redirect()->route('employees.create', ['step' => 'bank']);
    }

    /**
     * Store bank details step.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeBankDetails(Request $request)
    {
        // Only admin can create employees
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can add employees.');
        }

        $validated = $request->validate([
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|numeric|digits_between:9,18',
            'ifsc_code' => 'nullable|string|size:11|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'branch_name' => 'nullable|string|max:255',
        ]);

        // Check if previous steps were filled
        if (!session('employee_draft.personal') || !session('employee_draft.employment') || !session('employee_draft.salary')) {
            return redirect()->route('employees.create')->with('error', 'Please start from personal information');
        }

        // Store bank details in session (temporary storage)
        session([
            'employee_draft' => array_merge(session('employee_draft', []), [
                'bank' => $validated
            ])
        ]);

        return redirect()->route('employees.create', ['step' => 'documents']);
    }

    /**
     * Store documents step.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDocuments(Request $request)
    {
        // Only admin can create employees
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can add employees.');
        }

        try {
            $validated = $request->validate([
                'resume' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                'aadhaar_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'pan_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            ]);

            // Check if previous steps were filled
            if (!session('employee_draft.personal') || !session('employee_draft.employment') || !session('employee_draft.salary') || !session('employee_draft.bank')) {
                return redirect()->route('employees.create')->with('error', 'Please start from personal information');
            }

            // Handle document uploads
            $resumePath = null;
            $aadhaarCardPath = null;
            $panCardPath = null;

            if ($request->hasFile('resume')) {
                $resumePath = $request->file('resume')->store('documents/resumes', 'local');
            }

            if ($request->hasFile('aadhaar_card')) {
                $aadhaarCardPath = $request->file('aadhaar_card')->store('documents/aadhaar', 'local');
            }

            if ($request->hasFile('pan_card')) {
                $panCardPath = $request->file('pan_card')->store('documents/pan', 'local');
            }

            // Store documents in session (temporary storage)
            session([
                'employee_draft' => array_merge(session('employee_draft', []), [
                    'documents' => [
                        'resume' => $resumePath,
                        'aadhaar_card' => $aadhaarCardPath,
                        'pan_card' => $panCardPath,
                    ]
                ])
            ]);

            return redirect()->route('employees.create', ['step' => 'emergency']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let Laravel show the field-level validation messages
            throw $e;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Employee document upload failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to save documents. Please try again.')->withInput();
        }
    }

    /**
     * Store emergency contact step.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeEmergencyContact(Request $request)
    {
        // Only admin can create employees
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can add employees.');
        }

        $validated = $request->validate([
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|in:spouse,parent,sibling,friend,other',
            'emergency_contact_number' => 'nullable|numeric|digits:10',
            'emergency_contact_address' => 'nullable|string|max:500',
        ]);

        // Check if all previous steps were filled
        $draft = session('employee_draft');
        if (!$draft || !$draft['personal'] || !$draft['employment'] || !$draft['salary'] || !$draft['bank'] || !$draft['documents']) {
            return redirect()->route('employees.create')->with('error', 'Please complete all previous steps first');
        }

        // Store emergency contact in session
        session([
            'employee_draft' => array_merge(session('employee_draft', []), [
                'emergency' => $validated
            ])
        ]);

        // Now save all accumulated data to the database in a transaction
        $personalInfo = DB::transaction(function () use ($draft, $validated) {
            // Get the next auto-increment ID to generate a unique code
            $nextId = DB::select("SELECT AUTO_INCREMENT as next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personal_information'")[0]->next_id ?? 1;

            // Generate employee code based on the next ID (allows up to 999999 employees)
            $employeeCode = 'INN' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

            // Store personal information
            $personal = PersonalInformation::create([
                'employee_code' => $employeeCode,
                'first_name' => $draft['personal']['first_name'],
                'last_name' => $draft['personal']['last_name'],
                'gender' => $draft['personal']['gender'],
                'date_of_birth' => $draft['personal']['date_of_birth'],
                'email' => $draft['personal']['email'],
                'aadhaar_number' => $draft['personal']['aadhaar_number'],
                'pan_number' => $draft['personal']['pan_number'],
                'mobile_number' => $draft['personal']['mobile_number'],
                'photo' => $draft['personal']['photo'] ?? null,
            ]);

            // Store employment details
            EmploymentDetail::create([
                'personal_information_id' => $personal->id,
                'department' => $draft['employment']['department'],
                'designation' => $draft['employment']['designation'],
                'employment_type' => $draft['employment']['employment_type'],
                'joining_date' => $draft['employment']['joining_date'],
                'status' => $draft['employment']['status'],
            ]);

            // Store salary details
            SalaryDetail::create([
                'personal_information_id' => $personal->id,
                'basic_salary' => $draft['salary']['basic_salary'],
                'salary_type' => $draft['salary']['salary_type'],
                'currency' => $draft['salary']['currency'],
            ]);

            // Store bank details only if at least one field is provided
            if ($draft['bank']['bank_name'] || $draft['bank']['account_number'] || $draft['bank']['ifsc_code'] || $draft['bank']['branch_name']) {
                BankDetail::create([
                    'personal_information_id' => $personal->id,
                    'bank_name' => $draft['bank']['bank_name'] ?? 'N/A',
                    'account_number' => $draft['bank']['account_number'] ?? 'N/A',
                    'ifsc_code' => $draft['bank']['ifsc_code'] ?? 'N/A',
                    'branch_name' => $draft['bank']['branch_name'] ?? 'N/A',
                ]);
            }

            // Store documents
            EmployeeDocument::create([
                'personal_information_id' => $personal->id,
                'resume' => $draft['documents']['resume'] ?? null,
                'aadhaar_card' => $draft['documents']['aadhaar_card'] ?? null,
                'pan_card' => $draft['documents']['pan_card'] ?? null,
            ]);

            // Store emergency contact
            EmergencyContact::create([
                'personal_information_id' => $personal->id,
                'contact_name' => $validated['emergency_contact_name'] ?? null,
                'relationship' => $validated['emergency_contact_relationship'] ?? null,
                'contact_number' => $validated['emergency_contact_number'] ?? null,
                'address' => $validated['emergency_contact_address'] ?? null,
            ]);

            return $personal;
        });

        // Automatically create user account for the employee
        $plainPassword = null; // temporary password (shown once to the admin, never stored in plain text)
        if ($personalInfo) {
            $existingUser = User::where('email', $personalInfo->email)->first();
            if (!$existingUser) {
                $plainPassword = Str::random(12); // Generate random password
                User::create([
                    'name' => $personalInfo->first_name . ' ' . $personalInfo->last_name,
                    'email' => $personalInfo->email,
                    'password' => Hash::make($plainPassword),
                    'role' => 'employee',
                    'personal_information_id' => $personalInfo->id,
                    'is_active' => true,
                ]);
            } else {
                // User already exists, show existing credentials
                $plainPassword = 'Account already exists - use "Forgot password" to set a new password';
            }
        }

        // Initialize leave balances for the new employee
        $currentYear = date('Y');
        $leaveTypes = LeaveType::where('is_active', true)->get();

        foreach ($leaveTypes as $leaveType) {
            LeaveBalance::create([
                'personal_information_id' => $personalInfo->id,
                'leave_type_id' => $leaveType->id,
                'year' => $currentYear,
                'total_days' => $leaveType->days_per_year,
                'used_days' => 0,
                'remaining_days' => $leaveType->days_per_year,
                'carried_forward_days' => 0,
            ]);
        }

        // Clear session
        session()->forget('employee_draft');

        // Create notification for admin users
        NotificationController::createForAdmins(
            'new_employee',
            'New Employee Added',
            "New employee {$personalInfo->first_name} {$personalInfo->last_name} ({$personalInfo->employee_code}) has been added successfully.",
            route('employees.index'),
            ['employee_id' => $personalInfo->id, 'employee_code' => $personalInfo->employee_code]
        );

        // Credentials are flashed to the session (shown once) instead of being put in the URL,
        // so they don't end up in browser history, server logs or Referer headers.
        return redirect()->route('employees.credentials')
            ->with('success', 'Employee added successfully!')
            ->with('new_employee_credentials', [
            'employee_name' => $personalInfo->first_name . ' ' . $personalInfo->last_name,
            'employee_code' => $personalInfo->employee_code,
            'email' => $personalInfo->email,
            'password' => $plainPassword,
        ]);
    }

    /**
     * Show employee credentials after creation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showCredentials(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $credentials = session('new_employee_credentials');

        if (!$credentials) {
            return redirect()->route('employees.index')->with('error', 'Credentials are shown only once, right after an employee is created.');
        }

        // Clear employee draft from session after successful creation
        session()->forget('employee_draft');

        return view('employee_credentials', [
            'employee_name' => $credentials['employee_name'],
            'employee_code' => $credentials['employee_code'],
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ]);
    }

    /**
     * Display a listing of employees.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->query('search');

        // Role-based access control
        if ($user->role === 'admin') {
            $query = PersonalInformation::with('employmentDetail');

            // Apply search filter if provided
            if ($search) {
                $query->where(function($q) use ($search) {
                    // Search by employee code
                    $q->where('employee_code', 'like', '%' . $search . '%')
                      // Search by email
                      ->orWhere('email', 'like', '%' . $search . '%')
                      // Search by full name (first_name + last_name)
                      ->orWhereRaw('CONCAT(first_name, " ", last_name) LIKE ?', ['%' . $search . '%'])
                      // Search by first name only
                      ->orWhere('first_name', 'like', '%' . $search . '%')
                      // Search by last name only
                      ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            }

            $employees = $query->orderBy('created_at', 'desc')->paginate(100);
        } else {
            // HR and Employee can only see their own data
            if ($user->personal_information_id) {
                $query = PersonalInformation::with('employmentDetail')
                    ->where('id', $user->personal_information_id);

                // Apply search filter if provided
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('employee_code', 'like', '%' . $search . '%')
                          ->orWhere('email', 'like', '%' . $search . '%')
                          ->orWhereRaw('CONCAT(first_name, " ", last_name) LIKE ?', ['%' . $search . '%'])
                          ->orWhere('first_name', 'like', '%' . $search . '%')
                          ->orWhere('last_name', 'like', '%' . $search . '%');
                    });
                }

                $employees = $query->orderBy('created_at', 'desc')->paginate(100);
            } else {
                // User not linked to any employee record
                $employees = PersonalInformation::where('id', 0)->paginate(100); // Empty result
            }
        }

        return view('employee_list', compact('employees', 'search'));
    }

    /**
     * Remove the specified employee from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Employee not delete record only delete admin');
        }

        $employee = PersonalInformation::find($id);

        if (!$employee) {
            return redirect()->route('employees.index')->with('error', 'Employee not found');
        }

        if ((int) $employee->id === (int) auth()->user()->personal_information_id) {
            return redirect()->route('employees.index')->with('error', 'You cannot delete your own employee record.');
        }

        // Payroll history is a financial record: do not wipe it through an FK cascade
        if (\App\Models\Payroll::where('personal_information_id', $employee->id)->exists()) {
            return redirect()->route('employees.index')->with('error', 'This employee has payroll records and cannot be deleted. Mark the employee as inactive instead.');
        }

        // Collect files first, delete them only after the database changes succeed
        $document = $employee->employeeDocument;
        $files = array_filter([
            $document->resume ?? null,
            $document->aadhaar_card ?? null,
            $document->pan_card ?? null,
        ]);
        $photo = $employee->photo;

        \Illuminate\Support\Facades\DB::transaction(function () use ($employee) {
            $employee->employmentDetail()->delete();
            $employee->salaryDetail()->delete();
            $employee->bankDetail()->delete();
            $employee->employeeDocument()->delete();
            $employee->emergencyContact()->delete();
            if ($employee->user) {
                $employee->user->delete();
            }

            // Remaining child records (payroll, attendance, leave, claims) are removed by FK cascade
            $employee->delete();
        });

        foreach ($files as $file) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($file);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($file);
        }
        if ($photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($photo);
        }

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully!');
    }

    /**
     * Show the form for editing the specified employee.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function edit($id, Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can edit employees.');
        }

        $employee = PersonalInformation::with([
            'employmentDetail',
            'salaryDetail',
            'bankDetail',
            'employeeDocument',
            'emergencyContact'
        ])->find($id);

        if (!$employee) {
            return redirect()->route('employees.index')->with('error', 'Employee not found');
        }

        $step = $request->query('step', 'personal');

        return view('edit_employee', compact('employee', 'step'));
    }

    /**
     * Update personal information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePersonalInfo(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can edit employees.');
        }

        $employee = PersonalInformation::find($id);
        if (!$employee) {
            return redirect()->route('employees.index')->with('error', 'Employee not found');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'email' => 'required|email|unique:personal_information,email,' . $id,
            'aadhaar_number' => 'required|string|size:12',
            'pan_number' => 'required|string|size:10',
            'mobile_number' => 'required|string|size:10',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle photo upload
        $photoPath = $employee->photo;
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }
            $photoPath = $request->file('photo')->store('photos', 'public');
            $photoPath = str_replace('\\', '/', $photoPath);
        }

        $employee->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'],
            'date_of_birth' => $validated['date_of_birth'],
            'email' => $validated['email'],
            'aadhaar_number' => $validated['aadhaar_number'],
            'pan_number' => $validated['pan_number'],
            'mobile_number' => $validated['mobile_number'],
            'photo' => $photoPath,
        ]);

        // Update user name if exists
        if ($employee->user) {
            $employee->user->update([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
            ]);
        }

        return redirect()->route('employees.edit', ['id' => $id, 'step' => 'employment'])->with('success', 'Personal information updated successfully!');
    }

    /**
     * Update employment details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateEmploymentDetails(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can edit employees.');
        }

        $employee = PersonalInformation::find($id);
        if (!$employee) {
            return redirect()->route('employees.index')->with('error', 'Employee not found');
        }

        $validated = $request->validate([
            'department' => 'required|string',
            'designation' => 'required|string',
            'employment_type' => 'required|in:full-time,part-time,contract,intern',
            'joining_date' => 'required|date',
            'status' => 'nullable|in:active,inactive,on-leave',
        ]);

        $employmentDetail = $employee->employmentDetail;
        if ($employmentDetail) {
            $employmentDetail->update([
                'department' => $validated['department'],
                'designation' => $validated['designation'],
                'employment_type' => $validated['employment_type'],
                'joining_date' => $validated['joining_date'],
                'status' => !empty($validated['status']) ? $validated['status'] : $employmentDetail->status,
            ]);
        } else {
            EmploymentDetail::create([
                'personal_information_id' => $id,
                'department' => $validated['department'],
                'designation' => $validated['designation'],
                'employment_type' => $validated['employment_type'],
                'joining_date' => $validated['joining_date'],
                'status' => !empty($validated['status']) ? $validated['status'] : 'active',
            ]);
        }

        return redirect()->route('employees.edit', ['id' => $id, 'step' => 'salary'])->with('success', 'Employment details updated successfully!');
    }

    /**
     * Update salary details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSalaryDetails(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can edit employees.');
        }

        $employee = PersonalInformation::find($id);
        if (!$employee) {
            return redirect()->route('employees.index')->with('error', 'Employee not found');
        }

        $validated = $request->validate([
            'basic_salary' => 'required|numeric',
            'salary_type' => 'required|in:monthly,weekly,bi-weekly,annual',
            'currency' => 'required|in:inr,usd,eur,gbp',
        ]);

        $salaryDetail = $employee->salaryDetail;
        if ($salaryDetail) {
            $salaryDetail->update([
                'basic_salary' => $validated['basic_salary'],
                'salary_type' => $validated['salary_type'],
                'currency' => $validated['currency'],
            ]);
        } else {
            SalaryDetail::create([
                'personal_information_id' => $id,
                'basic_salary' => $validated['basic_salary'],
                'salary_type' => $validated['salary_type'],
                'currency' => $validated['currency'],
            ]);
        }

        return redirect()->route('employees.edit', ['id' => $id, 'step' => 'bank'])->with('success', 'Salary details updated successfully!');
    }

    /**
     * Update bank details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateBankDetails(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can edit employees.');
        }

        $employee = PersonalInformation::find($id);
        if (!$employee) {
            return redirect()->route('employees.index')->with('error', 'Employee not found');
        }

        $validated = $request->validate([
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|numeric|digits_between:9,18',
            'ifsc_code' => 'nullable|string|size:11|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'branch_name' => 'nullable|string|max:255',
        ]);

        $bankDetail = $employee->bankDetail;
        if ($bankDetail) {
            $bankDetail->update([
                'bank_name' => $validated['bank_name'] ?? 'N/A',
                'account_number' => $validated['account_number'] ?? 'N/A',
                'ifsc_code' => $validated['ifsc_code'] ?? 'N/A',
                'branch_name' => $validated['branch_name'] ?? 'N/A',
            ]);
        } else {
            BankDetail::create([
                'personal_information_id' => $id,
                'bank_name' => $validated['bank_name'] ?? 'N/A',
                'account_number' => $validated['account_number'] ?? 'N/A',
                'ifsc_code' => $validated['ifsc_code'] ?? 'N/A',
                'branch_name' => $validated['branch_name'] ?? 'N/A',
            ]);
        }

        return redirect()->route('employees.edit', ['id' => $id, 'step' => 'documents'])->with('success', 'Bank details updated successfully!');
    }

    /**
     * Update documents.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDocuments(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can edit employees.');
        }

        $employee = PersonalInformation::find($id);
        if (!$employee) {
            return redirect()->route('employees.index')->with('error', 'Employee not found');
        }

        try {
            $validated = $request->validate([
                'resume' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                'aadhaar_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'pan_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            ]);

            $document = $employee->employeeDocument;

            // Handle document uploads
            $resumePath = $document->resume ?? null;
            $aadhaarCardPath = $document->aadhaar_card ?? null;
            $panCardPath = $document->pan_card ?? null;

            if ($request->hasFile('resume')) {
                if ($document && $document->resume) {
                    Storage::disk('local')->delete($document->resume);
                }
                $resumePath = $request->file('resume')->store('documents/resumes', 'local');
            }

            if ($request->hasFile('aadhaar_card')) {
                if ($document && $document->aadhaar_card) {
                    Storage::disk('local')->delete($document->aadhaar_card);
                }
                $aadhaarCardPath = $request->file('aadhaar_card')->store('documents/aadhaar', 'local');
            }

            if ($request->hasFile('pan_card')) {
                if ($document && $document->pan_card) {
                    Storage::disk('local')->delete($document->pan_card);
                }
                $panCardPath = $request->file('pan_card')->store('documents/pan', 'local');
            }

            if ($document) {
                $document->update([
                    'resume' => $resumePath,
                    'aadhaar_card' => $aadhaarCardPath,
                    'pan_card' => $panCardPath,
                ]);
            } else {
                EmployeeDocument::create([
                    'personal_information_id' => $id,
                    'resume' => $resumePath,
                    'aadhaar_card' => $aadhaarCardPath,
                    'pan_card' => $panCardPath,
                ]);
            }

            return redirect()->route('employees.edit', ['id' => $id, 'step' => 'emergency'])->with('success', 'Documents updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Employee document update failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to update documents. Please try again.')->withInput();
        }
    }

    /**
     * Update emergency contact.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateEmergencyContact(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can edit employees.');
        }

        $employee = PersonalInformation::find($id);
        if (!$employee) {
            return redirect()->route('employees.index')->with('error', 'Employee not found');
        }

        $validated = $request->validate([
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|in:spouse,parent,sibling,friend,other',
            'emergency_contact_number' => 'nullable|numeric|digits:10',
            'emergency_contact_address' => 'nullable|string|max:500',
        ]);

        $emergencyContact = $employee->emergencyContact;
        if ($emergencyContact) {
            $emergencyContact->update([
                'contact_name' => $validated['emergency_contact_name'] ?? null,
                'relationship' => $validated['emergency_contact_relationship'] ?? null,
                'contact_number' => $validated['emergency_contact_number'] ?? null,
                'address' => $validated['emergency_contact_address'] ?? null,
            ]);
        } else {
            EmergencyContact::create([
                'personal_information_id' => $id,
                'contact_name' => $validated['emergency_contact_name'] ?? null,
                'relationship' => $validated['emergency_contact_relationship'] ?? null,
                'contact_number' => $validated['emergency_contact_number'] ?? null,
                'address' => $validated['emergency_contact_address'] ?? null,
            ]);
        }

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully!');
    }

    /**
     * Toggle employee status (active/inactive).
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus($id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'Only admin can change employee status.');
        }

        $employee = PersonalInformation::find($id);
        if (!$employee) {
            return redirect()->route('employees.index')->with('error', 'Employee not found');
        }

        $employmentDetail = $employee->employmentDetail;
        if (!$employmentDetail) {
            return redirect()->route('employees.index')->with('error', 'Employment details not found');
        }

        // Toggle status
        $newStatus = $employmentDetail->status === 'active' ? 'inactive' : 'active';
        $employmentDetail->update(['status' => $newStatus]);

        $statusText = $newStatus === 'active' ? 'activated' : 'deactivated';
        return redirect()->route('employees.index')->with('success', "Employee {$statusText} successfully!");
    }
}
