<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Reset Password - Innobles HRMS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f766e">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v={{ @filemtime(public_path('css/theme.css')) }}">
</head>
<body class="auth-body">
    <div class="auth-shell">
        @include('partials.auth_aside')
        <div class="auth-main">
        <div class="auth-card">
            <div class="auth-head">
                <img src="{{ asset('images/innobles_logo.png') }}" alt="Innobles" class="auth-logo-mobile">
                <h1>Reset Password</h1>
                <p>Enter your new password below</p>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    {{ session('error') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-envelope text-muted"></i>
                        </span>
                        <input 
                            type="email" 
                            class="form-control border-start-0 ps-0" 
                            id="email" 
                            name="email" 
                            placeholder="Enter your email"
                            required
                            value="{{ old('email') ?? request('email') }}"
                        >
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">New Password</label>
                    <div class="password-input-container">
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            placeholder="Enter new password"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="bi bi-eye" id="eye-icon"></i>
                            <i class="bi bi-eye-slash d-none" id="eye-off-icon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <div class="password-input-container">
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            placeholder="Confirm new password"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePasswordConfirm()">
                            <i class="bi bi-eye" id="eye-icon-confirm"></i>
                            <i class="bi bi-eye-slash d-none" id="eye-off-icon-confirm"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="bi bi-check-circle me-2"></i>
                    Reset Password
                </button>
            </form>

            <div class="login-footer">
                <p>Remember your password? <a href="{{ route('login') }}">Sign In</a></p>
            </div>
        </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeOffIcon = document.getElementById('eye-off-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('d-none');
                eyeOffIcon.classList.remove('d-none');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('d-none');
                eyeIcon.classList.add('d-none');
            }
        }

        function togglePasswordConfirm() {
            const passwordInput = document.getElementById('password_confirmation');
            const eyeIcon = document.getElementById('eye-icon-confirm');
            const eyeOffIcon = document.getElementById('eye-off-icon-confirm');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('d-none');
                eyeOffIcon.classList.remove('d-none');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('d-none');
                eyeIcon.classList.add('d-none');
            }
        }
    </script>
</body>
</html>
