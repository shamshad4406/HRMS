<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Setup Your Password - Innobles</title>
        <meta name="theme-color" content="#0f766e">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v={{ @filemtime(public_path('css/theme.css')) }}">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.querySelector('.success-message');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.transition = 'opacity 0.5s ease';
                    successMessage.style.opacity = '0';
                    setTimeout(function() {
                        successMessage.style.display = 'none';
                    }, 500);
                }, 2000);
            }
        });
    </script>
</head>
<body class="auth-body">
    <div class="auth-shell">
        @include('partials.auth_aside')
        <div class="auth-main">
        <div class="auth-card">
            <div class="auth-head">
                <img src="{{ asset('images/innobles_logo.png') }}" alt="Innobles" class="auth-logo-mobile">
                <p>Setup Your Account Password</p>
            </div>
            
            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif
            
            @if($invalidToken)
                <div class="alert alert-error">
                    <strong>Invalid or Expired Link</strong>
                    <p>This activation link is invalid or has expired. Please contact your administrator for a new activation link.</p>
                </div>
                <div class="form-actions">
                    <a href="{{ route('login') }}" class="btn-primary">Go to Login</a>
                </div>
            @else
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                
                <div class="welcome-message">
                    <h3>Welcome, {{ $employee_name }}!</h3>
                    <p>Please set your password to activate your account.</p>
                </div>
                
                <form method="POST" action="{{ route('employee.activate', ['token' => $token]) }}">
                    @csrf
                    <div class="form-group">
                        <label for="password">Create Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Create your password"
                            required
                        >
                        <small class="help-text">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            placeholder="Confirm your password"
                            required
                        >
                    </div>
                    
                    <button type="submit" class="btn-login">Activate Account</button>
                </form>
            @endif
        </div>
        </div>
    </div>
</body>
</html>