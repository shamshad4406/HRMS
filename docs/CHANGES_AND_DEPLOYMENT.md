# IEPMS – changes made in this version & how to deploy them

Full gap analysis (what was wrong, why it matters, what is still open) is in the separate
"IEPMS Gap Analysis" document. This file is the developer checklist.

## 1. Deploy steps (in this order)

1. **Back up** the database and the `storage/app` folder.
2. Copy this project over your existing one (`.env` is yours – only `APP_DEBUG=false` and `SESSION_ENCRYPT=true` were changed).
3. `php artisan migrate`  (also adds `reimbursement_claims.payroll_id`, salary structure columns and the `payroll_settings` table)  
   - drops `users.plain_text_password` (values are wiped first)  
   - adds a unique key `payrolls(personal_information_id, month, year)` and list/report indexes  
     (if duplicate payrolls exist the unique key is skipped with a warning)
4. `php artisan files:secure --dry-run` then `php artisan files:secure`  
   moves Aadhaar/PAN/resume files, reimbursement receipts and leave attachments from `storage/app/public` (publicly reachable) to `storage/app/private`. Employee photos stay public (used as avatars).
5. `php artisan optimize:clear`
6. **Reset passwords.** Plain-text passwords were stored for 5 of 11 users (including the admin) and are in every old DB dump / backup. Treat them as compromised: change the admin password and make employees reset theirs (Forgot password).
7. Production: `APP_ENV=production`, `APP_DEBUG=false`, a dedicated MySQL user (not `root`), HTTPS and `SESSION_SECURE_COOKIE=true`.
8. Optional: review and run `database/fixes/cleanup_duplicate_attendance.sql`.

## 2. What changed

| Area | File(s) | Change |
|---|---|---|
| Passwords | `AuthController`, `EmployeeController`, `User`, `admin/users.blade.php`, migration | No plain-text password is stored or shown any more; password column removed from the users page; min length 8 |
| Password reset | `AuthController` | Reset link is never shown on screen; token stored hashed; same response for unknown emails; remember-me tokens rotated |
| Brute force | `routes/web.php` | Rate limit on login, forgot/reset/change password |
| Payroll | `PayrollController` | Admin-only create/store/destroy; payslip & history only for admin or the owner; approved/paid payrolls cannot be deleted/re-approved/edited; payments must be on approved payrolls, cumulative, no overpayment, row-locked |
| Employees | `EmployeeController` | Delete is admin-only, transactional, blocked when payroll exists, removes files; credentials passed via session (not URL) and admin-only; upload type validation |
| Attendance | `AttendanceController` | Checkout/delete/photo lookup restricted to owner or admin; one check-in per day; average hours no longer leaks other users' data |
| Leave | `LeaveController` | Overlap check; pending requests reserve balance; auto-approved types deduct balance; approval is transactional and uses the leave's own year; employee is notified; yearly balance auto-created; inactive types rejected |
| Reimbursement | `ReimbursementController` | Monthly cap uses the expense month (was always the current month); auto-approved claims get `approved_at`; no self-approval when another admin exists; receipts private |
| Files | `SecureFileController`, `SecureUploads` command, routes, 4 views | Receipts, leave attachments, KYC documents are served only to owner/admin |
| XSS | `AuthController::dashboard` | Employee names escaped in dashboard activity feed |
| Reimbursement → Payroll | `PayrollController`, `ReimbursementController`, models, migration `2026_10_28_100000`, 4 views | *Generate Salary* can include approved unpaid claims (adds to allowances, links claim to payroll); claims become *paid* when the payroll is fully paid; linked claims cannot be paid separately; rejected payroll frees its claims |
| Payroll structure & auto deductions (F3) | `PayrollCalculator`, `PayrollSetting`, `PayrollController`, `SalaryDetail`, migration `2026_10_28_110000`, `payroll/salary_structure.blade.php`, generate view | Salary structure per employee (HRA, special/other allowance, PF/ESI/PT flags, monthly TDS); company rules page (divisor, weekly off, PF/ESI rates, professional tax, late rule); *Generate Salary* auto-fills allowances and deduction lines (PF, ESI, tax, unpaid leave, absent, late) which are still editable on the review screen; waived deductions are no longer counted; net salary never below 0 |
| Reimbursement features (F2) | `ReimbursementController`, `ReimbursementReportController`, create/show/approvals/reports views | Multi-item claims (amount = sum of items), bulk approve/reject, 5 reports (employee, category, monthly, pending, payment) with CSV export |
| Config | `.env`, `.env.example` | Debug off, session encryption on, MySQL + Zoho placeholders |

## 3. Not changed (needs a product decision) – see the gap document
HR role is selectable but has no extra permissions · mid-month joiner pro-rating · TDS slabs (a fixed monthly TDS amount is supported) · payroll month still stored as text · server-side face verification · automated tests · soft deletes / audit log.

> Note: PHP was not available in the environment where these changes were written, so the code was
> reviewed manually but **not executed**. Please run the app (and `php artisan test`) in a staging copy first.
