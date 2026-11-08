@extends('layouts.app')

@section('title', 'Submit Reimbursement Claim - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Submit Reimbursement Claim</h1>
            <p class="text-muted mb-0">Submit your expense for reimbursement</p>
        </div>
        <a href="{{ route('reimbursements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Claims
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('reimbursements.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" data-requires-receipt="{{ $category->requires_receipt }}" data-max-claim="{{ $category->max_amount_per_claim }}" data-max-month="{{ $category->max_amount_per_month }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                    @if($category->max_amount_per_claim)
                                        (Max: ₹{{ number_format($category->max_amount_per_claim, 2) }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Expense Date *</label>
                        <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" required max="{{ date('Y-m-d') }}" value="{{ old('expense_date') }}">
                        @error('expense_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Amount (₹) *</label>
                        <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" step="0.01" min="0.01" required value="{{ old('amount') }}">
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Receipt Upload</label>
                        <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">Accepted formats: PDF, JPG, PNG (Max 5MB)</small>
                        @error('receipt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Line items <small class="text-muted">(optional - the claim amount becomes the total of the items)</small></label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn"><i class="bi bi-plus-lg me-1"></i>Add item</button>
                    </div>
                    @error('items.*.name') <div class="text-danger small">{{ $message }}</div> @enderror
                    @error('items.*.quantity') <div class="text-danger small">{{ $message }}</div> @enderror
                    @error('items.*.unit_price') <div class="text-danger small">{{ $message }}</div> @enderror
                    <div id="itemRows"></div>
                    <div class="text-end fw-bold mt-2" id="itemsTotal" style="display:none;"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" required placeholder="Provide details about your expense...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info" id="categoryInfo" style="display: none;">
                    <i class="bi bi-info-circle me-2"></i>
                    <span id="categoryInfoText"></span>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Submit Claim
                    </button>
                    <a href="{{ route('reimbursements.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.querySelector('select[name="category_id"]');
        const receiptInput = document.querySelector('input[name="receipt"]');
        const categoryInfo = document.getElementById('categoryInfo');
        const categoryInfoText = document.getElementById('categoryInfoText');

        function updateCategoryInfo() {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const requiresReceipt = selectedOption.dataset.requiresReceipt === 'true';
            const maxClaim = parseFloat(selectedOption.dataset.maxClaim);
            const maxMonth = parseFloat(selectedOption.dataset.maxMonth);

            let info = [];

            if (requiresReceipt) {
                receiptInput.required = true;
                info.push('Receipt is required for this category.');
            } else {
                receiptInput.required = false;
            }

            if (maxClaim) {
                info.push('Maximum amount per claim: ₹' + maxClaim.toFixed(2));
            }

            if (maxMonth) {
                info.push('Maximum amount per month: ₹' + maxMonth.toFixed(2));
            }

            if (info.length > 0) {
                categoryInfoText.textContent = info.join(' ');
                categoryInfo.style.display = 'block';
            } else {
                categoryInfo.style.display = 'none';
            }
        }

        categorySelect.addEventListener('change', updateCategoryInfo);

        // Trigger on page load if a category is selected
        if (categorySelect.value) {
            updateCategoryInfo();
        }
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const rows = document.getElementById('itemRows');
        const addBtn = document.getElementById('addItemBtn');
        const totalBox = document.getElementById('itemsTotal');
        const amountInput = document.querySelector('input[name="amount"]');
        let index = 0;

        function recalc() {
            const lines = rows.querySelectorAll('.item-row');
            if (!lines.length) {
                totalBox.style.display = 'none';
                amountInput.readOnly = false;
                return;
            }
            let total = 0;
            lines.forEach(function (line) {
                const qty = parseFloat(line.querySelector('.item-qty').value) || 0;
                const price = parseFloat(line.querySelector('.item-price').value) || 0;
                total += qty * price;
            });
            amountInput.value = total.toFixed(2);
            amountInput.readOnly = true;
            totalBox.textContent = 'Items total: ₹' + total.toFixed(2);
            totalBox.style.display = 'block';
        }

        function addRow() {
            const line = document.createElement('div');
            line.className = 'row g-2 mb-2 item-row';
            line.innerHTML =
                '<div class="col-md-6"><input type="text" name="items[' + index + '][name]" class="form-control" placeholder="Item name" maxlength="255" required></div>' +
                '<div class="col-md-2"><input type="number" name="items[' + index + '][quantity]" class="form-control item-qty" placeholder="Qty" min="1" step="1" value="1" required></div>' +
                '<div class="col-md-3"><input type="number" name="items[' + index + '][unit_price]" class="form-control item-price" placeholder="Unit price" min="0.01" step="0.01" required></div>' +
                '<div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 remove-item"><i class="bi bi-x-lg"></i></button></div>';
            rows.appendChild(line);
            index++;
            recalc();
        }

        addBtn.addEventListener('click', addRow);
        rows.addEventListener('input', recalc);
        rows.addEventListener('click', function (event) {
            const btn = event.target.closest('.remove-item');
            if (btn) {
                btn.closest('.item-row').remove();
                recalc();
            }
        });
    });
    </script>
@endsection
