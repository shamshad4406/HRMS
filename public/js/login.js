document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    // Form validation
    loginForm.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value)) {
            emailInput.style.borderColor = '#dc2626';
            isValid = false;
        } else {
            emailInput.style.borderColor = '#e2e8f0';
        }
        
        // Password validation
        if (passwordInput.value.length < 6) {
            passwordInput.style.borderColor = '#dc2626';
            isValid = false;
        } else {
            passwordInput.style.borderColor = '#e2e8f0';
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all fields correctly.');
        }
    });
    
    // Real-time validation feedback
    emailInput.addEventListener('input', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value && !emailRegex.test(this.value)) {
            this.style.borderColor = '#dc2626';
        } else {
            this.style.borderColor = '#e2e8f0';
        }
    });
    
    passwordInput.addEventListener('input', function() {
        if (this.value.length > 0 && this.value.length < 6) {
            this.style.borderColor = '#dc2626';
        } else {
            this.style.borderColor = '#e2e8f0';
        }
    });
    
    // Clear error styles on focus
    emailInput.addEventListener('focus', function() {
        this.style.borderColor = '#667eea';
    });
    
    passwordInput.addEventListener('focus', function() {
        this.style.borderColor = '#667eea';
    });
});
