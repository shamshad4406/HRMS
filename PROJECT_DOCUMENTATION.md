# IEPMS - Innobles Employee Payroll Management System

## Project Overview

IEPMS is a comprehensive Human Resource Management System (HRMS) built with Laravel 12.0 that manages employee data, payroll processing, attendance tracking, leave management, and reimbursement claims. The system provides role-based access control for different user types (Admin, Manager, HR, CTO, Employee).

---

## Technology Stack

- **Backend Framework**: Laravel 12.0 (PHP 8.2+)
- **Database**: MySQL
- **Frontend**: Blade Templates with Bootstrap
- **Authentication**: Laravel Auth System
- **File Storage**: Laravel Filesystem

---

## User Roles & Permissions

### 1. Admin
- Full system access
- User management (create/edit/delete users)
- Employee management (full CRUD)
- Payroll processing and approval
- Attendance management
- Leave approval
- Reimbursement approval and settings
- Report generation
- Role assignments

### 2. Manager
- View all reimbursements
- Approve/reject reimbursements
- View pending approvals
- Manage assigned team members

### 3. HR
- View all reimbursements
- Approve/reject reimbursements
- View pending approvals
- Employee management (limited)
- Leave management

### 4. CTO
- Technical team oversight
- Access to technical employee data

### 5. Employee
- View own profile
- Submit reimbursement claims
- Request leave
- View own attendance
- View own payroll/payslips
- View payment history

---

## Core Modules

### 1. Authentication & User Management

#### Features:
- User login/logout with session management
- Password reset functionality (email-based)
- Change password (authenticated users)
- User creation by admin
- Employee activation workflow
- Role-based access control

#### Key Files:
- Controller: `app/Http/Controllers/AuthController.php`
- Routes: `routes/web.php` (lines 20-40)
- Models: `app/Models/User.php`

#### Routes:
- `GET /login` - Login form
- `POST /login` - Login action
- `POST /logout` - Logout
- `GET /forgot-password` - Forgot password form
- `POST /forgot-password` - Send reset link
- `GET /reset-password/{token}` - Reset password form
- `POST /reset-password` - Reset password action
- `GET /change-password` - Change password form
- `POST /change-password` - Change password action
- `GET /admin/users` - List all users (Admin only)
- `GET /admin/users/create` - Create user form (Admin only)
- `POST /admin/users` - Store new user (Admin only)
- `DELETE /admin/users/{id}` - Delete user (Admin only)

---

### 2. Employee Management

#### Features:
- Complete employee profile management
- Personal information (name, email, contact, Aadhaar, PAN)
- Employment details (designation, department, joining date)
- Salary details (basic salary, allowances, deductions)
- Bank details (account number, IFSC, bank name)
- Document management (Aadhaar, PAN, offer letter, etc.)
- Emergency contact information
- Employee status toggle (active/inactive)
- User credential generation for employees

#### Key Files:
- Controller: `app/Http/Controllers/EmployeeController.php`
- Routes: `routes/web.php` (lines 57-78)
- Models: `app/Models/PersonalInformation.php`, `EmploymentDetail.php`, `SalaryDetail.php`, `BankDetail.php`, `EmployeeDocument.php`, `EmergencyContact.php`

#### Database Tables:
- `personal_information` - Basic employee details
- `employment_details` - Job-related information
- `salary_details` - Salary structure
- `bank_details` - Bank account information
- `employee_documents` - Document storage
- `emergency_contacts` - Emergency contact info

#### Routes:
- `GET /employees` - List all employees
- `GET /employees/create` - Create employee form
- `GET /employees/{id}/edit` - Edit employee
- `PUT /employees/{id}/personal-info` - Update personal info
- `PUT /employees/{id}/employment-details` - Update employment details
- `PUT /employees/{id}/salary-details` - Update salary details
- `PUT /employees/{id}/bank-details` - Update bank details
- `PUT /employees/{id}/documents` - Update documents
- `PUT /employees/{id}/emergency-contact` - Update emergency contact
- `POST /employees/{id}/toggle-status` - Toggle employee status
- `GET /employees/credentials` - Show user credentials
- `POST /employees/personal-info` - Store personal info
- `POST /employees/employment-details` - Store employment details
- `POST /employees/salary-details` - Store salary details
- `POST /employees/bank-details` - Store bank details
- `POST /employees/documents` - Store documents
- `POST /employees/emergency-contact` - Store emergency contact
- `GET /employees/{id}/documents/{type}` - View document
- `DELETE /employees/{id}` - Delete employee

---

### 3. Payroll Management

#### Features:
- Salary structure configuration
- Monthly payroll generation
- Deduction management
- Payroll approval workflow
- Payment recording
- Payslip generation
- Payment history tracking
- Bulk approval system
- Payroll settings management

#### Workflow:
1. **Salary Structure Setup**: Define base salary, allowances, deductions
2. **Generate Salary**: Create monthly payroll for all/selected employees
3. **Review Deductions**: Admin reviews and adjusts deductions
4. **Approve Payroll**: Manager/CTO approves payroll
5. **Record Payment**: Record payment method, date, reference
6. **Generate Payslip**: Generate downloadable payslip

#### Key Files:
- Controller: `app/Http/Controllers/PayrollController.php`
- Routes: `routes/web.php` (lines 80-104)
- Models: `app/Models/Payroll.php`, `PayrollDeductionDetail.php`, `PayrollPayment.php`, `PayrollSetting.php`

#### Database Tables:
- `payrolls` - Main payroll records
- `payroll_deduction_details` - Deduction breakdown
- `payroll_payments` - Payment records
- `payroll_settings` - Global payroll settings

#### Routes:
- `GET /payrolls` - List all payrolls
- `GET /payrolls/create` - Create payroll form
- `POST /payrolls` - Store payroll
- `DELETE /payrolls/{id}` - Delete payroll
- `GET /payrolls/salary-structure` - View salary structure
- `POST /payrolls/salary-structure/settings` - Update payroll settings
- `POST /payrolls/salary-structure/{id}` - Update salary structure
- `GET /payrolls/generate` - Generate salary form
- `POST /payrolls/generate` - Generate salary action
- `GET /payrolls/{id}/review-deductions` - Review deductions
- `POST /payrolls/{id}/deductions` - Store deductions
- `GET /payrolls/{id}/approve` - Approve payroll form
- `POST /payrolls/{id}/approve` - Approve payroll action
- `GET /payrolls/{id}/record-payment` - Record payment form
- `POST /payrolls/{id}/record-payment` - Record payment action
- `GET /payrolls/{id}/payslip` - Generate payslip
- `GET /payrolls/{id}/history` - Payment history
- `GET /payrolls/bulk-approval` - Bulk approval

---

### 4. Attendance Management

#### Features:
- Employee check-in/check-out
- Face verification for attendance
- Attendance history tracking
- Attendance deletion (admin only)
- Employee photo retrieval for verification

#### Key Files:
- Controller: `app/Http/Controllers/AttendanceController.php`
- Routes: `routes/web.php` (lines 106-117)
- Model: `app/Models/Attendance.php`

#### Database Tables:
- `attendances` - Attendance records with face verification data

#### Routes:
- `GET /attendances` - List all attendances
- `GET /attendances/create` - Create attendance form
- `POST /attendances` - Store attendance
- `POST /attendances/{id}/checkout` - Checkout action
- `DELETE /attendances/{id}` - Delete attendance
- `GET /api/employee-photo/{id}` - Get employee photo for verification

---

### 5. Leave Management

#### Features:
- Leave request submission
- Leave type configuration (Casual, Sick, Earned, etc.)
- Leave balance tracking
- Leave approval workflow
- Leave calendar view
- Leave history
- Holiday management
- Leave attachment support
- Paid/Unpaid leave classification

#### Leave Types:
- Casual Leave
- Sick Leave
- Earned Leave
- Maternity Leave
- Paternity Leave
- Other custom types

#### Workflow:
1. Employee submits leave request with dates and reason
2. Optional attachment upload (medical certificate, etc.)
3. Manager/HR reviews request
4. Manager/HR approves or rejects with reason
5. Leave balance updated automatically
6. Notification sent to employee

#### Key Files:
- Controller: `app/Http/Controllers/LeaveController.php`
- Routes: `routes/web.php` (lines 137-158)
- Models: `app/Models/Leave.php`, `LeaveType.php`, `LeaveBalance.php`, `Holiday.php`

#### Database Tables:
- `leaves` - Leave requests
- `leave_types` - Leave type definitions
- `leave_balances` - Employee leave balances
- `holidays` - Company holidays

#### Routes:
- `GET /leave` - Leave dashboard
- `GET /leave/create` - Create leave request form
- `POST /leave` - Store leave request
- `GET /leave/approvals` - Pending approvals (Admin/Manager/HR)
- `POST /leave/{id}/status` - Update leave status
- `GET /leave/balance` - View leave balance
- `GET /leave/calendar` - Leave calendar view
- `GET /leave/history` - Leave history
- `GET /leave/settings` - Leave settings
- `GET /leave/leave-types` - Redirect to settings
- `GET /leave/leave-types/{id}` - Get leave type
- `POST /leave/leave-types` - Store leave type
- `PUT /leave/leave-types/{id}` - Update leave type
- `DELETE /leave/leave-types/{id}` - Delete leave type
- `POST /leave/holidays` - Store holiday
- `POST /leave/initialize-balances` - Initialize leave balances
- `GET /leave/{id}/attachment` - View leave attachment

---

### 6. Reimbursement Management

#### Features:
- Reimbursement claim submission
- Category-based reimbursement (Travel, Medical, Internet, etc.)
- Receipt upload support
- Multi-item claims support
- Category limits (max per claim, max per month)
- Approval workflow
- Payment processing
- Reimbursement reports
- Category settings management

#### Reimbursement Categories:
- Travel Expenses
- Medical Expenses
- Internet/Phone
- Training & Development
- Books & Reference Material
- Equipment
- Other custom categories

#### Workflow:
1. Employee selects category and fills expense details
2. Uploads receipt (if required by category)
3. System validates category limits
4. Claim submitted with status "Pending"
5. Admin/Manager/HR reviews claim
6. Claim approved or rejected with reason
7. If approved, payment processed
8. Status updated to "Paid"
9. Notification sent at each stage

#### Key Files:
- Controller: `app/Http/Controllers/ReimbursementController.php`
- Report Controller: `app/Http/Controllers/ReimbursementReportController.php`
- Routes: `routes/web.php` (lines 160-184)
- Models: `app/Models/ReimbursementClaim.php`, `ReimbursementCategory.php`, `ReimbursementItem.php`

#### Database Tables:
- `reimbursement_categories` - Category definitions
- `reimbursement_claims` - Reimbursement claims
- `reimbursement_items` - Individual items in claims

#### Routes:
- `GET /reimbursements` - My reimbursements (Employee)
- `GET /reimbursements/create` - Create claim form
- `POST /reimbursements` - Store claim
- `GET /reimbursements/all` - All claims (Admin only)
- `GET /reimbursements/approvals` - Pending approvals (Admin only)
- `POST /reimbursements/bulk-process` - Bulk approve/reject
- `GET /reimbursements/reports` - Reimbursement reports
- `GET /reimbursements/settings` - Category settings (Admin only)
- `POST /reimbursements/categories` - Store category (Admin only)
- `PUT /reimbursements/categories/{id}` - Update category (Admin only)
- `DELETE /reimbursements/categories/{id}` - Delete category (Admin only)
- `GET /reimbursements/{id}` - View claim details
- `DELETE /reimbursements/{id}` - Delete claim
- `GET /reimbursements/{id}/receipt` - View receipt
- `POST /reimbursements/{id}/approve` - Approve/reject claim
- `GET /reimbursements/{id}/payment` - Payment form
- `POST /reimbursements/{id}/payment` - Record payment

---

### 7. Reports & Analytics

#### Features:
- Monthly payroll reports
- Department-wise salary reports
- Attendance summary reports
- Leave reports
- Payment history reports
- Reimbursement reports

#### Key Files:
- Controller: `app/Http/Controllers/ReportsController.php`
- Routes: `routes/web.php` (lines 118-128)

#### Routes:
- `GET /reports` - Reports dashboard
- `GET /reports/monthly-payroll` - Monthly payroll report
- `GET /reports/department-salary` - Department salary report
- `GET /reports/attendance-summary` - Attendance summary
- `GET /reports/leave-report` - Leave report
- `GET /reports/payment-history` - Payment history (all users)

---

### 8. Notification System

#### Features:
- Real-time notifications
- Notification types for different events
- Mark as read functionality
- Mark all as read
- Notification history

#### Notification Types:
- User activation
- Leave pending/approved/rejected
- Reimbursement pending/approved/rejected/paid
- Payroll generated/approved/paid
- Password reset

#### Key Files:
- Controller: `app/Http/Controllers/NotificationController.php`
- Routes: `routes/web.php` (lines 130-135)
- Model: `app/Models/Notification.php`

#### Database Tables:
- `notifications` - Notification records

#### Routes:
- `GET /notifications` - List notifications
- `POST /notifications/{id}/read` - Mark as read
- `POST /notifications/mark-all-read` - Mark all as read

---

### 9. Role Assignment Management

#### Features:
- Define organizational hierarchy
- Assign managers to employees
- Role-based approval chains
- Visual hierarchy view

#### Key Files:
- Controller: `app/Http/Controllers/RoleAssignmentController.php`
- Routes: `routes/web.php` (lines 42-48)

#### Routes:
- `GET /role-assignments` - List role assignments
- `GET /role-assignments/{id}/edit` - Edit role assignment
- `PUT /role-assignments/{id}` - Update role assignment
- `GET /role-assignments/hierarchy` - View hierarchy

---

### 10. Secure File Management

#### Features:
- Secure document access
- Employee document retrieval
- Leave attachment access
- Reimbursement receipt access
- Role-based file access control

#### Key Files:
- Controller: `app/Http/Controllers/SecureFileController.php`
- Routes: `routes/web.php` (lines 12, 76, 157, 180)

#### Routes:
- `GET /employees/{id}/documents/{type}` - View employee document
- `GET /leave/{id}/attachment` - View leave attachment
- `GET /reimbursements/{id}/receipt` - View reimbursement receipt

---

## Database Schema Overview

### Core Tables:
1. **users** - User accounts with roles
2. **personal_information** - Employee basic information
3. **employment_details** - Employment-related information
4. **salary_details** - Salary structure
5. **bank_details** - Bank account information
6. **employee_documents** - Document storage
7. **emergency_contacts** - Emergency contact information
8. **payrolls** - Payroll records
9. **payroll_deduction_details** - Deduction breakdown
10. **payroll_payments** - Payment records
11. **payroll_settings** - Global payroll settings
12. **attendances** - Attendance records
13. **leaves** - Leave requests
14. **leave_types** - Leave type definitions
15. **leave_balances** - Employee leave balances
16. **holidays** - Company holidays
17. **reimbursement_categories** - Reimbursement categories
18. **reimbursement_claims** - Reimbursement claims
19. **reimbursement_items** - Reimbursement line items
20. **notifications** - Notification records
21. **password_resets** - Password reset tokens

---

## Security Features

### Authentication & Authorization:
- Session-based authentication
- Password hashing (bcrypt)
- Rate limiting on login (6 attempts per minute)
- Rate limiting on password reset (3 attempts per minute)
- Role-based middleware
- Employee status check middleware

### Data Protection:
- CSRF protection on all forms
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade templating)
- Secure file access control
- Validation on all inputs

### Access Control:
- Role-based access control (RBAC)
- Manager approval chains
- Separation of duties (Admins cannot approve own claims when multiple admins exist)
- Document access control based on role

---

## Key Workflows

### Employee Onboarding Workflow:
1. Admin creates employee record in system
2. Admin fills personal information
3. Admin fills employment details
4. Admin fills salary details
4. Admin fills bank details
5. Admin uploads documents
6. Admin adds emergency contact
7. System generates user credentials
8. Admin shares credentials with employee
9. Employee logs in and sets password
10. Employee profile becomes active

### Payroll Processing Workflow:
1. Admin configures salary structure and settings
2. Admin generates monthly payroll for employees
3. Admin reviews and adjusts deductions
4. Manager/CTO approves payroll
5. Admin records payment details
6. System generates payslip
7. Employee can view and download payslip

### Leave Request Workflow:
1. Employee submits leave request
2. System checks leave balance
3. Manager/HR receives notification
4. Manager/HR reviews request
5. Manager/HR approves or rejects
6. System updates leave balance
7. Employee receives notification
8. Leave recorded in calendar

### Reimbursement Claim Workflow:
1. Employee selects reimbursement category
2. Employee fills expense details
3. Employee uploads receipt (if required)
4. System validates category limits
5. Claim submitted (status: Pending)
6. Admin/Manager/HR receives notification
7. Admin/Manager/HR reviews claim
8. Admin/Manager/HR approves or rejects
9. If approved, payment processed
10. Employee receives notification
11. Status updated to Paid

---

## File Structure

```
iepms/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── AttendanceController.php
│   │   │   ├── EmployeeController.php
│   │   │   ├── LeaveController.php
│   │   │   ├── NotificationController.php
│   │   │   ├── PayrollController.php
│   │   │   ├── ReimbursementController.php
│   │   │   ├── ReimbursementReportController.php
│   │   │   ├── ReportsController.php
│   │   │   ├── RoleAssignmentController.php
│   │   │   └── SecureFileController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── Attendance.php
│   │   ├── BankDetail.php
│   │   ├── EmergencyContact.php
│   │   ├── EmployeeDocument.php
│   │   ├── EmploymentDetail.php
│   │   ├── Holiday.php
│   │   ├── Leave.php
│   │   ├── LeaveBalance.php
│   │   ├── LeaveType.php
│   │   ├── Notification.php
│   │   ├── Payroll.php
│   │   ├── PayrollDeductionDetail.php
│   │   ├── PayrollPayment.php
│   │   ├── PayrollSetting.php
│   │   ├── PersonalInformation.php
│   │   ├── ReimbursementCategory.php
│   │   ├── ReimbursementClaim.php
│   │   ├── ReimbursementItem.php
│   │   ├── SalaryDetail.php
│   │   └── User.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── layouts/
│       ├── partials/
│       ├── reimbursements/
│       ├── leave/
│       ├── payroll/
│       ├── reports/
│       └── role_assignments/
├── routes/
│   └── web.php
└── storage/
    └── app/
        └── public/
```

---

## Setup Instructions

### Prerequisites:
- PHP 8.2 or higher
- Composer
- MySQL
- Node.js & NPM

### Installation Steps:
1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env`
4. Configure database in `.env`
5. Run `php artisan key:generate`
6. Run `php artisan migrate`
7. Run `php artisan db:seed` (to seed initial data)
8. Run `npm install`
9. Run `npm run build`
10. Run `php artisan serve`

### Database Seeding:
- UserSeeder - Creates admin user
- EmployeeSeeder - Creates sample employees
- LeaveDataSeeder - Creates leave types and holidays
- ReimbursementCategorySeeder - Creates reimbursement categories

---

## Middleware

### Custom Middleware:
1. **admin** - Restricts access to admin users only
2. **check.employee.status** - Checks if employee profile is active
3. **can.approve.leave** - Checks if user can approve leaves

---

## Environment Variables

Key `.env` variables:
- `APP_NAME` - Application name
- `APP_ENV` - Environment (local/production)
- `APP_KEY` - Application key
- `APP_URL` - Application URL
- `DB_CONNECTION` - Database connection (mysql)
- `DB_HOST` - Database host
- `DB_PORT` - Database port
- `DB_DATABASE` - Database name
- `DB_USERNAME` - Database username
- `DB_PASSWORD` - Database password
- `MAIL_MAILER` - Mail driver
- `MAIL_HOST` - Mail host
- `MAIL_PORT` - Mail port
- `MAIL_USERNAME` - Mail username
- `MAIL_PASSWORD` - Mail password
- `MAIL_ENCRYPTION` - Mail encryption

---

## Features Summary

### For Employees:
- Profile management
- Attendance check-in/check-out
- Leave request submission
- Leave balance tracking
- Reimbursement claim submission
- Payslip viewing
- Payment history
- Notifications

### For HR:
- Employee management
- Leave approval
- Attendance tracking
- Reimbursement approval
- Report generation

### For Managers:
- Team management
- Leave approval
- Reimbursement approval
- Attendance monitoring
- Team reports

### For Admin:
- Full system access
- User management
- Payroll processing
- Role assignments
- System settings
- Report generation
- Category management

---

## Future Enhancements

Potential improvements:
- Mobile app support
- Biometric attendance integration
- Advanced analytics dashboard
- Performance management module
- Recruitment module
- Training management
- Expense management integration
- API for third-party integrations
- Multi-company support
- Advanced reporting with charts
- Email notification system
- SMS notifications
- Document signature integration

---

## Support & Maintenance

For issues or questions:
- Check logs in `storage/logs`
- Review database migrations
- Check middleware configurations
- Review role assignments
- Verify user permissions

---

## License

This project is proprietary software for Innobles HRMS.

---

*Documentation generated on 2026-10-31*
