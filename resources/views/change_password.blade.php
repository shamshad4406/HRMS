@extends('layouts.app')

@section('title', 'Change Password - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header">
        <h1 class="mb-1">Change Password</h1>
        <p class="text-muted mb-0">Update your account password</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('change.password') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-key text-muted"></i>
                                </span>
                                <input 
                                    type="password" 
                                    class="form-control border-start-0 ps-0" 
                                    id="current_password" 
                                    name="current_password" 
                                    placeholder="Enter your current password"
                                    required
                                >
                                <button type="button" class="btn btn-outline-secondary border-start-0" onclick="togglePassword('current_password')">
                                    <i class="bi bi-eye" id="eye-current_password"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input 
                                    type="password" 
                                    class="form-control border-start-0 ps-0" 
                                    id="new_password" 
                                    name="new_password" 
                                    placeholder="Enter your new password"
                                    required
                                >
                                <button type="button" class="btn btn-outline-secondary border-start-0" onclick="togglePassword('new_password')">
                                    <i class="bi bi-eye" id="eye-new_password"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-shield-lock text-muted"></i>
                                </span>
                                <input 
                                    type="password" 
                                    class="form-control border-start-0 ps-0" 
                                    id="new_password_confirmation" 
                                    name="new_password_confirmation" 
                                    placeholder="Confirm your new password"
                                    required
                                >
                                <button type="button" class="btn btn-outline-secondary border-start-0" onclick="togglePassword('new_password_confirmation')">
                                    <i class="bi bi-eye" id="eye-new_password_confirmation"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-check-circle me-2"></i>Change Password
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function togglePassword(fieldId) {
        const passwordInput = document.getElementById(fieldId);
        const eyeIcon = document.getElementById('eye-' + fieldId);

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('bi-eye');
            eyeIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('bi-eye-slash');
            eyeIcon.classList.add('bi-eye');
        }
    }
</script>
@endpush
