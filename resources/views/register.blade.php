<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Sign Up - Innobles</title>
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
                <p>Create your account</p>
            </div>
            
            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif
            
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        placeholder="Enter your full name"
                        required
                        value="{{ old('name') }}"
                        class="form-control"
                    >
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email"
                        required
                        value="{{ old('email') }}"
                        class="form-control"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Create a password"
                        required
                        class="form-control"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Confirm your password"
                        required
                        class="form-control"
                    >
                </div>

                <div class="form-group">
                    <label for="personal_information_id">Employee ID (Optional)</label>
                    <input
                        type="number"
                        id="personal_information_id"
                        name="personal_information_id"
                        placeholder="Enter your employee ID"
                        value="{{ old('personal_information_id') }}"
                    >
                    <small class="help-text">Enter your employee ID if you have one (optional)</small>
                </div>

                <button type="submit" class="btn-login">Sign Up</button>
            </form>
            
            <div class="login-footer">
                <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
            </div>
        </div>
        </div>
    </div>

    <script src="{{ asset('js/register.js') }}"></script>
</body>
</html>
