// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Remove active class from all buttons and panels
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanels.forEach(panel => panel.classList.remove('active'));

            // Add active class to clicked button and corresponding panel
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });

    // File upload preview for photo
    const photoUpload = document.getElementById('photo-upload');
    if (photoUpload) {
        photoUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    this.value = '';
                    return;
                }

                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    alert('Only JPG and PNG files are allowed');
                    this.value = '';
                    return;
                }

                // Show preview
                const uploadLabel = this.nextElementSibling;
                const uploadText = uploadLabel.querySelector('.upload-text');
                const uploadIcon = uploadLabel.querySelector('.upload-icon');
                
                uploadText.textContent = file.name;
                uploadIcon.textContent = '✓';
                uploadIcon.style.color = '#22c55e';
            }
        });
    }

    // Cancel button functionality
    const cancelButton = document.querySelector('.btn-cancel');
    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
                window.location.href = '{{ route("employees.index") }}';
            }
        });
    }

    // Reset button functionality
    const resetButton = document.querySelector('.btn-reset');
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            if (confirm('Are you sure you want to reset the form? All entered data will be cleared.')) {
                document.querySelector('.employee-form').reset();
                
                // Reset file upload labels
                const uploadLabels = document.querySelectorAll('.upload-label');
                uploadLabels.forEach(label => {
                    const uploadText = label.querySelector('.upload-text');
                    const uploadIcon = label.querySelector('.upload-icon');
                    if (uploadText.textContent === 'Upload Photo') {
                        uploadText.textContent = 'Upload Photo';
                    } else if (uploadText.textContent === 'Upload Resume') {
                        uploadText.textContent = 'Upload Resume';
                    } else if (uploadText.textContent === 'Upload Aadhaar') {
                        uploadText.textContent = 'Upload Aadhaar';
                    } else if (uploadText.textContent === 'Upload PAN') {
                        uploadText.textContent = 'Upload PAN';
                    }
                    uploadIcon.textContent = '📷';
                    uploadIcon.style.color = '#94a3b8';
                });
            }
        });
    }

    // Form submission
    const employeeForm = document.querySelector('.employee-form');
    if (employeeForm) {
        employeeForm.addEventListener('submit', function(e) {
            // Let Laravel handle the form submission
            // Remove e.preventDefault() to allow normal form submission
        });
    }

    // Mobile sidebar toggle
    const menuButton = document.querySelector('.icon-btn');
    const sidebar = document.querySelector('.sidebar');
    
    if (menuButton && sidebar) {
        menuButton.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !menuButton.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });

    // Input validation for Aadhaar number (12 digits)
    const aadhaarInput = document.querySelector('input[name="aadhaar_number"]');
    if (aadhaarInput) {
        aadhaarInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 12);
        });
    }

    // Input validation for PAN number (10 characters)
    const panInput = document.querySelector('input[name="pan_number"]');
    if (panInput) {
        panInput.addEventListener('input', function(e) {
            this.value = this.value.toUpperCase().slice(0, 10);
        });
    }

    // Input validation for mobile number (10 digits)
    const mobileInput = document.querySelector('input[name="mobile_number"]');
    if (mobileInput) {
        mobileInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });
    }

    // Email validation
    const emailInput = document.querySelector('input[name="email"]');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                alert('Please enter a valid email address');
                this.style.borderColor = '#ef4444';
            } else {
                this.style.borderColor = '#e2e8f0';
            }
        });
    }
});
