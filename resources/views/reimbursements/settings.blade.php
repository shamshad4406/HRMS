@extends('layouts.app')

@section('title', 'Reimbursement Settings - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Reimbursement Settings</h1>
            <p class="text-muted mb-0">Manage reimbursement categories and limits</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
            <i class="bi bi-plus-lg me-2"></i>Add Category
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Max per Claim</th>
                            <th>Max per Month</th>
                            <th>Receipt Required</th>
                            <th>Approval Required</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td><code>{{ $category->code }}</code></td>
                                <td>{{ $category->max_amount_per_claim ? '₹' . number_format($category->max_amount_per_claim, 2) : '-' }}</td>
                                <td>{{ $category->max_amount_per_month ? '₹' . number_format($category->max_amount_per_month, 2) : '-' }}</td>
                                <td>
                                    @if($category->requires_receipt)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($category->requires_approval)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('reimbursements.delete.category', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No categories found. Click "Add Category" to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Reimbursement Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('reimbursements.store.category') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Category Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required value="{{ old('name') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Code *</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" required placeholder="e.g., TRAVEL, MEDICAL" value="{{ old('code') }}">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Max Amount per Claim (₹)</label>
                                <input type="number" name="max_amount_per_claim" class="form-control @error('max_amount_per_claim') is-invalid @enderror" step="0.01" min="0" value="{{ old('max_amount_per_claim') }}">
                                @error('max_amount_per_claim')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Max Amount per Month (₹)</label>
                                <input type="number" name="max_amount_per_month" class="form-control @error('max_amount_per_month') is-invalid @enderror" step="0.01" min="0" value="{{ old('max_amount_per_month') }}">
                                @error('max_amount_per_month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-check mb-2">
                            <input type="hidden" name="requires_receipt" value="0">
                            <input class="form-check-input" type="checkbox" name="requires_receipt" id="requiresReceipt" value="1" checked>
                            <label class="form-check-label" for="requiresReceipt">Requires Receipt</label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="hidden" name="requires_approval" value="0">
                            <input class="form-check-input" type="checkbox" name="requires_approval" id="requiresApproval" value="1" checked>
                            <label class="form-check-label" for="requiresApproval">Requires Approval</label>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" checked>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modals -->
    @foreach($categories as $category)
        <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('reimbursements.update.category', $category->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>Please fix the following errors:</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Category Name *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ $category->name }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Code *</label>
                                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ $category->code }}" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ $category->description }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Max Amount per Claim (₹)</label>
                                    <input type="number" name="max_amount_per_claim" class="form-control @error('max_amount_per_claim') is-invalid @enderror" step="0.01" min="0" value="{{ $category->max_amount_per_claim }}">
                                    @error('max_amount_per_claim')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Max Amount per Month (₹)</label>
                                    <input type="number" name="max_amount_per_month" class="form-control @error('max_amount_per_month') is-invalid @enderror" step="0.01" min="0" value="{{ $category->max_amount_per_month }}">
                                    @error('max_amount_per_month')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-check mb-2">
                                <input type="hidden" name="requires_receipt" value="0">
                                <input class="form-check-input" type="checkbox" name="requires_receipt" id="requiresReceipt{{ $category->id }}" value="1" {{ $category->requires_receipt ? 'checked' : '' }}>
                                <label class="form-check-label" for="requiresReceipt{{ $category->id }}">Requires Receipt</label>
                            </div>
                            <div class="form-check mb-2">
                                <input type="hidden" name="requires_approval" value="0">
                                <input class="form-check-input" type="checkbox" name="requires_approval" id="requiresApproval{{ $category->id }}" value="1" {{ $category->requires_approval ? 'checked' : '' }}>
                                <label class="form-check-label" for="requiresApproval{{ $category->id }}">Requires Approval</label>
                            </div>
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive{{ $category->id }}" value="1" {{ $category->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive{{ $category->id }}">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-open add modal if there are errors
        @if($errors->any() && !request()->has('edit'))
            var addModal = new bootstrap.Modal(document.getElementById('categoryModal'));
            addModal.show();
        @endif

        // Auto-open edit modal if there are errors
        @if($errors->any() && request()->has('edit'))
            var editModalId = 'editCategoryModal{{ request()->input('edit') }}';
            var editModal = new bootstrap.Modal(document.getElementById(editModalId));
            if (editModal) {
                editModal.show();
            }
        @endif
    });
    </script>
@endsection
