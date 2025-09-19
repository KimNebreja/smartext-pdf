document.addEventListener('DOMContentLoaded', () => {
    const signupForm = document.getElementById('signupForm');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const strengthMeter = document.querySelector('.strength-meter-fill');
    const strengthText = document.getElementById('passwordStrength');
    const requirementElements = document.querySelectorAll('.requirement');
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');

    // Password strength requirements
    const passwordRequirements = {
        length: password => password.length >= 8,
        uppercase: password => /[A-Z]/.test(password),
        lowercase: password => /[a-z]/.test(password),
        number: password => /[0-9]/.test(password),
        special: password => /[!@#$%^&*(),.?":{}|<>]/.test(password)
    };

    // Toggle password visibility
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', () => {
            const input = button.parentElement.querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Toggle eye icon
            const icon = button.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });

    // Check password strength
    function checkPasswordStrength(password) {
        let strength = 0;
        
        // Check each requirement
        Object.entries(passwordRequirements).forEach(([requirement, check]) => {
            const requirementElement = document.querySelector(`[data-requirement="${requirement}"]`);
            const icon = requirementElement.querySelector('i');
            
            if (check(password)) {
                strength++;
                requirementElement.classList.add('valid');
                requirementElement.classList.remove('invalid');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-check');
            } else {
                requirementElement.classList.remove('valid');
                requirementElement.classList.add('invalid');
                icon.classList.remove('fa-check');
                icon.classList.add('fa-times');
            }
        });

        // Update strength meter
        strengthMeter.setAttribute('data-strength', strength);
        
        // Update strength text
        const strengthLabels = ['None', 'Weak', 'Moderate', 'Strong', 'Very Strong'];
        strengthText.textContent = `Password strength: ${strengthLabels[strength]}`;
    }

    // Show success notification
    function showSuccessNotification() {
        const notification = document.createElement('div');
        notification.className = 'success-notification';
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-check-circle"></i>
                <p>Account created successfully! Redirecting to login...</p>
            </div>
        `;
        document.body.appendChild(notification);

        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Validate form
    function validateForm() {
        const fullName = document.getElementById('fullName').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        let isValid = true;

        // Validate full name
        if (fullName.length < 2) {
            showError('fullName', 'Please enter your full name');
            isValid = false;
        } else {
            clearError('fullName');
        }

        // Validate email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showError('email', 'Please enter a valid email address');
            isValid = false;
        } else {
            clearError('email');
        }

        // Validate password
        const passwordStrength = checkPasswordStrength(password);
        if (passwordStrength < 3) {
            showError('password', 'Please choose a stronger password');
            isValid = false;
        } else {
            clearError('password');
        }

        // Validate confirm password
        if (password !== confirmPassword) {
            showError('confirmPassword', 'Passwords do not match');
            isValid = false;
        } else {
            clearError('confirmPassword');
        }

        return isValid;
    }

    // Show error message
    function showError(fieldId, message) {
        const errorElement = document.getElementById(`${fieldId}Error`);
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }

    // Clear error message
    function clearError(fieldId) {
        const errorElement = document.getElementById(`${fieldId}Error`);
        errorElement.textContent = '';
        errorElement.style.display = 'none';
    }

    // Handle password input
    passwordInput.addEventListener('input', () => {
        checkPasswordStrength(passwordInput.value);
    });

    // Handle form submission
    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        const signupButton = document.getElementById('signupButton');
        const buttonText = signupButton.querySelector('.button-text');
        const loadingSpinner = signupButton.querySelector('.loading-spinner');

        try {
            // Show loading state
            buttonText.style.display = 'none';
            loadingSpinner.style.display = 'inline-block';
            signupButton.disabled = true;

            // Get form data
            const formData = {
                fullName: document.getElementById('fullName').value.trim(),
                email: document.getElementById('email').value.trim(),
                password: passwordInput.value
            };

            // Simulate API call (replace with actual API call)
            await new Promise(resolve => setTimeout(resolve, 1000));

            // Store user data in sessionStorage
            sessionStorage.setItem('user', JSON.stringify({
                fullName: formData.fullName,
                email: formData.email
            }));

            // Show success notification
            showSuccessNotification();

            // Redirect to login page after a short delay
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);

        } catch (error) {
            console.error('Signup error:', error);
            showError('email', 'An error occurred. Please try again.');
        } finally {
            // Reset button state
            buttonText.style.display = 'inline-block';
            loadingSpinner.style.display = 'none';
            signupButton.disabled = false;
        }
    });
}); 