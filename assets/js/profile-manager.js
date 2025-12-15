/**
 * Profile Manager - Handle profile edit functionality with proper change detection
 * RetroLoved E-Commerce System
 */

// Store original values
let originalProfileData = {};
let originalAccountData = {};

let profileFormChanged = false;
let accountFormChanged = false;
let pictureChanged = false;
let pictureDeleted = false;
let pendingNavigation = null;
let isSubmitting = false;
let isPageLoaded = false;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeProfileManager();
    
    // Mark page as loaded after a delay to prevent autofill from triggering changes
    setTimeout(function() {
        isPageLoaded = true;
        console.log('Profile Manager: Page fully loaded, change detection active');
    }, 500);
});

function initializeProfileManager() {
    // Get current page from body data attribute or URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 'profile';
    
    console.log('Profile Manager initialized for page:', currentPage);
    
    // Store original data based on page
    if (currentPage === 'profile') {
        const fullNameInput = document.getElementById('fullNameInput');
        const birthDateInput = document.getElementById('birthDateInput');
        const profileImage = document.getElementById('profileImage');
        
        if (fullNameInput) {
            originalProfileData.fullName = fullNameInput.value;
            originalProfileData.birthDate = birthDateInput ? birthDateInput.value : '';
            originalProfileData.hasImage = profileImage !== null;
            originalProfileData.imageSrc = profileImage ? profileImage.src : '';
            
            console.log('Original profile data stored:', originalProfileData);
        }
        
        // Setup profile form listeners
        setupProfileFormListeners();
        
    } else if (currentPage === 'account') {
        const usernameInput = document.getElementById('usernameInput');
        const emailInput = document.getElementById('emailInput');
        
        if (usernameInput) {
            originalAccountData.username = usernameInput.value;
            originalAccountData.email = emailInput.value;
            
            console.log('Original account data stored:', originalAccountData);
        }
        
        // Setup account form listeners
        setupAccountFormListeners();
    }
    
    // Setup navigation interception
    setupNavigationInterception();
}

function setupProfileFormListeners() {
    // File input change
    const fileInput = document.getElementById('profilePictureInput');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            handleProfilePictureChange(e.target);
        });
    }
    
    // Form inputs change
    const fullNameInput = document.getElementById('fullNameInput');
    const birthDateInput = document.getElementById('birthDateInput');
    
    if (fullNameInput) {
        fullNameInput.addEventListener('input', checkProfileChanges);
    }
    if (birthDateInput) {
        birthDateInput.addEventListener('change', checkProfileChanges);
    }
    
    // Form submission
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            console.log('Profile form submitting...');
            isSubmitting = true;
            profileFormChanged = false;
        });
    }
}

function setupAccountFormListeners() {
    const usernameInput = document.getElementById('usernameInput');
    const emailInput = document.getElementById('emailInput');
    const currentPasswordInput = document.getElementById('currentPasswordInput');
    const newPasswordInput = document.getElementById('newPasswordInput');
    const confirmPasswordInput = document.getElementById('confirmPasswordInput');
    
    [usernameInput, emailInput, currentPasswordInput, newPasswordInput, confirmPasswordInput].forEach(input => {
        if (input) {
            input.addEventListener('input', checkAccountChanges);
        }
    });
    
    // Form submission
    const accountForm = document.getElementById('accountForm');
    if (accountForm) {
        accountForm.addEventListener('submit', function(e) {
            console.log('Account form submitting...');
            isSubmitting = true;
            accountFormChanged = false;
        });
    }
}

function handleProfilePictureChange(input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    console.log('File selected:', file.name, file.size, file.type);
    
    // Validate file size (2MB max)
    if (file.size > 2 * 1024 * 1024) {
        if (typeof toastError === 'function') {
            toastError('Ukuran file terlalu besar! Maksimal 2MB.');
        } else {
            alert('Ukuran file terlalu besar! Maksimal 2MB.');
        }
        input.value = '';
        return;
    }
    
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        if (typeof toastError === 'function') {
            toastError('Format file tidak didukung! Gunakan JPG, PNG, GIF, atau WEBP.');
        } else {
            alert('Format file tidak didukung! Gunakan JPG, PNG, GIF, atau WEBP.');
        }
        input.value = '';
        return;
    }
    
    // Preview image
    const reader = new FileReader();
    reader.onload = function(e) {
        const display = document.getElementById('profilePictureDisplay');
        if (display) {
            display.innerHTML = '<img id="profileImage" src="' + e.target.result + '" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">';
            console.log('Profile picture preview updated');
        }
        
        // Mark as changed
        pictureChanged = true;
        pictureDeleted = false;
        document.getElementById('deletePictureFlag').value = '0';
        
        checkProfileChanges();
    };
    reader.readAsDataURL(file);
}

function deleteProfilePicture() {
    console.log('deleteProfilePicture called');
    
    // Get initial from full name
    const fullNameInput = document.getElementById('fullNameInput');
    const initial = fullNameInput ? fullNameInput.value.charAt(0).toUpperCase() : 'U';
    
    const display = document.getElementById('profilePictureDisplay');
    if (display) {
        display.innerHTML = '<span id="profileInitial" style="font-size: 40px; font-weight: 700; color: white;">' + initial + '</span>';
    }
    
    // Clear file input
    const fileInput = document.getElementById('profilePictureInput');
    if (fileInput) {
        fileInput.value = '';
    }
    
    // Mark as deleted
    pictureDeleted = true;
    pictureChanged = true;
    document.getElementById('deletePictureFlag').value = '1';
    
    checkProfileChanges();
}

function checkProfileChanges() {
    if (!isPageLoaded) return;
    
    const fullNameInput = document.getElementById('fullNameInput');
    const birthDateInput = document.getElementById('birthDateInput');
    const fileInput = document.getElementById('profilePictureInput');
    
    if (!fullNameInput) return;
    
    const currentFullName = fullNameInput.value;
    const currentBirthDate = birthDateInput ? birthDateInput.value : '';
    const hasNewFile = fileInput && fileInput.files.length > 0;
    
    const nameChanged = currentFullName !== originalProfileData.fullName;
    const birthDateChanged = currentBirthDate !== originalProfileData.birthDate;
    const imageChanged = hasNewFile || pictureDeleted;
    
    console.log('Profile change check:', {
        nameChanged,
        birthDateChanged,
        imageChanged,
        hasNewFile,
        pictureDeleted
    });
    
    if (nameChanged || birthDateChanged || imageChanged) {
        profileFormChanged = true;
        showCancelButton('profile');
    } else {
        profileFormChanged = false;
        hideCancelButton('profile');
    }
}

function checkAccountChanges() {
    if (!isPageLoaded) return;
    
    const usernameInput = document.getElementById('usernameInput');
    const emailInput = document.getElementById('emailInput');
    const currentPasswordInput = document.getElementById('currentPasswordInput');
    const newPasswordInput = document.getElementById('newPasswordInput');
    const confirmPasswordInput = document.getElementById('confirmPasswordInput');
    
    if (!usernameInput) return;
    
    const currentUsername = usernameInput.value;
    const currentEmail = emailInput.value;
    const hasPasswordChange = (currentPasswordInput && currentPasswordInput.value) || 
                             (newPasswordInput && newPasswordInput.value) || 
                             (confirmPasswordInput && confirmPasswordInput.value);
    
    const usernameChanged = currentUsername !== originalAccountData.username;
    const emailChanged = currentEmail !== originalAccountData.email;
    
    console.log('Account change check:', {
        usernameChanged,
        emailChanged,
        hasPasswordChange
    });
    
    if (usernameChanged || emailChanged || hasPasswordChange) {
        accountFormChanged = true;
        showCancelButton('account');
    } else {
        accountFormChanged = false;
        hideCancelButton('account');
    }
}

function showCancelButton(formType) {
    const btnId = formType === 'profile' ? 'profileCancelBtn' : 'accountCancelBtn';
    const btn = document.getElementById(btnId);
    if (btn) {
        btn.style.display = 'inline-block';
        console.log('Cancel button shown for', formType);
    }
}

function hideCancelButton(formType) {
    const btnId = formType === 'profile' ? 'profileCancelBtn' : 'accountCancelBtn';
    const btn = document.getElementById(btnId);
    if (btn) {
        btn.style.display = 'none';
        console.log('Cancel button hidden for', formType);
    }
}

function cancelProfileChanges() {
    console.log('Cancelling profile changes...');
    
    // Reset form values
    const fullNameInput = document.getElementById('fullNameInput');
    const birthDateInput = document.getElementById('birthDateInput');
    const fileInput = document.getElementById('profilePictureInput');
    
    if (fullNameInput) fullNameInput.value = originalProfileData.fullName;
    if (birthDateInput) birthDateInput.value = originalProfileData.birthDate;
    if (fileInput) fileInput.value = '';
    
    // Reset picture display
    const display = document.getElementById('profilePictureDisplay');
    if (display) {
        if (originalProfileData.hasImage && originalProfileData.imageSrc) {
            display.innerHTML = '<img id="profileImage" src="' + originalProfileData.imageSrc + '" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">';
        } else {
            const initial = originalProfileData.fullName.charAt(0).toUpperCase();
            display.innerHTML = '<span id="profileInitial" style="font-size: 40px; font-weight: 700; color: white;">' + initial + '</span>';
        }
    }
    
    // Reset flags
    pictureChanged = false;
    pictureDeleted = false;
    profileFormChanged = false;
    document.getElementById('deletePictureFlag').value = '0';
    
    hideCancelButton('profile');
    
    console.log('Profile changes cancelled');
}

function cancelAccountChanges() {
    console.log('Cancelling account changes...');
    
    // Reset form values
    const usernameInput = document.getElementById('usernameInput');
    const emailInput = document.getElementById('emailInput');
    const currentPasswordInput = document.getElementById('currentPasswordInput');
    const newPasswordInput = document.getElementById('newPasswordInput');
    const confirmPasswordInput = document.getElementById('confirmPasswordInput');
    
    if (usernameInput) usernameInput.value = originalAccountData.username;
    if (emailInput) emailInput.value = originalAccountData.email;
    if (currentPasswordInput) currentPasswordInput.value = '';
    if (newPasswordInput) newPasswordInput.value = '';
    if (confirmPasswordInput) confirmPasswordInput.value = '';
    
    // Reset password requirements
    const reqLength = document.getElementById('req-length');
    const reqCase = document.getElementById('req-case');
    if (reqLength) reqLength.className = 'requirement-item';
    if (reqCase) reqCase.className = 'requirement-item';
    
    // Reset flags
    accountFormChanged = false;
    
    hideCancelButton('account');
    
    console.log('Account changes cancelled');
}

function setupNavigationInterception() {
    // Intercept all link clicks (but NOT buttons or other elements)
    document.addEventListener('click', function(e) {
        // Only intercept actual links, not buttons or other elements
        const link = e.target.closest('a[href]');
        
        // If not a link, don't intercept
        if (!link) return;
        
        // Skip if submitting
        if (isSubmitting) return;
        
        // Skip logout links
        if (link.href.includes('logout')) return;
        
        // Skip javascript: links
        if (link.href.startsWith('javascript:')) return;
        
        // Skip same-page hash links
        const currentPath = window.location.pathname;
        const linkUrl = new URL(link.href, window.location.origin);
        const linkPath = linkUrl.pathname;
        
        if (linkPath === currentPath && linkUrl.hash) return;
        
        // Check for unsaved changes
        if (profileFormChanged || accountFormChanged) {
            console.log('Unsaved changes detected, showing confirmation modal');
            e.preventDefault();
            e.stopPropagation();
            pendingNavigation = link.href;
            showConfirmationModal();
        }
    }, true);
}

function showConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function hideConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    pendingNavigation = null;
}

function stayOnPage() {
    hideConfirmationModal();
}

function discardChanges() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 'profile';
    
    if (currentPage === 'profile') {
        cancelProfileChanges();
    } else if (currentPage === 'account') {
        cancelAccountChanges();
    }
    
    hideConfirmationModal();
    
    if (pendingNavigation) {
        isSubmitting = true;
        window.location.href = pendingNavigation;
    }
}

function saveBeforeLeave() {
    const targetUrl = pendingNavigation;
    console.log('Saving before navigation to:', targetUrl);
    
    // Set flags
    isSubmitting = true;
    profileFormChanged = false;
    accountFormChanged = false;
    
    hideConfirmationModal();
    
    // Get current page and form
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 'profile';
    
    let form, submitButtonName;
    
    if (currentPage === 'profile') {
        form = document.getElementById('profileForm');
        submitButtonName = 'update_profile';
    } else if (currentPage === 'account') {
        form = document.getElementById('accountForm');
        submitButtonName = 'update_account';
    }
    
    if (!form) {
        console.error('Form not found!');
        isSubmitting = false;
        return;
    }
    
    // Check required fields
    const requiredFields = form.querySelectorAll('[required]');
    let hasEmpty = false;
    
    requiredFields.forEach(field => {
        if (field.offsetParent !== null && !field.value.trim()) {
            hasEmpty = true;
        }
    });
    
    if (hasEmpty) {
        if (typeof toastError === 'function') {
            toastError('Mohon isi semua field yang wajib diisi!');
        } else {
            alert('Mohon isi semua field yang wajib diisi!');
        }
        isSubmitting = false;
        profileFormChanged = true;
        accountFormChanged = true;
        return;
    }
    
    // Submit via AJAX
    const formData = new FormData(form);
    formData.append(submitButtonName, '1');
    
    fetch(form.action || window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(() => {
        setTimeout(() => {
            if (targetUrl) {
                window.location.replace(targetUrl);
            } else {
                window.location.reload();
            }
        }, 300);
    })
    .catch(error => {
        console.error('Form submission failed:', error);
        if (typeof toastError === 'function') {
            toastError('Gagal menyimpan data. Silakan coba lagi.');
        } else {
            alert('Gagal menyimpan data. Silakan coba lagi.');
        }
        isSubmitting = false;
        profileFormChanged = true;
        accountFormChanged = true;
    });
}

// Export functions to global scope
window.handleProfilePictureChange = handleProfilePictureChange;
window.deleteProfilePicture = deleteProfilePicture;
window.checkProfileChanges = checkProfileChanges;
window.checkAccountChanges = checkAccountChanges;
window.cancelProfileChanges = cancelProfileChanges;
window.cancelAccountChanges = cancelAccountChanges;
window.stayOnPage = stayOnPage;
window.discardChanges = discardChanges;
window.saveBeforeLeave = saveBeforeLeave;
