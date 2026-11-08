# Reimbursement System - Complete Plan

## Overview
Employee और HR के लिए Reimbursement System जिससे employees expenses claims कर सकें और HR/admin approve करके reimbursement process कर सकें।

---

## 1. Database Structure

### 1.1 Reimbursement Categories Table
```sql
Table: reimbursement_categories
- id (bigint, primary key)
- name (string) - e.g., Travel, Medical, Internet, Training, Books, Equipment
- code (string, unique) - e.g., TRAVEL, MEDICAL, INTERNET
- description (text, nullable)
- max_amount_per_claim (decimal, nullable) - maximum amount per single claim
- max_amount_per_month (decimal, nullable) - maximum amount per month
- requires_receipt (boolean, default: true)
- requires_approval (boolean, default: true)
- is_active (boolean, default: true)
- created_at (timestamp)
- updated_at (timestamp)
```

### 1.2 Reimbursement Claims Table
```sql
Table: reimbursement_claims
- id (bigint, primary key)
- personal_information_id (bigint, foreign key -> personal_information)
- category_id (bigint, foreign key -> reimbursement_categories)
- claim_date (date)
- amount (decimal)
- description (text)
- expense_date (date)
- receipt_path (string, nullable) - file path for receipt
- status (enum: pending, approved, rejected, paid)
- rejection_reason (text, nullable)
- approved_by (bigint, foreign key -> users, nullable)
- approved_at (timestamp, nullable)
- processed_by (bigint, foreign key -> users, nullable)
- processed_at (timestamp, nullable)
- payment_date (date, nullable)
- payment_method (enum: bank_transfer, cash, cheque, other, nullable)
- payment_reference (string, nullable)
- remarks (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### 1.3 Reimbursement Items Table (for multiple items in one claim)
```sql
Table: reimbursement_items
- id (bigint, primary key)
- claim_id (bigint, foreign key -> reimbursement_claims)
- item_name (string)
- item_description (text, nullable)
- quantity (int, default: 1)
- unit_price (decimal)
- total_amount (decimal)
- receipt_path (string, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

---

## 2. Models

### 2.1 ReimbursementCategory Model
```php
Location: app/Models/ReimbursementCategory.php

Properties:
- $fillable: name, code, description, max_amount_per_claim, max_amount_per_month,
             requires_receipt, requires_approval, is_active

Relationships:
- hasMany(ReimbursementClaim::class, 'category_id')
```

### 2.2 ReimbursementClaim Model
```php
Location: app/Models/ReimbursementClaim.php

Properties:
- $fillable: personal_information_id, category_id, claim_date, amount, description,
             expense_date, receipt_path, status, rejection_reason, approved_by,
             approved_at, processed_by, processed_at, payment_date, payment_method,
             payment_reference, remarks

Relationships:
- belongsTo(PersonalInformation::class, 'personal_information_id')
- belongsTo(ReimbursementCategory::class, 'category_id')
- belongsTo(User::class, 'approved_by')
- belongsTo(User::class, 'processed_by')
- hasMany(ReimbursementItem::class, 'claim_id')
```

### 2.3 ReimbursementItem Model
```php
Location: app/Models/ReimbursementItem.php

Properties:
- $fillable: claim_id, item_name, item_description, quantity, unit_price,
             total_amount, receipt_path

Relationships:
- belongsTo(ReimbursementClaim::class, 'claim_id')
```

---

## 3. Controllers

### 3.1 ReimbursementController
```php
Location: app/Http/Controllers/ReimbursementController.php

Methods:

// Employee Methods
- index() - List all claims for logged-in user
- create() - Show form to create new claim
- store(Request $request) - Store new reimbursement claim
- show($id) - Show claim details
- destroy($id) - Delete claim (only if pending)

// Admin/HR Methods
- allClaims() - List all claims (admin view)
- approvals() - Show pending claims for approval
- approveClaim(Request $request, $id) - Approve or reject claim
- processPayment($id) - Show payment processing form
- recordPayment(Request $request, $id) - Record payment for claim
- settings() - Show reimbursement settings
- storeCategory(Request $request) - Create new reimbursement category
- updateCategory(Request $request, $id) - Update category
- deleteCategory($id) - Delete category
```

---

## 4. Routes

```php
Location: routes/web.php

Route::prefix('reimbursements')->name('reimbursements.')->middleware('auth')->group(function () {
    // Employee Routes
    Route::get('/', [ReimbursementController::class, 'index'])->name('index');
    Route::get('/create', [ReimbursementController::class, 'create'])->name('create');
    Route::post('/', [ReimbursementController::class, 'store'])->name('store');
    Route::get('/{id}', [ReimbursementController::class, 'show'])->name('show');
    Route::delete('/{id}', [ReimbursementController::class, 'destroy'])->name('destroy');

    // Admin/HR Routes
    Route::get('/all', [ReimbursementController::class, 'allClaims'])->name('all');
    Route::get('/approvals', [ReimbursementController::class, 'approvals'])->name('approvals');
    Route::post('/{id}/approve', [ReimbursementController::class, 'approveClaim'])->name('approve');
    Route::get('/{id}/payment', [ReimbursementController::class, 'processPayment'])->name('payment');
    Route::post('/{id}/payment', [ReimbursementController::class, 'recordPayment'])->name('record.payment');
    Route::get('/settings', [ReimbursementController::class, 'settings'])->name('settings')->middleware('admin');
    Route::post('/categories', [ReimbursementController::class, 'storeCategory'])->name('store.category')->middleware('admin');
    Route::put('/categories/{id}', [ReimbursementController::class, 'updateCategory'])->name('update.category')->middleware('admin');
    Route::delete('/categories/{id}', [ReimbursementController::class, 'deleteCategory'])->name('delete.category')->middleware('admin');
});
```

---

## 5. Views (Blade Templates)

### 5.1 Employee Views
```
Location: resources/views/reimbursements/

- index.blade.php - List of employee's claims
- create.blade.php - Form to create new claim
- show.blade.php - Claim details
```

### 5.2 Admin/HR Views
```
Location: resources/views/reimbursements/

- all.blade.php - All claims list (admin view)
- approvals.blade.php - Pending claims for approval
- payment.blade.php - Payment processing form
- settings.blade.php - Reimbursement categories and settings
```

---

## 6. Workflow / Process Flow

### 6.1 Employee Claim Submission Flow
```
1. Employee Login
   ↓
2. Go to Reimbursements Section
   ↓
3. Click "New Claim"
   ↓
4. Select Category (Travel, Medical, etc.)
   ↓
5. Fill Details:
   - Expense Date
   - Amount
   - Description
   - Upload Receipt (if required)
   ↓
6. Submit Claim
   ↓
7. Status: Pending (waiting for approval)
   ↓
8. Notification sent to Admin/HR
```

### 6.2 Admin/HR Approval Flow
```
1. Admin/HR Login
   ↓
2. Go to Reimbursements > Approvals
   ↓
3. View Pending Claims
   ↓
4. Review Claim Details:
   - Employee Info
   - Category
   - Amount
   - Receipt
   - Description
   ↓
5. Take Action:
   - Approve: Change status to "Approved"
   - Reject: Enter rejection reason, change status to "Rejected"
   ↓
6. If Approved:
   - Notification sent to Employee
   - Status: Approved (waiting for payment)
```

### 6.3 Payment Processing Flow
```
1. Admin/HR goes to Approved Claims
   ↓
2. Select Claim for Payment
   ↓
3. Fill Payment Details:
   - Payment Method (Bank Transfer, Cash, etc.)
   - Payment Date
   - Payment Reference
   ↓
4. Record Payment
   ↓
5. Status: Paid
   ↓
6. Notification sent to Employee
   ↓
7. Optional: Integrate with Payroll (deduct/add to next salary)
```

---

## 7. Key Features

### 7.1 For Employees
- Submit reimbursement claims
- Upload receipts
- Track claim status
- View claim history
- View payment status
- Download receipt copies

### 7.2 For Admin/HR
- View all claims
- Approve/reject claims
- Process payments
- Manage reimbursement categories
- Set limits (max amount per claim/month)
- Generate reports
- View payment history

### 7.3 System Features
- Receipt upload and storage
- Automatic notifications
- Status tracking
- Approval workflow
- Payment recording
- Category-based limits
- Receipt requirement enforcement
- Audit trail (who approved, when)

---

## 8. Notifications Integration

### 8.1 Notification Types
```php
'reimbursement_pending' - New claim submitted
'reimbursement_approved' - Claim approved
'reimbursement_rejected' - Claim rejected
'reimbursement_paid' - Payment processed
```

### 8.2 When to Create Notifications
- When employee submits claim → Notify all admins
- When admin approves claim → Notify employee
- When admin rejects claim → Notify employee
- When payment is processed → Notify employee

---

## 9. Integration with Payroll (Optional but Recommended)

### 9.1 Options
1. **Direct Payment**: Reimbursement paid separately from payroll
2. **Payroll Integration**: Add to next month's salary as allowance

### 9.2 Implementation for Payroll Integration
```php
// When processing payroll, check for approved reimbursements
$approvedReimbursements = ReimbursementClaim::where('personal_information_id', $employeeId)
    ->where('status', 'approved')
    ->where('payment_date', null)
    ->get();

// Add to allowances
$totalReimbursement = $approvedReimbursements->sum('amount');
$payroll->allowances += $totalReimbursement;

// Mark as included in payroll
$approvedReimbursements->update(['payment_date' => $payroll->payment_date]);
```

---

## 10. Reports

### 10.1 Report Types
1. **Employee-wise Report**: All claims by an employee
2. **Category-wise Report**: Claims by category
3. **Monthly Report**: Claims in a specific month
4. **Pending Claims Report**: All pending claims
5. **Payment Report**: All paid claims with payment details
6. **Yearly Summary**: Total reimbursements per year

### 10.2 Report Data Points
- Employee name and code
- Category
- Claim date
- Amount
- Status
- Approval date
- Payment date
- Payment method

---

## 11. Security & Validation

### 11.1 Validations
- Amount must be positive
- Receipt required if category requires it
- Amount within category limits
- Expense date cannot be future date
- Only pending claims can be deleted
- Only approved claims can be paid
- Admins can approve/reject only pending claims

### 11.2 Role-based Access
- Employees: Can only view/edit their own claims
- HR: Can view all claims, approve, process payments
- Admin: Full access including settings management

---

## 12. File Structure Summary

```
app/
├── Models/
│   ├── ReimbursementCategory.php
│   ├── ReimbursementClaim.php
│   └── ReimbursementItem.php
├── Http/Controllers/
│   └── ReimbursementController.php

database/
└── migrations/
    ├── create_reimbursement_categories_table.php
    ├── create_reimbursement_claims_table.php
    └── create_reimbursement_items_table.php

resources/views/
└── reimbursements/
    ├── index.blade.php
    ├── create.blade.php
    ├── show.blade.php
    ├── all.blade.php
    ├── approvals.blade.php
    ├── payment.blade.php
    └── settings.blade.php

routes/
└── web.php (add reimbursement routes)
```

---

## 13. Implementation Order (Recommended)

### Phase 1: Basic Structure
1. Create migrations
2. Create models
3. Run migrations

### Phase 2: Admin Setup
4. Create ReimbursementController
5. Add routes
6. Create settings view (manage categories)
7. Implement category CRUD

### Phase 3: Employee Claims
8. Create claim submission form
9. Implement claim store logic
10. Create claim list view for employees
11. Implement receipt upload

### Phase 4: Approval Workflow
12. Create approvals view for admin
13. Implement approve/reject logic
14. Add notifications for approval actions

### Phase 5: Payment Processing
15. Create payment processing form
16. Implement payment recording
17. Add notifications for payment

### Phase 6: Advanced Features
18. Add reports
19. Integrate with payroll (optional)
20. Add multi-item support per claim

---

## 14. Additional Considerations

### 14.1 Receipt Storage
- Store in `storage/app/public/reimbursements/`
- Use Laravel's filesystem
- Consider compression for large files
- Set file size limits (max 5MB)

### 14.2 Audit Trail
- Log who approved/rejected
- Log who processed payment
- Log timestamp changes
- Store original values on updates

### 14.3 Future Enhancements
- Bulk approval of claims
- Automatic approval for small amounts
- Integration with expense management software
- Mobile app support
- Barcode/QR code scanning for receipts
- GST/Invoice matching

---

## Summary

यह Reimbursement System complete flow है जिसमें:
- Employees claim submit कर सकते हैं
- HR/Admin approve/reject कर सकते हैं
- Payment process हो सकती है
- Notifications integrate होंगे
- Optional payroll integration
- Reports और tracking

जब आप implement करना चाहें, तो बता दें, मैं step-by-step implement कर दूंगा।
