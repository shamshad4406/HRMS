<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReimbursementController;
use App\Http\Controllers\SecureFileController;
use App\Http\Controllers\ReimbursementReportController;
use App\Http\Controllers\RoleAssignmentController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:6,1');
// Public registration disabled for security - admins should be created via admin panel or seeder
// Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
// Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:3,1');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update')->middleware('throttle:5,1');

// Admin-only user management routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/users', [AuthController::class, 'listUsers'])->name('users');
    Route::get('/users/create', [AuthController::class, 'showCreateUserForm'])->name('users.create');
    Route::post('/users', [AuthController::class, 'createUser'])->name('users.store');
    Route::delete('/users/{id}', [AuthController::class, 'deleteUser'])->name('users.delete');
});

// Role Assignment Routes (Admin only)
Route::prefix('role-assignments')->name('role-assignments.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [RoleAssignmentController::class, 'index'])->name('index');
    Route::get('/{id}/edit', [RoleAssignmentController::class, 'edit'])->name('edit');
    Route::put('/{id}', [RoleAssignmentController::class, 'update'])->name('update');
    Route::get('/hierarchy', [RoleAssignmentController::class, 'hierarchy'])->name('hierarchy');
});

// Change Password Route
Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('change.password')->middleware(['auth', 'check.employee.status']);
Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change.password.post')->middleware(['auth', 'check.employee.status', 'throttle:5,1']);

// Dashboard Route
Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard')->middleware(['auth', 'check.employee.status']);

// Employee Routes (Protected)
Route::prefix('employees')->name('employees.')->middleware(['auth', 'check.employee.status'])->group(function () {
    Route::get('/', [EmployeeController::class, 'index'])->name('index');
    Route::get('/create', [EmployeeController::class, 'create'])->name('create');
    Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('edit');
    Route::put('/{id}/personal-info', [EmployeeController::class, 'updatePersonalInfo'])->name('update.personal');
    Route::put('/{id}/employment-details', [EmployeeController::class, 'updateEmploymentDetails'])->name('update.employment');
    Route::put('/{id}/salary-details', [EmployeeController::class, 'updateSalaryDetails'])->name('update.salary');
    Route::put('/{id}/bank-details', [EmployeeController::class, 'updateBankDetails'])->name('update.bank');
    Route::put('/{id}/documents', [EmployeeController::class, 'updateDocuments'])->name('update.documents');
    Route::put('/{id}/emergency-contact', [EmployeeController::class, 'updateEmergencyContact'])->name('update.emergency');
    Route::post('/{id}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('toggle.status');
    Route::get('/credentials', [EmployeeController::class, 'showCredentials'])->name('credentials');
    Route::post('/personal-info', [EmployeeController::class, 'storePersonalInfo'])->name('store.personal');
    Route::post('/employment-details', [EmployeeController::class, 'storeEmploymentDetails'])->name('store.employment');
    Route::post('/salary-details', [EmployeeController::class, 'storeSalaryDetails'])->name('store.salary');
    Route::post('/bank-details', [EmployeeController::class, 'storeBankDetails'])->name('store.bank');
    Route::post('/documents', [EmployeeController::class, 'storeDocuments'])->name('store.documents');
    Route::post('/emergency-contact', [EmployeeController::class, 'storeEmergencyContact'])->name('store.emergency');
    Route::get('/{id}/documents/{type}', [SecureFileController::class, 'employeeDocument'])->name('documents.show');
    Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('destroy');
});

// Payroll Routes (Protected)
Route::prefix('payrolls')->name('payrolls.')->middleware(['auth', 'check.employee.status'])->group(function () {
    Route::get('/', [PayrollController::class, 'index'])->name('index');
    Route::get('/create', [PayrollController::class, 'create'])->name('create');
    Route::post('/', [PayrollController::class, 'store'])->name('store');
    Route::delete('/{id}', [PayrollController::class, 'destroy'])->name('destroy');

    // Salary structure & payroll rules (admin)
    Route::get('/salary-structure', [PayrollController::class, 'salaryStructure'])->name('salary.structure');
    Route::post('/salary-structure/settings', [PayrollController::class, 'updatePayrollSettings'])->name('salary.settings');
    Route::post('/salary-structure/{id}', [PayrollController::class, 'updateSalaryStructure'])->name('salary.structure.update');
    
    // Workflow Routes
    Route::get('/generate', [PayrollController::class, 'showGenerateSalary'])->name('generate');
    Route::post('/generate', [PayrollController::class, 'generateSalary'])->name('generate.salary');
    Route::get('/{id}/review-deductions', [PayrollController::class, 'showReviewDeductions'])->name('review.deductions');
    Route::post('/{id}/deductions', [PayrollController::class, 'storeDeductions'])->name('store.deductions');
    Route::get('/{id}/approve', [PayrollController::class, 'showApprovePayroll'])->name('approve');
    Route::post('/{id}/approve', [PayrollController::class, 'approvePayroll'])->name('approve.action');
    Route::get('/{id}/record-payment', [PayrollController::class, 'showRecordPayment'])->name('record.payment');
    Route::post('/{id}/record-payment', [PayrollController::class, 'recordPayment'])->name('record.payment.action');
    Route::get('/{id}/payslip', [PayrollController::class, 'generatePayslip'])->name('payslip');
    Route::get('/{id}/history', [PayrollController::class, 'showPaymentHistory'])->name('history');
    Route::get('/bulk-approval', [PayrollController::class, 'showBulkApproval'])->name('bulk.approval');
});

// Attendance Routes (Protected)
Route::prefix('attendances')->name('attendances.')->middleware(['auth', 'check.employee.status'])->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('index');
    Route::get('/create', [AttendanceController::class, 'create'])->name('create');
    Route::post('/', [AttendanceController::class, 'store'])->name('store');
    Route::post('/{id}/checkout', [AttendanceController::class, 'checkout'])->name('checkout');
    Route::delete('/{id}', [AttendanceController::class, 'destroy'])->name('destroy');
});

// API Routes for face verification
Route::get('/api/employee-photo/{id}', [AttendanceController::class, 'getEmployeePhoto'])->middleware('auth');

// Reports Routes (Protected - Admin only)
Route::prefix('reports')->name('reports.')->middleware(['auth', 'check.employee.status'])->group(function () {
    Route::get('/', [ReportsController::class, 'index'])->name('index');
    Route::get('/monthly-payroll', [ReportsController::class, 'monthlyPayroll'])->name('monthly.payroll');
    Route::get('/department-salary', [ReportsController::class, 'departmentSalary'])->name('department.salary');
    Route::get('/attendance-summary', [ReportsController::class, 'attendanceSummary'])->name('attendance.summary');
    Route::get('/leave-report', [ReportsController::class, 'leaveReport'])->name('leave');
});

// Payment History (Accessible to all authenticated users)
Route::get('/reports/payment-history', [ReportsController::class, 'paymentHistory'])->name('reports.payment.history')->middleware(['auth', 'check.employee.status']);

// Notification Routes (Protected)
Route::prefix('notifications')->name('notifications.')->middleware(['auth', 'check.employee.status'])->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark.all.read');
});

// Leave Management Routes (Protected)
Route::prefix('leave')->name('leave.')->middleware(['auth', 'check.employee.status'])->group(function () {
    Route::get('/', [LeaveController::class, 'index'])->name('index');
    Route::get('/create', [LeaveController::class, 'create'])->name('create');
    Route::post('/', [LeaveController::class, 'store'])->name('store');
    Route::get('/approvals', [LeaveController::class, 'approvals'])->name('approvals')->middleware('can.approve.leave');
    Route::post('/{id}/status', [LeaveController::class, 'updateStatus'])->name('update.status')->middleware('can.approve.leave');
    Route::get('/balance', [LeaveController::class, 'balance'])->name('balance');
    Route::get('/calendar', [LeaveController::class, 'calendar'])->name('calendar');
    Route::get('/history', [LeaveController::class, 'history'])->name('history');
    Route::get('/settings', [LeaveController::class, 'settings'])->name('settings');
    Route::get('/leave-types', function() {
        return redirect()->route('leave.settings');
    })->name('leave.types');
    Route::get('/leave-types/{id}', [LeaveController::class, 'getLeaveType'])->name('get.leave.type');
    Route::post('/leave-types', [LeaveController::class, 'storeLeaveType'])->name('store.leave.type');
    Route::put('/leave-types/{id}', [LeaveController::class, 'updateLeaveType'])->name('update.leave.type');
    Route::delete('/leave-types/{id}', [LeaveController::class, 'deleteLeaveType'])->name('delete.leave.type');
    Route::post('/holidays', [LeaveController::class, 'storeHoliday'])->name('store.holiday');
    Route::post('/initialize-balances', [LeaveController::class, 'initializeBalances'])->name('initialize.balances');
    Route::get('/{id}/attachment', [SecureFileController::class, 'leaveAttachment'])->name('attachment');
});

// Reimbursement Routes (Protected)
Route::prefix('reimbursements')->name('reimbursements.')->middleware(['auth', 'check.employee.status'])->group(function () {
    // Employee Routes (Static routes first)
    Route::get('/', [ReimbursementController::class, 'index'])->name('index');
    Route::get('/create', [ReimbursementController::class, 'create'])->name('create');
    Route::post('/', [ReimbursementController::class, 'store'])->name('store');

    // Admin/HR/Manager Routes (Static routes first)
    Route::get('/all', [ReimbursementController::class, 'allClaims'])->name('all');
    Route::get('/approvals', [ReimbursementController::class, 'approvals'])->name('approvals');
    Route::post('/bulk-process', [ReimbursementController::class, 'bulkProcess'])->name('bulk.process');
    Route::get('/reports', [ReimbursementReportController::class, 'index'])->name('reports');
    Route::get('/settings', [ReimbursementController::class, 'settings'])->name('settings')->middleware('admin');
    Route::post('/categories', [ReimbursementController::class, 'storeCategory'])->name('store.category')->middleware('admin');
    Route::put('/categories/{id}', [ReimbursementController::class, 'updateCategory'])->name('update.category')->middleware('admin');
    Route::delete('/categories/{id}', [ReimbursementController::class, 'deleteCategory'])->name('delete.category')->middleware('admin');

    // Dynamic routes (must come last)
    Route::get('/{id}', [ReimbursementController::class, 'show'])->name('show');
    Route::delete('/{id}', [ReimbursementController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/receipt', [SecureFileController::class, 'reimbursementReceipt'])->name('receipt');
    Route::post('/{id}/approve', [ReimbursementController::class, 'approveClaim'])->name('approve');
    Route::get('/{id}/payment', [ReimbursementController::class, 'processPayment'])->name('payment');
    Route::post('/{id}/payment', [ReimbursementController::class, 'recordPayment'])->name('record.payment');
});
