document.addEventListener('DOMContentLoaded', function() {
    // Password strength validation
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', validatePasswordStrength);
    }
    
    if (passwordConfirmationInput) {
        passwordConfirmationInput.addEventListener('input', validatePasswordMatch);
    }

    // Form validation
    const authForm = document.querySelector('.auth-form');
    if (authForm) {
        authForm.addEventListener('submit', handleFormSubmit);
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // Demo account quick fill
    setupDemoAccountQuickFill();
});

function validatePasswordStrength() {
    const password = document.getElementById('password').value;
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password)
    };
    
    const requirementsList = document.querySelectorAll('.password-requirements li');
    requirementsList.forEach((item, index) => {
        const requirement = Object.values(requirements)[index];
        if (requirement) {
            item.style.color = '#38a169';
            item.style.fontWeight = 'bold';
        } else {
            item.style.color = '#718096';
            item.style.fontWeight = 'normal';
        }
    });
    
    return Object.values(requirements).every(req => req);
}

function validatePasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    const confirmationInput = document.getElementById('password_confirmation');
    
    if (confirmation && password !== confirmation) {
        confirmationInput.style.borderColor = '#e53e3e';
        showFieldError(confirmationInput, 'Les mots de passe ne correspondent pas');
        return false;
    } else if (confirmation) {
        confirmationInput.style.borderColor = '#38a169';
        hideFieldError(confirmationInput);
        return true;
    }
    
    return true;
}

function handleFormSubmit(e) {
    let isValid = true;
    const form = e.target;
    
    // Clear previous errors
    const errorMessages = form.querySelectorAll('.field-error');
    errorMessages.forEach(msg => msg.remove());
    
    // Validate required fields
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'Ce champ est requis');
            isValid = false;
        }
    });
    
    // Email validation
    const emailField = form.querySelector('input[type="email"]');
    if (emailField && emailField.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailField.value)) {
            showFieldError(emailField, 'Veuillez entrer une adresse email valide');
            isValid = false;
        }
    }
    
    // Password validation for registration
    const passwordField = form.querySelector('#password');
    const confirmationField = form.querySelector('#password_confirmation');
    
    if (passwordField && form.action.includes('register')) {
        if (!validatePasswordStrength()) {
            showFieldError(passwordField, 'Le mot de passe ne respecte pas tous les critères');
            isValid = false;
        }
        
        if (confirmationField && !validatePasswordMatch()) {
            isValid = false;
        }
    }
    
    // Terms acceptance for registration
    const termsCheckbox = form.querySelector('input[name="terms"]');
    if (termsCheckbox && !termsCheckbox.checked) {
        showFieldError(termsCheckbox.parentElement, 'Vous devez accepter les conditions d\'utilisation');
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
        
        // Scroll to first error
        const firstError = form.querySelector('.field-error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    } else {
        // Show loading state
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Connexion...';
            submitButton.disabled = true;
            
            // Reset button if form submission fails
            setTimeout(() => {
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            }, 10000);
        }
    }
}

function showFieldError(field, message) {
    // Remove existing error for this field
    hideFieldError(field);
    
    // Create error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.color = '#e53e3e';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.textContent = message;
    
    // Add error styling to field
    field.style.borderColor = '#e53e3e';
    
    // Insert error message after field
    field.parentNode.insertBefore(errorDiv, field.nextSibling);
}

function hideFieldError(field) {
    // Remove error message
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Reset field styling
    field.style.borderColor = '#e2e8f0';
}

function setupDemoAccountQuickFill() {
    const demoAccounts = document.querySelectorAll('.demo-account');
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password');
    
    if (!emailField || !passwordField) return;
    
    demoAccounts.forEach(account => {
        account.style.cursor = 'pointer';
        account.title = 'Cliquer pour remplir automatiquement';
        
        account.addEventListener('click', function() {
            const text = this.textContent;
            
            if (text.includes('admin@calanques.fr')) {
                emailField.value = 'admin@calanques.fr';
                passwordField.value = 'admin123';
            } else if (text.includes('user@calanques.fr')) {
                emailField.value = 'user@calanques.fr';
                passwordField.value = 'user123';
            }
            
            // Visual feedback
            this.style.backgroundColor = 'rgba(44, 82, 130, 0.2)';
            setTimeout(() => {
                this.style.backgroundColor = 'rgba(44, 82, 130, 0.1)';
            }, 200);
        });
    });
}