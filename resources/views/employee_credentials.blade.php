<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Employee Credentials - Innobles</title>
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
        <div class="auth-card auth-card-wide">
            <div class="auth-head">
                <img src="{{ asset('images/innobles_logo.png') }}" alt="Innobles" class="auth-logo-mobile">
                <p>Employee Login Credentials</p>
            </div>
            
            <div class="alert alert-success">
                <strong>Employee Created Successfully!</strong>
            </div>
            
            <div class="credentials-card">
                <h3>Employee Login Credentials</h3>
                
                <div class="credential-item">
                    <label>Employee Name:</label>
                    <div class="credential-value">{{ $employee_name }}</div>
                </div>
                
                <div class="credential-item">
                    <label>Employee Code:</label>
                    <div class="credential-value">{{ $employee_code }}</div>
                </div>
                
                <div class="credential-item">
                    <label>Email:</label>
                    <div class="credential-value">{{ $email }}</div>
                </div>
                
                <div class="credential-item">
                    <label>Password:</label>
                    <div class="credential-value password">{{ $password }}</div>
                    <button onclick="copyPassword()" class="btn-copy">Copy Password</button>
                </div>
                
                <div class="alert alert-info">
                    <strong>Next Steps:</strong>
                    <ol>
                        <li>Share these login credentials with the employee (via email, WhatsApp, etc.)</li>
                        <li>Employee can login immediately using their email and this password</li>
                        <li>After first login, employee can change their password from profile settings</li>
                    </ol>
                </div>
                
                <div class="alert alert-warning">
                    <strong>Important:</strong> Please share these credentials securely with the employee. They can change their password after first login.
                </div>
            </div>
            
            <div class="form-actions">
                <a href="{{ route('employees.index') }}" class="btn-secondary">Back to Employees List</a>
                <a href="{{ route('employees.create') }}" class="btn-primary">Add Another Employee</a>
            </div>
        </div>
        </div>
    </div>
    
    <script>
        function copyPassword() {
            const password = document.querySelector('.password').textContent;
            navigator.clipboard.writeText(password).then(() => {
                alert('Password copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy: ', err);
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = password;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Password copied to clipboard!');
            });
        }
    </script>
</body>
</html>