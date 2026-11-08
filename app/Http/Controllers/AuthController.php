<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\PersonalInformation;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\EmploymentDetail;
use App\Services\ZohoMailService;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('register');
    }

    /**
     * Handle a registration request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'personal_information_id' => 'nullable|exists:personal_information,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'employee', // Force employee role for public registration
            'personal_information_id' => $validated['personal_information_id'] ?? null,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if employee is inactive (admin users are exempt)
            if ($user->role !== 'admin' && $user->personal_information_id) {
                $employmentDetail = EmploymentDetail::where('personal_information_id', $user->personal_information_id)->first();
                if ($employmentDetail && $employmentDetail->status === 'inactive') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return back()->with('error', 'Your account has been deactivated. Please contact your administrator.')
                                ->withInput($request->only('email'));
                }
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->with('error', 'Invalid email or password.')
                    ->withInput($request->only('email'));
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    /**
     * Show the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Get statistics
        $totalEmployees = PersonalInformation::count();
        
        // Get unique departments
        $departments = EmploymentDetail::distinct('department')->pluck('department');
        $totalDepartments = $departments->count();
        
        // Get today's attendance
        $today = now()->format('Y-m-d');
        $presentToday = Attendance::whereDate('attendance_date', $today)->count();
        
        // Get active leave requests
        $onLeave = Leave::where('status', 'approved')
            ->where('from_date', '<=', $today)
            ->where('to_date', '>=', $today)
            ->count();
        
        // Get recent activities based on user role
        $recentActivities = [];
        if ($user->role === 'admin') {
            // Get recent employees
            $recentEmployees = PersonalInformation::orderBy('created_at', 'desc')->take(3)->get();
            foreach ($recentEmployees as $employee) {
                $time = 'Recently';
                if ($employee->created_at) {
                    if (is_string($employee->created_at)) {
                        $time = \Carbon\Carbon::parse($employee->created_at)->diffForHumans();
                    } else {
                        $time = $employee->created_at->diffForHumans();
                    }
                }
                
                $recentActivities[] = [
                    'icon' => '<i class="bi bi-person-plus-fill"></i>',
                    'text' => "New employee <strong>" . e($employee->first_name . ' ' . $employee->last_name) . "</strong> added",
                    'time' => $time
                ];
            }
            
            // Get recent leave requests
            $recentLeaves = Leave::orderBy('created_at', 'desc')->take(2)->get();
            foreach ($recentLeaves as $leave) {
                $employee = PersonalInformation::find($leave->personal_information_id);
                if ($employee) {
                    $time = 'Recently';
                    if ($leave->created_at) {
                        if (is_string($leave->created_at)) {
                            $time = \Carbon\Carbon::parse($leave->created_at)->diffForHumans();
                        } else {
                            $time = $leave->created_at->diffForHumans();
                        }
                    }
                    
                    $recentActivities[] = [
                        'icon' => '<i class="bi bi-calendar3-fill"></i>',
                        'text' => "Leave request from <strong>" . e($employee->first_name . ' ' . $employee->last_name) . "</strong>",
                        'time' => $time
                    ];
                }
            }
        } else {
            // Employee specific activities
            $recentAttendances = Attendance::where('personal_information_id', $user->personal_information_id)
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();
            
            foreach ($recentAttendances as $attendance) {
                $date = $attendance->attendance_date;
                if (is_string($date)) {
                    $date = date('M d, Y', strtotime($date));
                } elseif ($date instanceof \Carbon\Carbon) {
                    $date = $date->format('M d, Y');
                } else {
                    $date = 'N/A';
                }
                
                $time = 'Recently';
                if ($attendance->created_at) {
                    if (is_string($attendance->created_at)) {
                        $time = \Carbon\Carbon::parse($attendance->created_at)->diffForHumans();
                    } else {
                        $time = $attendance->created_at->diffForHumans();
                    }
                }
                
                $recentActivities[] = [
                    'icon' => '<i class="bi bi-calendar-check-fill"></i>',
                    'text' => "Attendance marked on <strong>" . e($date) . "</strong>",
                    'time' => $time
                ];
            }
            
            $recentLeaves = Leave::where('personal_information_id', $user->personal_information_id)
                ->orderBy('created_at', 'desc')
                ->take(2)
                ->get();
            
            foreach ($recentLeaves as $leave) {
                $time = 'Recently';
                if ($leave->created_at) {
                    if (is_string($leave->created_at)) {
                        $time = \Carbon\Carbon::parse($leave->created_at)->diffForHumans();
                    } else {
                        $time = $leave->created_at->diffForHumans();
                    }
                }
                
                $recentActivities[] = [
                    'icon' => '<i class="bi bi-calendar3-fill"></i>',
                    'text' => "Leave request <strong>" . e($leave->status) . "</strong>",
                    'time' => $time
                ];
            }
        }
        
        return view('dashboard', compact(
            'totalEmployees',
            'totalDepartments', 
            'presentToday',
            'onLeave',
            'recentActivities'
        ));
    }

    /**
     * Show change password form.
     *
     * @return \Illuminate\View\View
     */
    public function showChangePasswordForm()
    {
        return view('change_password');
    }

    /**
     * Handle password change request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ]);

        $user = Auth::user();

        // Check if current password is correct
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        // Update password
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Password changed successfully!');
    }

    /**
     * List all users (Admin only).
     *
     * @return \Illuminate\View\View
     */
    public function listUsers(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $search = $request->input('search');
        $search = is_string($search) ? $search : null;

        $query = User::with(['personalInformation.employmentDetail.reportingManager'])->orderBy('created_at', 'desc');

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10);

        return view('admin.users', compact('users', 'search'));
    }

    /**
     * Show create user form (Admin only).
     *
     * @return \Illuminate\View\View
     */
    public function showCreateUserForm()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $employees = PersonalInformation::all();
        return view('admin.create_user', compact('employees'));
    }

    /**
     * Create a new user (Admin only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createUser(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,hr,manager,employee',
            'personal_information_id' => 'nullable|exists:personal_information,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'personal_information_id' => $validated['personal_information_id'] ?? null,
        ]);

        // If employee is linked, initialize leave balances
        if (!empty($validated['personal_information_id'])) {
            $currentYear = date('Y');
            $leaveTypes = LeaveType::where('is_active', true)->get();

            foreach ($leaveTypes as $leaveType) {
                // firstOrCreate: balances may already exist (unique per employee/type/year)
                LeaveBalance::firstOrCreate(
                    [
                        'personal_information_id' => $validated['personal_information_id'],
                        'leave_type_id' => $leaveType->id,
                        'year' => $currentYear,
                    ],
                    [
                        'total_days' => $leaveType->days_per_year,
                        'used_days' => 0,
                        'remaining_days' => $leaveType->days_per_year,
                        'carried_forward_days' => 0,
                    ]
                );
            }
        }

        return redirect()->route('admin.users')->with('success', 'User created successfully!');
    }

    /**
     * Delete a user (Admin only).
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUser($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $user = User::findOrFail($id);

        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully!');
    }

    /**
     * Show the forgot password form.
     *
     * @return \Illuminate\View\View
     */
    public function showForgotPasswordForm()
    {
        return view('forgot_password');
    }

    /**
     * Send password reset link email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Same response whether or not the account exists (prevents user enumeration)
        $genericResponse = back()->with('success', 'If an account exists for that email, a password reset link has been sent.');

        if (!User::where('email', $request->email)->exists()) {
            return $genericResponse;
        }

        $token = Str::random(60);

        DB::table('password_resets')->where('email', $request->email)->delete();

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => hash('sha256', $token), // only the hash is stored; the raw token is only in the email link
            'created_at' => Carbon::now(),
        ]);

        $resetLink = route('password.reset', ['token' => $token, 'email' => $request->email]);

        // Send email using Zoho Mail Service
        $zohoMailService = new ZohoMailService();
        $htmlContent = view('emails.password_reset', ['resetLink' => $resetLink])->render();
        $emailSent = $zohoMailService->sendEmail($request->email, 'Reset Your Password - Innobles HRMS', $htmlContent);

        if (!$emailSent) {
            // Never show the reset link on screen: that would let anyone take over any account.
            \Illuminate\Support\Facades\Log::error('Password reset email could not be sent.', ['email' => $request->email]);
        }

        return $genericResponse;
    }

    /**
     * Show the reset password form.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function showResetPasswordForm($token)
    {
        return view('reset_password', ['token' => $token]);
    }

    /**
     * Handle password reset request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $passwordReset = DB::table('password_resets')
            ->where('token', hash('sha256', (string) $request->token))
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return back()->withInput($request->only('email'))->with('error', 'Invalid or expired password reset token.');
        }

        // Check if token is expired (60 minutes)
        $tokenAge = Carbon::parse($passwordReset->created_at)->diffInMinutes(Carbon::now());
        if ($tokenAge > 60) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return back()->withInput($request->only('email'))->with('error', 'Password reset token has expired. Please request a new one.');
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withInput($request->only('email'))->with('error', 'Invalid or expired password reset token.');
        }

        $user->password = Hash::make($request->password);
        $user->setRememberToken(Str::random(60)); // log out "remember me" sessions
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Password has been reset successfully! Please login with your new password.');
    }
}
