IEPMS – Innobles Employee Payroll Management System
A web-based Human Resource Management System (HRMS) built with Laravel 12 that brings employee records, payroll, attendance, leave and reimbursements into one role-based application.
Status: In active development.

✨ Features
👤 Employee Management
Complete employee profile: personal info, employment details, salary, bank details, emergency contact
Secure KYC / document uploads (Aadhaar, PAN, resume) served only to the owner or an admin
Employee activation workflow with password setup
Activate / deactivate employees
💰 Payroll
Per-employee salary structure (HRA, special/other allowance, PF / ESI / PT flags, monthly TDS)
Company-level payroll rules (PF/ESI rates, professional tax, weekly off, late-mark rule)
Monthly salary generation with automatic deductions (PF, ESI, tax, unpaid leave, absence, late)
Review & edit deductions → approval workflow → payment recording → downloadable payslips
Bulk approval, payment history, partial/cumulative payments with overpayment protection
🕒 Attendance
Daily check-in / check-out with face-capture verification
One check-in per day, attendance history, admin-only deletion
🌴 Leave Management
Configurable leave types (Casual, Sick, Earned, Maternity, Paternity, custom)
Leave balances, overlap checks, attachments (e.g. medical certificate)
Approval workflow, leave calendar, history, company holidays
🧾 Reimbursements
Category-based claims with per-claim and per-month limits
Multi-item claims, receipt upload
Approve / reject (single or bulk), payment processing
Approved claims can be added directly into payroll
5 reports with CSV export (employee, category, monthly, pending, payments)
📊 Reports & Notifications
Monthly payroll, department-wise salary, attendance summary, leave and payment history reports
In-app notifications for leave, reimbursement and payroll events
🔐 Security
Role-based access control, login rate limiting, hashed reset tokens
Private file storage for sensitive documents
Session encryption enabled

👥 User Roles
Role	Access
Admin	Full access – users, employees, payroll, attendance, leave, reimbursements, reports, role assignments
Manager	Review and approve team requests (leave, reimbursements)
HR	Leave and reimbursement management, limited employee management
CTO	Technical team oversight, payroll approval
Employee	Own profile, attendance, leave requests, reimbursement claims, payslips

🛠️ Tech Stack
Backend: PHP 8.2+, Laravel 12
Database: MySQL
Frontend: Blade templates, custom CSS/JS (Vite + Tailwind tooling available)
Email: Zoho ZeptoMail API
Testing: PHPUnit

🏢 About
Developed for Innobles.
