// Form Validation & Error Handling
// Step 8B: Specific Error Messages

/**
 * Show error message for a field
 * @param {HTMLElement} field - Input field element
 * @param {string} message - Error message to display
 */
function showFieldError(field, message) {
    if (!field) return;
    
    // Add error class to field
    field.classList.add('error');
    field.classList.remove('success');
    
    // Find or create error message element
    let errorMsg = field.parentElement.querySelector('.error-message');
    if (!errorMsg) {
        errorMsg = document.createElement('div');
        errorMsg.className = 'error-message';
        field.parentElement.appendChild(errorMsg);
    }
    
    // Set error message with icon
    errorMsg.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span>${message}</span>
    `;
    errorMsg.classList.add('show');
    
    // Add icon to input
    addInputIcon(field, 'error');
}

/**
 * Show success state for a field
 * @param {HTMLElement} field - Input field element
 */
function showFieldSuccess(field) {
    if (!field) return;
    
    // Add success class to field
    field.classList.add('success');
    field.classList.remove('error');
    
    // Hide error message
    const errorMsg = field.parentElement.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.classList.remove('show');
    }
    
    // Add success icon
    addInputIcon(field, 'success');
}

/**
 * Clear field validation state
 * @param {HTMLElement} field - Input field element
 */
function clearFieldValidation(field) {
    if (!field) return;
    
    field.classList.remove('error', 'success');
    
    const errorMsg = field.parentElement.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.classList.remove('show');
    }
    
    const icon = field.parentElement.querySelector('.input-icon');
    if (icon) {
        icon.remove();
    }
}

/**
 * Add validation icon to input
 * @param {HTMLElement} field - Input field element
 * @param {string} type - Icon type ('error' or 'success')
 */
function addInputIcon(field, type) {
    // Remove existing icon
    const existingIcon = field.parentElement.querySelector('.input-icon');
    if (existingIcon) {
        existingIcon.remove();
    }
    
    // Don't add icon if field is in a password input with toggle
    if (field.type === 'password' || field.parentElement.querySelector('.password-toggle')) {
        return;
    }
    
    const icon = document.createElement('div');
    icon.className = 'input-icon ' + type;
    
    if (type === 'error') {
        icon.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        `;
    } else {
        icon.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        `;
    }
    
    // Make parent relative if not already
    if (getComputedStyle(field.parentElement).position === 'static') {
        field.parentElement.style.position = 'relative';
    }
    
    field.parentElement.appendChild(icon);
}

/**
 * Validate email format
 * @param {string} email - Email address to validate
 * @returns {boolean}
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validate phone number (Indonesian format)
 * @param {string} phone - Phone number to validate
 * @returns {boolean}
 */
function validatePhone(phone) {
    const re = /^(\+62|62|0)[0-9]{9,12}$/;
    return re.test(phone.replace(/[\s-]/g, ''));
}

/**
 * Check password strength
 * @param {string} password - Password to check
 * @returns {object} - { strength: 'weak'|'medium'|'strong', score: 0-100 }
 */
function checkPasswordStrength(password) {
    let score = 0;
    
    if (!password) return { strength: 'weak', score: 0 };
    
    // Length
    if (password.length >= 8) score += 25;
    if (password.length >= 12) score += 15;
    
    // Contains lowercase
    if (/[a-z]/.test(password)) score += 15;
    
    // Contains uppercase
    if (/[A-Z]/.test(password)) score += 15;
    
    // Contains numbers
    if (/\d/.test(password)) score += 15;
    
    // Contains special characters
    if (/[^a-zA-Z0-9]/.test(password)) score += 15;
    
    let strength = 'weak';
    if (score >= 70) strength = 'strong';
    else if (score >= 45) strength = 'medium';
    
    return { strength, score };
}

/**
 * Show password strength indicator
 * @param {HTMLElement} passwordField - Password input field
 */
function showPasswordStrength(passwordField) {
    if (!passwordField) return;
    
    const password = passwordField.value;
    const result = checkPasswordStrength(password);
    
    let strengthIndicator = passwordField.parentElement.querySelector('.password-strength');
    if (!strengthIndicator) {
        strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength';
        strengthIndicator.innerHTML = `
            <div class="password-strength-bar">
                <div class="password-strength-fill"></div>
            </div>
            <div class="password-strength-text"></div>
        `;
        passwordField.parentElement.appendChild(strengthIndicator);
    }
    
    if (password.length > 0) {
        strengthIndicator.classList.add('show');
        
        const fill = strengthIndicator.querySelector('.password-strength-fill');
        const text = strengthIndicator.querySelector('.password-strength-text');
        
        // Remove all strength classes
        fill.classList.remove('weak', 'medium', 'strong');
        text.classList.remove('weak', 'medium', 'strong');
        
        // Add current strength class
        fill.classList.add(result.strength);
        text.classList.add(result.strength);
        
        // Set text
        const strengthText = {
            weak: 'Lemah - Gunakan kombinasi huruf, angka, dan simbol',
            medium: 'Sedang - Tambahkan karakter lagi untuk lebih aman',
            strong: 'Kuat - Password Anda aman!'
        };
        text.textContent = strengthText[result.strength];
    } else {
        strengthIndicator.classList.remove('show');
    }
}

/**
 * Show password requirements checklist
 * @param {HTMLElement} passwordField - Password input field
 */
function showPasswordRequirements(passwordField) {
    if (!passwordField) return;
    
    let requirements = passwordField.parentElement.querySelector('.password-requirements');
    if (!requirements) {
        requirements = document.createElement('div');
        requirements.className = 'password-requirements';
        requirements.innerHTML = `
            <ul>
                <li data-req="length">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                    </svg>
                    Minimal 8 karakter
                </li>
                <li data-req="uppercase">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                    </svg>
                    Minimal 1 huruf besar (A-Z)
                </li>
                <li data-req="lowercase">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                    </svg>
                    Minimal 1 huruf kecil (a-z)
                </li>
                <li data-req="number">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                    </svg>
                    Minimal 1 angka (0-9)
                </li>
            </ul>
        `;
        passwordField.parentElement.appendChild(requirements);
    }
    
    const password = passwordField.value;
    
    // Check each requirement
    const lengthReq = requirements.querySelector('[data-req="length"]');
    const uppercaseReq = requirements.querySelector('[data-req="uppercase"]');
    const lowercaseReq = requirements.querySelector('[data-req="lowercase"]');
    const numberReq = requirements.querySelector('[data-req="number"]');
    
    if (password.length >= 8) {
        lengthReq.classList.add('valid');
        lengthReq.querySelector('svg').innerHTML = '<polyline points="20 6 9 17 4 12"></polyline>';
    } else {
        lengthReq.classList.remove('valid');
        lengthReq.querySelector('svg').innerHTML = '<circle cx="12" cy="12" r="10"></circle>';
    }
    
    if (/[A-Z]/.test(password)) {
        uppercaseReq.classList.add('valid');
        uppercaseReq.querySelector('svg').innerHTML = '<polyline points="20 6 9 17 4 12"></polyline>';
    } else {
        uppercaseReq.classList.remove('valid');
        uppercaseReq.querySelector('svg').innerHTML = '<circle cx="12" cy="12" r="10"></circle>';
    }
    
    if (/[a-z]/.test(password)) {
        lowercaseReq.classList.add('valid');
        lowercaseReq.querySelector('svg').innerHTML = '<polyline points="20 6 9 17 4 12"></polyline>';
    } else {
        lowercaseReq.classList.remove('valid');
        lowercaseReq.querySelector('svg').innerHTML = '<circle cx="12" cy="12" r="10"></circle>';
    }
    
    if (/\d/.test(password)) {
        numberReq.classList.add('valid');
        numberReq.querySelector('svg').innerHTML = '<polyline points="20 6 9 17 4 12"></polyline>';
    } else {
        numberReq.classList.remove('valid');
        numberReq.querySelector('svg').innerHTML = '<circle cx="12" cy="12" r="10"></circle>';
    }
}

/**
 * Validate form field
 * @param {HTMLElement} field - Input field to validate
 * @returns {boolean}
 */
function validateField(field) {
    if (!field) return false;
    
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    const name = field.name;
    
    // Clear previous validation
    clearFieldValidation(field);
    
    // Check if required and empty
    if (required && !value) {
        showFieldError(field, 'Field ini wajib diisi');
        return false;
    }
    
    // If empty and not required, it's valid
    if (!value && !required) {
        return true;
    }
    
    // Email validation
    if (type === 'email' || name.includes('email')) {
        if (!validateEmail(value)) {
            showFieldError(field, 'Format email tidak valid. Contoh: nama@example.com');
            return false;
        }
    }
    
    // Phone validation
    if (name.includes('phone') || name.includes('telp')) {
        if (!validatePhone(value)) {
            showFieldError(field, 'Format nomor telepon tidak valid. Contoh: 081234567890');
            return false;
        }
    }
    
    // Password validation
    if (type === 'password' && name.includes('new') || name === 'password') {
        if (value.length < 8) {
            showFieldError(field, 'Password minimal 8 karakter');
            return false;
        }
    }
    
    // Confirm password validation
    if (name.includes('confirm')) {
        const passwordField = document.querySelector('input[name="new_password"], input[name="password"]');
        if (passwordField && value !== passwordField.value) {
            showFieldError(field, 'Password tidak cocok');
            return false;
        }
    }
    
    // If all validations pass
    showFieldSuccess(field);
    return true;
}

/**
 * Show alert message
 * @param {string} message - Alert message
 * @param {string} type - Alert type ('error', 'success', 'warning', 'info')
 * @param {number} duration - Auto-hide duration in ms (0 = no auto-hide)
 */
function showAlert(message, type = 'info', duration = 5000) {
    const alertBox = document.createElement('div');
    alertBox.className = 'alert alert-' + type;
    
    const icons = {
        error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
    };
    
    alertBox.innerHTML = icons[type] + '<span>' + message + '</span><button class="alert-close" onclick="this.parentElement.remove()">×</button>';
    
    // Find a good place to insert (after header or at top of main content)
    const container = document.querySelector('main, .main-content, .container');
    if (container) {
        container.insertBefore(alertBox, container.firstChild);
    } else {
        document.body.insertBefore(alertBox, document.body.firstChild);
    }
    
    // Auto-hide
    if (duration > 0) {
        setTimeout(() => {
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 300);
        }, duration);
    }
}

// Export functions
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showFieldError,
        showFieldSuccess,
        clearFieldValidation,
        validateEmail,
        validatePhone,
        checkPasswordStrength,
        showPasswordStrength,
        showPasswordRequirements,
        validateField,
        showAlert
    };
}
