// Placeholder for future authentication-related client-side scripts if needed.
// All primary authentication logic (login/logout/session check) is handled server-side in PHP.

// Example of a client-side script that *might* be needed in the future:
// A function to dynamically update UI elements based on login status,
// but not for enforcing page access.

/*
function updateLoginStatusUI(isLoggedIn) {
    const loginLink = document.getElementById('loginLink');
    const logoutLink = document.getElementById('logoutLink');
    if (isLoggedIn) {
        if (loginLink) loginLink.style.display = 'none';
        if (logoutLink) logoutLink.style.display = 'block';
    } else {
        if (loginLink) loginLink.style.display = 'block';
        if (logoutLink) logoutLink.style.display = 'none';
    }
}
*/

// Note: The actual login/logout functions called by buttons should make API calls
// to the PHP backend, which handles the session management on the server.

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('errorMessage');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const showSignupLink = document.getElementById('showSignup');

    if (loginForm) {
        // Real-time validation
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        emailInput.addEventListener('input', () => validateEmail(emailInput));
        passwordInput.addEventListener('input', () => validatePassword(passwordInput));

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Reset previous errors
            resetErrors();

            // Validate form
            const isEmailValid = validateEmail(emailInput);
            const isPasswordValid = validatePassword(passwordInput);

            if (!isEmailValid || !isPasswordValid) {
                return;
            }

            // Show loading state
            setLoading(true);

            try {
                // This simulateLogin is a mock and needs to be replaced with an actual fetch call to your PHP login handler
                // The PHP login handler will set the session and redirect on success.
                const response = await simulateLogin(emailInput.value, passwordInput.value);
                
                if (response.success) {
                    // If simulateLogin indicates success, we would typically *not* redirect client-side here.
                    // Instead, the PHP login handler would have already set the session and issued a redirect header.
                    // This client-side simulateLogin is for frontend validation/UI feedback before a real submit/redirect.
                    
                    // *** For now, keep the client-side redirect to match previous mock behavior, but this should change ***
                    // *** when integrating with the real PHP login handler. ***
                    window.location.href = 'pages/dashboard.php'; // Redirect to the correct dashboard path

                } else {
                    showError(response.message || 'Invalid credentials. Please try again.');
                }
            } catch (error) {
                console.error('Login error:', error);
                showError('An error occurred during login. Please try again.');
            } finally {
                setLoading(false);
            }
        });
    }

    if (showSignupLink) {
        showSignupLink.addEventListener('click', (e) => {
            e.preventDefault();
            // For now, just show a message
            alert('Sign up functionality will be implemented in the next phase.');
        });
    }

    // Mobile Navigation
    const mobileNavToggle = document.createElement('button');
    mobileNavToggle.className = 'mobile-nav-toggle';
    mobileNavToggle.setAttribute('aria-label', 'Toggle navigation menu');
    mobileNavToggle.innerHTML = '☰';
    
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (sidebar && mainContent) {
        // Check if mobileNavToggle already exists to avoid adding duplicates
        if (!mainContent.querySelector('.mobile-nav-toggle')) {
             mainContent.insertBefore(mobileNavToggle, mainContent.firstChild);
        }
        
        mobileNavToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            mobileNavToggle.setAttribute('aria-expanded', 
                sidebar.classList.contains('active'));
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            // Add a check to ensure the clicked element is not within the sidebar or the toggle button itself
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !mobileNavToggle.contains(e.target) && 
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                mobileNavToggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                mobileNavToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
});

function validateEmail(input) {
    const email = input.value.trim();
    // Updated regex for slightly broader email validation
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    
    if (!email) {
        showFieldError(input, 'Email is required');
        return false;
    }
    
    if (!emailRegex.test(email)) {
        showFieldError(input, 'Please enter a valid email address');
        return false;
    }
    
    clearFieldError(input);
    return true;
}

function validatePassword(input) {
    const password = input.value.trim();
    
    if (!password) {
        showFieldError(input, 'Password is required');
        return false;
    }
    
    if (password.length < 6) {
        showFieldError(input, 'Password must be at least 6 characters long');
        return false;
    }
    
    clearFieldError(input);
    return true;
}

function showFieldError(input, message) {
    // Use closest to find the parent form-group and then query for the error element within it
    const formGroup = input.closest('.form-group');
    const errorElement = formGroup ? formGroup.querySelector('.error-text') : null;
    
    if (errorElement) {
        errorElement.textContent = message;
    } else {
        console.warn(`Error element not found for input: ${input.id}`);
    }
    input.classList.add('error');
}

function clearFieldError(input) {
    // Use closest to find the parent form-group and then query for the error element within it
    const formGroup = input.closest('.form-group');
    const errorElement = formGroup ? formGroup.querySelector('.error-text') : null;

    if (errorElement) {
        errorElement.textContent = '';
    } else {
         console.warn(`Error element not found for input: ${input.id}`);
    }
    input.classList.remove('error');
}

function resetErrors() {
    const errorMessages = document.querySelectorAll('.error-text');
    errorMessages.forEach(element => element.textContent = '');
    
    const inputs = document.querySelectorAll('input.error');
    inputs.forEach(input => input.classList.remove('error'));
    
    const mainErrorMessage = document.getElementById('errorMessage');
    if (mainErrorMessage) {
        mainErrorMessage.style.display = 'none';
        mainErrorMessage.textContent = '';
    }
}

function showError(message) {
    const mainErrorMessage = document.getElementById('errorMessage');
    if (mainErrorMessage) {
        mainErrorMessage.textContent = message;
        mainErrorMessage.style.display = 'block';
    }
}

function setLoading(isLoading) {
    const button = document.querySelector('.btn-primary');
    if (button) {
        // Store original button text if not already stored
        if (!button.dataset.originalText) {
            button.dataset.originalText = button.innerHTML;
        }

        if (isLoading) {
            button.classList.add('loading');
            button.disabled = true;
            button.innerHTML = '<span class="loading-spinner"></span> Loading...'; // Add spinner and text
        } else {
            button.classList.remove('loading');
            button.disabled = false;
            button.innerHTML = button.dataset.originalText; // Restore original text
        }
    }
}

// Simulated login function (replace with actual API call to your PHP login handler)
// This mock function is only for demonstrating client-side behavior before
// the real API call is integrated.
async function simulateLogin(email, password) {
    console.warn("Using simulateLogin - replace with actual API call to PHP login handler.");
    // Simulate API delay
    await new Promise(resolve => setTimeout(resolve, 500)); // Reduced delay

    // *** IMPORTANT ***
    // In a real application, this function would send the email and password
    // to a PHP endpoint (e.g., api/login.php) using `fetch()`. The PHP endpoint
    // would perform the actual authentication against the database,
    // manage the server-side session, and return a success/failure JSON response.
    // If successful, the PHP would typically also issue a redirect header.
    // This client-side JS would then just handle UI feedback (loading, errors).

    // For demo purposes, still simulate a basic check:
    if (email && password.length >= 6) {
        // In a real scenario, the PHP backend would return success: true
        // *only* after successful database authentication and session setup.
        return { success: true }; // Simulate success
    }

    return { success: false, message: 'Invalid credentials (simulated)' };
}

// Add logout function
function logout() {
    fetch('../api/logout.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to login page
                window.location.href = '../index.php';
            } else {
                console.error('Logout failed:', data.message || 'Unknown error');
                alert('Failed to logout. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error during logout:', error);
            alert('An error occurred during logout. Please try again.');
        });
}

// Removed: isLoggedIn function (session managed by PHP)
// Removed: logout function (handle logout via PHP endpoint) 