document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.querySelector('form');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    
    // Form validation
    registerForm.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Name validation
        if (nameInput.value.trim().length < 2) {
            nameInput.style.borderColor = '#dc2626';
            isValid = false;
        } else {
            nameInput.style.borderColor = '#e2e8f0';
        }
        
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
        
        // Password confirmation validation
        if (passwordConfirmInput.value !== passwordInput.value) {
            passwordConfirmInput.style.borderColor = '#dc2626';
            isValid = false;
        } else {
            passwordConfirmInput.style.borderColor = '#e2e8f0';
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all fields correctly.');
        }
    });
    
    // Real-time validation feedback
    nameInput.addEventListener('input', function() {
        if (this.value.trim().length > 0 && this.value.trim().length < 2) {
            this.style.borderColor = '#dc2626';
        } else {
            this.style.borderColor = '#e2e8f0';
        }
    });
    
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
        
        // Check confirmation if already filled
        if (passwordConfirmInput.value) {
            if (passwordConfirmInput.value !== this.value) {
                passwordConfirmInput.style.borderColor = '#dc2626';
            } else {
                passwordConfirmInput.style.borderColor = '#e2e8f0';
            }
        }
    });
    
    passwordConfirmInput.addEventListener('input', function() {
        if (this.value !== passwordInput.value) {
            this.style.borderColor = '#dc2626';
        } else {
            this.style.borderColor = '#e2e8f0';
        }
    });
    
    // Clear error styles on focus
    nameInput.addEventListener('focus', function() {
        this.style.borderColor = '#667eea';
    });
    
    emailInput.addEventListener('focus', function() {
        this.style.borderColor = '#667eea';
    });
    
    passwordInput.addEventListener('focus', function() {
        this.style.borderColor = '#667eea';
    });
    
    passwordConfirmInput.addEventListener('focus', function() {
        this.style.borderColor = '#667eea';
    });
});
