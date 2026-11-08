<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReimbursementCategory;
use App\Models\ReimbursementClaim;
use App\Models\PersonalInformation;
use App\Models\Notification;
use App\Http\Controllers\NotificationController;

class ReimbursementController extends Controller
{
    /**
     * Display a listing of claims for the authenticated user.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user->personal_information_id) {
            return redirect()->route('dashboard')->with('error', 'Your profile is not linked to any employee record.');
        }

        $claims = ReimbursementClaim::with(['category', 'approvedBy', 'processedBy'])
            ->where('personal_information_id', $user->personal_information_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('reimbursements.index', compact('claims'));
    }

    /**
     * Show the form for creating a new claim.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = auth()->user();

        if (!$user->personal_information_id) {
            return redirect()->route('dashboard')->with('error', 'Your profile is not linked to any employee record.');
        }

        $categories = ReimbursementCategory::where('is_active', true)->get();

        return view('reimbursements.create', compact('categories'));
    }

    /**
     * Store a newly created claim.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->personal_information_id) {
            return redirect()->route('dashboard')->with('error', 'Your profile is not linked to any employee record.');
        }

        // Optional line items: ignore completely empty rows, then validate the rest
        $items = collect($request->input('items', []))
            ->filter(fn ($item) => is_array($item) && (filled($item['name'] ?? null) || filled($item['unit_price'] ?? null)))
            ->values()
            ->all();
        $request->merge(['items' => $items]);

        $validated = $request->validate([
            'category_id' => 'required|exists:reimbursement_categories,id',
            'expense_date' => 'required|date|before_or_equal:today',
            'amount' => $items ? 'nullable|numeric' : 'required|numeric|min:0.01',
            'description' => 'required|string|min:10',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'items' => 'nullable|array|max:20',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1|max:1000',
            'items.*.unit_price' => 'required|numeric|min:0.01',
        ]);

        // With line items the claim amount is always the sum of the items (never trust the posted total)
        if ($items) {
            $validated['amount'] = round(collect($validated['items'])->sum(fn ($i) => $i['quantity'] * $i['unit_price']), 2);
        }

        $category = ReimbursementCategory::findOrFail($validated['category_id']);

        // Check if receipt is required
        if ($category->requires_receipt && !$request->hasFile('receipt')) {
            return redirect()->back()->with('error', 'Receipt is required for this category.')->withInput();
        }

        // Check max amount per claim
        if ($category->max_amount_per_claim && $validated['amount'] > $category->max_amount_per_claim) {
            return redirect()->back()->with('error', "Maximum amount per claim is ₹{$category->max_amount_per_claim}")->withInput();
        }

        // Check max amount per month
        if ($category->max_amount_per_month) {
            $expenseMonth = \Carbon\Carbon::parse($validated['expense_date']);

            $currentMonthTotal = ReimbursementClaim::where('personal_information_id', $user->personal_information_id)
                ->where('category_id', $validated['category_id'])
                ->whereMonth('expense_date', $expenseMonth->month)
                ->whereYear('expense_date', $expenseMonth->year)
                ->where('status', '!=', 'rejected')
                ->sum('amount');

            if (($currentMonthTotal + $validated['amount']) > $category->max_amount_per_month) {
                return redirect()->back()->with('error', "Maximum amount per month is ₹{$category->max_amount_per_month}. You have already claimed ₹{$currentMonthTotal} for {$expenseMonth->format('F Y')}.")->withInput();
            }
        }

        // Handle receipt upload
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('reimbursements', 'local');
        }

        $claim = ReimbursementClaim::create([
            'personal_information_id' => $user->personal_information_id,
            'category_id' => $validated['category_id'],
            'claim_date' => now(),
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'expense_date' => $validated['expense_date'],
            'receipt_path' => $receiptPath,
            'status' => $category->requires_approval ? 'pending' : 'approved',
            // Categories that need no approval are auto-approved; record that in the audit trail
            'approved_at' => $category->requires_approval ? null : now(),
        ]);

        foreach ($validated['items'] ?? [] as $item) {
            \App\Models\ReimbursementItem::create([
                'claim_id' => $claim->id,
                'item_name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_amount' => round($item['quantity'] * $item['unit_price'], 2),
            ]);
        }

        // Create notification for admin users (only when their approval is needed)
        if ($claim->status === 'pending') {
            NotificationController::createForAdmins(
                'reimbursement_pending',
                'New Reimbursement Claim',
                "New reimbursement claim of ₹{$validated['amount']} submitted by {$user->name} for {$category->name}.",
                route('reimbursements.approvals'),
                ['claim_id' => $claim->id, 'employee_id' => $user->personal_information_id]
            );
        }

        return redirect()->route('reimbursements.index')->with('success', 'Reimbursement claim submitted successfully!');
    }

    /**
     * Display the specified claim.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = auth()->user();
        $claim = ReimbursementClaim::with(['category', 'personalInformation', 'approvedBy', 'processedBy', 'items'])
            ->findOrFail($id);

        // Check if user has access to this claim
        if ($user->role !== 'admin' && $claim->personal_information_id !== $user->personal_information_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('reimbursements.show', compact('claim'));
    }

    /**
     * Remove the specified claim.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $claim = ReimbursementClaim::findOrFail($id);

        // Check if user has access to this claim
        if ($user->role !== 'admin' && $claim->personal_information_id !== $user->personal_information_id) {
            abort(403, 'Unauthorized action.');
        }

        // Only pending claims can be deleted
        if ($claim->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending claims can be deleted.');
        }

        // Delete receipt file
        if ($claim->receipt_path) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($claim->receipt_path);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($claim->receipt_path); // legacy location
        }

        $claim->delete();

        return redirect()->route('reimbursements.index')->with('success', 'Claim deleted successfully!');
    }

    /**
     * Display all claims (admin view).
     *
     * @return \Illuminate\View\View
     */
    /**
     * Approve or reject several pending claims at once.
     */
    public function bulkProcess(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'claim_ids' => 'required|array|min:1',
            'claim_ids.*' => 'integer|exists:reimbursement_claims,id',
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:500',
        ], [
            'claim_ids.required' => 'Select at least one claim.',
            'rejection_reason.required_if' => 'Enter a reason to reject the selected claims.',
        ]);

        $multipleAdmins = \App\Models\User::where('role', 'admin')->count() > 1;
        $ownPersonalInfoId = (int) auth()->user()->personal_information_id;
        $processed = 0;
        $skipped = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $multipleAdmins, $ownPersonalInfoId, &$processed, &$skipped) {
            $claims = ReimbursementClaim::with(['personalInformation.user', 'category'])
                ->whereIn('id', $validated['claim_ids'])
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();

            // Claims that were already processed in the meantime are skipped too
            $skipped += count($validated['claim_ids']) - $claims->count();

            foreach ($claims as $claim) {
                // Same separation-of-duties rule as single approval
                if ($multipleAdmins && (int) $claim->personal_information_id === $ownPersonalInfoId) {
                    $skipped++;
                    continue;
                }

                $approve = $validated['action'] === 'approve';

                if ($approve) {
                    $claim->status = 'approved';
                    $claim->approved_by = auth()->id();
                    $claim->approved_at = now();
                } else {
                    $claim->status = 'rejected';
                    $claim->rejection_reason = $validated['rejection_reason'];
                }
                $claim->save();
                $processed++;

                if ($claim->personalInformation && $claim->personalInformation->user) {
                    NotificationController::createNotification(
                        $claim->personalInformation->user->id,
                        $approve ? 'reimbursement_approved' : 'reimbursement_rejected',
                        $approve ? 'Reimbursement Approved' : 'Reimbursement Rejected',
                        $approve
                            ? "Your reimbursement claim of ₹{$claim->amount} for {$claim->category->name} has been approved."
                            : "Your reimbursement claim of ₹{$claim->amount} for {$claim->category->name} has been rejected. Reason: {$validated['rejection_reason']}",
                        route('reimbursements.show', $claim->id),
                        ['claim_id' => $claim->id]
                    );
                }
            }
        });

        $verb = $validated['action'] === 'approve' ? 'approved' : 'rejected';
        $message = "{$processed} claim(s) {$verb}." . ($skipped ? " {$skipped} skipped (already processed or your own claim)." : '');

        return redirect()->route('reimbursements.approvals')->with($processed ? 'success' : 'error', $message);
    }

    public function allClaims()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $claims = ReimbursementClaim::with(['category', 'personalInformation', 'approvedBy', 'processedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('reimbursements.all', compact('claims'));
    }

    /**
     * Display pending claims for approval.
     *
     * @return \Illuminate\View\View
     */
    public function approvals()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $pendingClaims = ReimbursementClaim::with(['category', 'personalInformation'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reimbursements.approvals', compact('pendingClaims'));
    }

    /**
     * Approve or reject a claim.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveClaim(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string',
        ]);

        $claim = ReimbursementClaim::with(['personalInformation.user', 'category'])->findOrFail($id);

        if ($claim->status !== 'pending') {
            return redirect()->back()->with('error', 'This claim has already been processed.');
        }

        // Separation of duties: an admin cannot approve their own claim when another admin exists
        // (a sole admin is allowed, otherwise their claims could never be processed).
        if ((int) $claim->personal_information_id === (int) auth()->user()->personal_information_id
            && \App\Models\User::where('role', 'admin')->count() > 1) {
            return redirect()->back()->with('error', 'You cannot approve or reject your own claim. Another admin must review it.');
        }

        if ($validated['action'] === 'approve') {
            $claim->status = 'approved';
            $claim->approved_by = auth()->id();
            $claim->approved_at = now();

            // Notify employee
            if ($claim->personalInformation->user) {
                NotificationController::createNotification(
                    $claim->personalInformation->user->id,
                    'reimbursement_approved',
                    'Reimbursement Approved',
                    "Your reimbursement claim of ₹{$claim->amount} for {$claim->category->name} has been approved.",
                    route('reimbursements.show', $claim->id),
                    ['claim_id' => $claim->id]
                );
            }

            $message = 'Claim approved successfully!';
        } else {
            $claim->status = 'rejected';
            $claim->rejection_reason = $validated['rejection_reason'];

            // Notify employee
            if ($claim->personalInformation->user) {
                NotificationController::createNotification(
                    $claim->personalInformation->user->id,
                    'reimbursement_rejected',
                    'Reimbursement Rejected',
                    "Your reimbursement claim of ₹{$claim->amount} for {$claim->category->name} has been rejected. Reason: {$validated['rejection_reason']}",
                    route('reimbursements.show', $claim->id),
                    ['claim_id' => $claim->id]
                );
            }

            $message = 'Claim rejected.';
        }

        $claim->save();

        return redirect()->route('reimbursements.approvals')->with('success', $message);
    }

    /**
     * Show payment processing form.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function processPayment($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $claim = ReimbursementClaim::with(['category', 'personalInformation'])->findOrFail($id);

        if ($claim->payroll_id) {
            return redirect()->route('reimbursements.show', $claim->id)->with('error', 'This claim is included in a payroll and will be paid with the salary.');
        }

        if ($claim->status !== 'approved') {
            return redirect()->back()->with('error', 'Only approved claims can be processed for payment.');
        }

        return view('reimbursements.payment', compact('claim'));
    }

    /**
     * Record payment for a claim.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recordPayment(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:bank_transfer,cash,cheque,other',
            'payment_date' => 'required|date',
            'payment_reference' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $claim = ReimbursementClaim::with(['personalInformation.user', 'category'])->findOrFail($id);

        if ($claim->payroll_id) {
            return redirect()->route('reimbursements.show', $claim->id)->with('error', 'This claim is included in a payroll and will be paid with the salary.');
        }

        if ($claim->status !== 'approved') {
            return redirect()->back()->with('error', 'Only approved claims can be processed for payment.');
        }

        $claim->status = 'paid';
        $claim->payment_method = $validated['payment_method'];
        $claim->payment_date = $validated['payment_date'];
        $claim->payment_reference = $validated['payment_reference'];
        $claim->remarks = $validated['remarks'];
        $claim->processed_by = auth()->id();
        $claim->processed_at = now();
        $claim->save();

        // Notify employee
        if ($claim->personalInformation->user) {
            NotificationController::createNotification(
                $claim->personalInformation->user->id,
                'reimbursement_paid',
                'Reimbursement Paid',
                "Your reimbursement of ₹{$claim->amount} for {$claim->category->name} has been paid via {$validated['payment_method']}.",
                route('reimbursements.show', $claim->id),
                ['claim_id' => $claim->id]
            );
        }

        return redirect()->route('reimbursements.all')->with('success', 'Payment recorded successfully!');
    }

    /**
     * Display reimbursement settings.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $categories = ReimbursementCategory::all();

        return view('reimbursements.settings', compact('categories'));
    }

    /**
     * Store a new reimbursement category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCategory(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:reimbursement_categories,code',
            'description' => 'nullable|string',
            'max_amount_per_claim' => 'nullable|numeric|min:0',
            'max_amount_per_month' => 'nullable|numeric|min:0',
            'requires_receipt' => 'nullable',
            'requires_approval' => 'nullable',
            'is_active' => 'nullable',
        ]);

        // Convert checkbox values to boolean
        $validated['requires_receipt'] = $request->boolean('requires_receipt');
        $validated['requires_approval'] = $request->boolean('requires_approval');
        $validated['is_active'] = $request->boolean('is_active');

        ReimbursementCategory::create($validated);

        return redirect()->route('reimbursements.settings')->with('success', 'Category created successfully!');
    }

    /**
     * Update a reimbursement category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCategory(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:reimbursement_categories,code,' . $id,
            'description' => 'nullable|string',
            'max_amount_per_claim' => 'nullable|numeric|min:0',
            'max_amount_per_month' => 'nullable|numeric|min:0',
            'requires_receipt' => 'nullable',
            'requires_approval' => 'nullable',
            'is_active' => 'nullable',
        ]);

        // Convert checkbox values to boolean
        $validated['requires_receipt'] = $request->boolean('requires_receipt');
        $validated['requires_approval'] = $request->boolean('requires_approval');
        $validated['is_active'] = $request->boolean('is_active');

        $category = ReimbursementCategory::findOrFail($id);
        $category->update($validated);

        return redirect()->route('reimbursements.settings')->with('success', 'Category updated successfully!');
    }

    /**
     * Delete a reimbursement category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteCategory($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $category = ReimbursementCategory::findOrFail($id);

        // Check if category has claims
        if ($category->claims()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with existing claims.');
        }

        $category->delete();

        return redirect()->route('reimbursements.settings')->with('success', 'Category deleted successfully!');
    }
}
