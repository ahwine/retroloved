// ===== CONTACT SUPPORT MODAL =====

// Show Contact Support Modal
function showContactSupportModal() {
    const modal = document.getElementById('contactSupportModal');
    if (modal) {
        modal.classList.add('active');
        
        // Prevent background scrolling - FIXED
        const scrollbarWidth = getScrollbarWidth();
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.top = `-${window.scrollY}px`;
        document.body.style.width = '100%';
        if (scrollbarWidth > 0) {
            document.body.style.paddingRight = scrollbarWidth + 'px';
        }
        
        // Reset form
        const contactForm = document.getElementById('contactSupportForm');
        if (contactForm) {
            contactForm.reset();
        }
        
        // Pre-fill name and email if user is logged in
        const userName = document.querySelector('[data-user-name]');
        const userEmail = document.querySelector('[data-user-email]');
        
        if (userName && userName.dataset.userName) {
            const supportNameInput = document.getElementById('supportName');
            if (supportNameInput) {
                supportNameInput.value = userName.dataset.userName;
            }
        }
        
        if (userEmail && userEmail.dataset.userEmail) {
            const supportEmailInput = document.getElementById('supportEmail');
            if (supportEmailInput) {
                supportEmailInput.value = userEmail.dataset.userEmail;
            }
        }
    }
}

// Close Contact Support Modal
function closeContactSupportModal() {
    const modal = document.getElementById('contactSupportModal');
    if (modal) {
        modal.classList.remove('active');
        
        // Restore background scrolling - FIXED
        const scrollY = document.body.style.top;
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.width = '';
        document.body.style.paddingRight = '';
        
        // Restore scroll position
        if (scrollY) {
            window.scrollTo(0, parseInt(scrollY || '0') * -1);
        }
    }
}

// Get scrollbar width to prevent layout shift
function getScrollbarWidth() {
    const outer = document.createElement('div');
    outer.style.visibility = 'hidden';
    outer.style.overflow = 'scroll';
    document.body.appendChild(outer);
    
    const inner = document.createElement('div');
    outer.appendChild(inner);
    
    const scrollbarWidth = outer.offsetWidth - inner.offsetWidth;
    outer.parentNode.removeChild(outer);
    
    return scrollbarWidth;
}

// Handle Contact Support Form Submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactSupportForm');
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span style="display: inline-flex; align-items: center; gap: 8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg> Mengirim...</span>';
            
            // Get form data
            const formData = new FormData(form);
            
            try {
                // Determine the correct path based on current location
                let formAction = 'process-contact-support.php';
                if (window.location.pathname.includes('/customer/') || 
                    window.location.pathname.includes('/admin/') || 
                    window.location.pathname.includes('/auth/')) {
                    formAction = '../process-contact-support.php';
                }
                
                const response = await fetch(formAction, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                console.log('Contact Support Response:', result);
                
                if (result.success) {
                    // Show success toast
                    if (typeof showToast === 'function') {
                        showToast(result.message, 'success');
                    } else if (typeof window.Toast !== 'undefined') {
                        window.Toast.success(result.message);
                    } else {
                        alert(result.message); // Fallback
                    }
                    
                    // Close modal after short delay
                    setTimeout(() => {
                        closeContactSupportModal();
                    }, 2000);
                } else {
                    // Show error toast
                    if (typeof showToast === 'function') {
                        showToast(result.message, 'error');
                    } else if (typeof window.Toast !== 'undefined') {
                        window.Toast.error(result.message);
                    } else {
                        alert(result.message); // Fallback
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (typeof showToast === 'function') {
                    showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
                } else if (typeof window.Toast !== 'undefined') {
                    window.Toast.error('Terjadi kesalahan. Silakan coba lagi.');
                } else {
                    alert('Terjadi kesalahan. Silakan coba lagi.'); // Fallback
                }
            } finally {
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    }
    
    // Close modal when clicking outside
    const modal = document.getElementById('contactSupportModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeContactSupportModal();
            }
        });
    }
});

// Add spinning animation for loading icon
if (!document.getElementById('contact-support-styles')) {
    const contactSupportStyle = document.createElement('style');
    contactSupportStyle.id = 'contact-support-styles';
    contactSupportStyle.textContent = `
        .spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
    `;
    document.head.appendChild(contactSupportStyle);
}