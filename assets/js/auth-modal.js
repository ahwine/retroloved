// ===== AUTHENTICATION MODAL SYSTEM =====

// Show Login Modal
function showLoginModal() {
    const overlay = createAuthOverlay();
    const modal = createLoginModal();
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Show immediately without animation
    overlay.style.opacity = '1';
    modal.style.opacity = '1';
    modal.style.transform = 'scale(1)';
    
    // Setup close handlers
    setupCloseHandlers(overlay, modal);
}

// Show Register Modal
function showRegisterModal() {
    const overlay = createAuthOverlay();
    const modal = createRegisterModal();
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Show immediately without animation
    overlay.style.opacity = '1';
    modal.style.opacity = '1';
    modal.style.transform = 'scale(1)';
    
    // Setup close handlers
    setupCloseHandlers(overlay, modal);
}

// Create Overlay
function createAuthOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'auth-modal-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.65);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 1;
    `;
    return overlay;
}

// Create Login Modal
function createLoginModal() {
    const modal = document.createElement('div');
    modal.className = 'auth-modal';
    modal.style.cssText = `
        background: white;
        border-radius: 20px;
        padding: 40px;
        max-width: 450px;
        width: 90%;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        transform: scale(1);
        opacity: 1;
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
    `;
    
    modal.innerHTML = `
        <button class="auth-modal-close" onclick="closeAuthModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; cursor: pointer; padding: 8px; color: #6B7280; transition: color 0.2s;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <div style="text-align: center; margin-bottom: 32px;">
            <h2 style="font-size: 28px; font-weight: 800; color: #1F2937; margin-bottom: 8px; font-family: 'Playfair Display', serif;">Selamat Datang Kembali!</h2>
            <p style="color: #6B7280; font-size: 15px;">Masuk untuk melanjutkan perjalanan vintage Anda</p>
        </div>
        
        <form id="loginForm" onsubmit="handleLogin(event)" style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600; font-size: 14px;">Username atau Email</label>
                <input type="text" name="username" required style="width: 100%; padding: 12px 16px; border: 1.5px solid #E5E7EB; border-radius: 10px; font-size: 15px; transition: all 0.2s; font-family: inherit;" placeholder="Enter your username or email">
            </div>
            
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label style="color: #374151; font-weight: 600; font-size: 14px;">Password</label>
                    <a href="javascript:void(0)" onclick="switchToForgotPassword()" style="color: #D97706; font-size: 13px; font-weight: 600; text-decoration: none;">Lupa Password?</a>
                </div>
                <div style="position: relative;">
                    <input type="password" id="loginPassword" name="password" required style="width: 100%; padding: 12px 40px 12px 16px; border: 1.5px solid #E5E7EB; border-radius: 10px; font-size: 15px; transition: all 0.2s; font-family: inherit;" placeholder="Masukkan password Anda">
                    <button type="button" onclick="togglePasswordVisibility('loginPassword')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9CA3AF; cursor: pointer; padding: 0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
            </div>
            
            <button type="submit" style="width: 100%; padding: 14px; background: #D97706; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s; margin-top: 8px;">
                Masuk
            </button>
        </form>
        
        <div style="margin-top: 24px; text-align: center;">
            <p style="color: #6B7280; font-size: 14px;">
                Belum punya akun? 
                <a href="javascript:void(0)" onclick="switchToRegister()" style="color: #D97706; font-weight: 600; text-decoration: none;">Daftar di sini</a>
            </p>
        </div>
    `;
    
    return modal;
}

// Create Register Modal
function createRegisterModal() {
    const modal = document.createElement('div');
    modal.className = 'auth-modal';
    modal.style.cssText = `
        background: white;
        border-radius: 20px;
        padding: 40px;
        max-width: 450px;
        width: 90%;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        transform: scale(1);
        opacity: 1;
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
    `;
    
    modal.innerHTML = `
        <button class="auth-modal-close" onclick="closeAuthModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; cursor: pointer; padding: 8px; color: #6B7280; transition: color 0.2s;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <div style="text-align: center; margin-bottom: 32px;">
            <h2 style="font-size: 28px; font-weight: 800; color: #1F2937; margin-bottom: 8px; font-family: 'Playfair Display', serif;">Buat Akun</h2>
            <p style="color: #6B7280; font-size: 15px;">Bergabunglah dengan kami dan mulai koleksi vintage Anda</p>
        </div>
        
        <form id="registerForm" onsubmit="handleRegister(event)" style="display: flex; flex-direction: column; gap: 16px;">
            <div>
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600; font-size: 14px;">Nama Lengkap <span style="color: #DC2626;">*</span></label>
                <input type="text" name="full_name" required style="width: 100%; padding: 12px 16px; border: 1.5px solid #E5E7EB; border-radius: 10px; font-size: 15px; transition: all 0.2s; font-family: inherit;" placeholder="Nama lengkap Anda">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600; font-size: 14px;">Email <span style="color: #DC2626;">*</span></label>
                <input type="email" id="registerEmail" name="email" required style="width: 100%; padding: 12px 16px; border: 1.5px solid #E5E7EB; border-radius: 10px; font-size: 15px; transition: all 0.2s; font-family: inherit;" placeholder="your@email.com">
                <div id="emailValidation" style="margin-top: 6px; font-size: 13px; display: none;"></div>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600; font-size: 14px;">Username <span style="color: #DC2626;">*</span></label>
                <input type="text" id="registerUsername" name="username" required style="width: 100%; padding: 12px 16px; border: 1.5px solid #E5E7EB; border-radius: 10px; font-size: 15px; transition: all 0.2s; font-family: inherit;" placeholder="Pilih username unik">
                <div id="usernameValidation" style="margin-top: 6px; font-size: 13px; display: none;"></div>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600; font-size: 14px;">Password <span style="color: #DC2626;">*</span></label>
                <input type="text" id="registerPassword" name="password" required minlength="8" style="width: 100%; padding: 12px 16px; border: 1.5px solid #E5E7EB; border-radius: 10px; font-size: 15px; transition: all 0.2s; font-family: inherit;" placeholder="Minimal 8 karakter">
                <div style="margin-top: 12px; display: flex; flex-direction: column; gap: 6px;">
                    <div style="display: flex; align-items: center; gap: 8px; color: #9CA3AF; font-size: 13px;">
                        <span id="registerLengthCheck" style="font-weight: bold;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display: inline-block; vertical-align: middle;">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </span> Minimal 8 karakter
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; color: #9CA3AF; font-size: 13px;">
                        <span id="registerCaseCheck" style="font-weight: bold;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display: inline-block; vertical-align: middle;">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </span> Huruf besar, huruf kecil, dan satu angka
                    </div>
                </div>
            </div>
            
            <button type="submit" style="width: 100%; padding: 14px; background: #D97706; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s; margin-top: 8px;">
                Buat Akun
            </button>
        </form>
        
        <div style="margin-top: 24px; text-align: center;">
            <p style="color: #6B7280; font-size: 14px;">
                Sudah punya akun? 
                <a href="javascript:void(0)" onclick="switchToLogin()" style="color: #D97706; font-weight: 600; text-decoration: none;">Masuk di sini</a>
            </p>
        </div>
    `;
    
    return modal;
}

// Close Modal
function closeAuthModal() {
    const overlay = document.querySelector('.auth-modal-overlay');
    const modal = document.querySelector('.auth-modal');
    
    // Clear all intervals when closing
    clearAllIntervals();
    
    // Reset forgot password state
    currentUserId = null;
    currentEmail = null;
    
    // Reset register verification state
    currentRegisterEmail = null;
    
    if (overlay && modal) {
        if (overlay.parentElement) {
            document.body.removeChild(overlay);
        }
    }
}

// Setup Close Handlers
function setupCloseHandlers(overlay, modal) {
    // Close on overlay click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeAuthModal();
        }
    });
    
    // Close on ESC key
    const escHandler = (e) => {
        if (e.key === 'Escape') {
            closeAuthModal();
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
    
    // Close button hover effect
    const closeBtn = modal.querySelector('.auth-modal-close');
    closeBtn.addEventListener('mouseover', function() {
        this.style.color = '#DC2626';
    });
    closeBtn.addEventListener('mouseout', function() {
        this.style.color = '#6B7280';
    });
    
    // Input focus effects
    modal.querySelectorAll('input, textarea').forEach(input => {
        input.addEventListener('focus', function() {
            this.style.borderColor = '#D97706';
            this.style.outline = 'none';
        });
        input.addEventListener('blur', function() {
            this.style.borderColor = '#E5E7EB';
        });
    });
    
    // Button hover effect
    modal.querySelectorAll('button[type="submit"]').forEach(btn => {
        btn.addEventListener('mouseover', function() {
            this.style.background = '#B45309';
            this.style.transform = 'translateY(-2px)';
        });
        btn.addEventListener('mouseout', function() {
            this.style.background = '#D97706';
            this.style.transform = 'translateY(0)';
        });
    });
}

// Show Forgot Password Modal
function showForgotPasswordModal(email = '') {
    // Try to get email from various sources if not provided
    if (!email) {
        // Try from localStorage (last registered or login email)
        const savedEmail = localStorage.getItem('lastUserEmail');
        if (savedEmail) {
            email = savedEmail;
        }
    }
    
    const overlay = createAuthOverlay();
    const modal = createForgotPasswordModal(email);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Show immediately without animation
    overlay.style.opacity = '1';
    modal.style.opacity = '1';
    modal.style.transform = 'scale(1)';
    
    // Setup close handlers
    setupCloseHandlers(overlay, modal);
}

// Show New Password Modal - Step 2
function showNewPasswordModal(email, userId) {
    const overlay = createAuthOverlay();
    const modal = createNewPasswordModal(email, userId);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Show immediately without animation
    overlay.style.opacity = '1';
    modal.style.opacity = '1';
    modal.style.transform = 'scale(1)';
    
    // Setup close handlers
    setupCloseHandlers(overlay, modal);
    
    console.log('📝 New Password Modal opened for:', email);
}

// Show Email Verification Modal - For Register
function showEmailVerificationModal(email) {
    const overlay = createAuthOverlay();
    const modal = createEmailVerificationModal(email);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Show immediately without animation
    overlay.style.opacity = '1';
    modal.style.opacity = '1';
    modal.style.transform = 'scale(1)';
    
    // Setup close handlers
    setupCloseHandlers(overlay, modal);
    
    // Start OTP expiry countdown (10 minutes = 600 seconds)
    startRegisterOTPExpiryCountdown(600);
    
    // Start resend cooldown (60 seconds)
    startRegisterResendCooldown(60);
    
    console.log('📧 Email Verification Modal opened for:', email);
}

// Create Forgot Password Modal - Step 1: Email & OTP
function createForgotPasswordModal(email = '') {
    const modal = document.createElement('div');
    modal.className = 'auth-modal';
    modal.id = 'forgotPasswordModalContent';
    modal.style.cssText = `
        background: white;
        border-radius: 20px;
        padding: 40px;
        max-width: 480px;
        width: 90%;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        transform: scale(1);
        opacity: 1;
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
    `;
    
    modal.innerHTML = `
        <button class="auth-modal-close" onclick="closeAuthModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; cursor: pointer; padding: 8px; color: #6B7280; transition: color 0.2s;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="width: 64px; height: 64px; background: #FEF3C7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <h2 style="font-size: 28px; font-weight: 800; color: #1F2937; margin-bottom: 8px; font-family: 'Playfair Display', serif;">Verifikasi Identitas Anda</h2>
            <p style="color: #6B7280; font-size: 15px;">Masukkan email Anda untuk menerima kode verifikasi</p>
        </div>
        
        <form id="forgotPasswordForm" onsubmit="handleForgotPassword(event)" style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Email Section -->
            <div id="emailSection">
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600; font-size: 14px;">Alamat Email*</label>
                <input type="email" id="forgotEmail" name="email" value="${email}" required style="width: 100%; padding: 12px 16px; border: 1.5px solid #E5E7EB; border-radius: 10px; font-size: 15px; transition: all 0.2s; font-family: inherit;" placeholder="email@anda.com">
                <p id="emailHelper" style="margin-top: 8px; color: #6B7280; font-size: 13px;">Kami akan mengirim kode verifikasi ke email ini</p>
            </div>
            
            <!-- OTP Section (Hidden initially) -->
            <div id="otpSection" style="display: none;">
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600; font-size: 14px;">Kode Verifikasi*</label>
                <input type="text" id="otpCode" name="code" maxlength="6" pattern="[0-9]{6}" disabled style="width: 100%; padding: 12px 16px; border: 1.5px solid #E5E7EB; border-radius: 10px; font-size: 20px; letter-spacing: 6px; font-weight: 600; text-align: center; transition: all 0.2s; font-family: monospace;" placeholder="000000">
                <p style="margin-top: 8px; color: #6B7280; font-size: 13px;">
                    Masukkan kode 6 digit yang dikirim ke email Anda
                </p>
                <div id="otpExpiryTimer" style="margin-top: 8px; padding: 10px; background: #FEF3C7; border-radius: 6px; font-size: 13px; color: #92400E; font-weight: 600; text-align: center;"></div>
                
                <!-- Resend Code Button -->
                <div style="margin-top: 12px; text-align: center;">
                    <button type="button" onclick="resendVerificationCode()" id="resendCodeBtn" style="background: none; border: none; color: #9CA3AF; cursor: not-allowed; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 6px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                        </svg>
                        Kirim Ulang Kode (<span id="resendTimer">60</span>s)
                    </button>
                </div>
            </div>
            
            <button type="submit" id="submitBtnForgot" style="width: 100%; padding: 14px; background: #D97706; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s; margin-top: 8px;">
                Kirim Kode Verifikasi
            </button>
        </form>
        
        <div style="margin-top: 24px; text-align: center;">
            <p style="color: #6B7280; font-size: 14px;">
                Ingat password Anda? 
                <a href="javascript:void(0)" onclick="switchToLogin()" style="color: #D97706; font-weight: 600; text-decoration: none;">Masuk di sini</a>
            </p>
        </div>
    `;
    
    return modal;
}

// Create New Password Modal - Step 2: Set New Password
function createNewPasswordModal(email, userId) {
    const modal = document.createElement('div');
    modal.className = 'auth-modal';
    modal.id = 'newPasswordModalContent';
    modal.style.cssText = `
        background: white;
        border-radius: 20px;
        padding: 40px;
        max-width: 480px;
        width: 90%;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        transform: scale(1);
        opacity: 1;
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
    `;
    
    modal.innerHTML = `
        <button class="auth-modal-close" onclick="closeAuthModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; cursor: pointer; padding: 8px; color: #6B7280; transition: color 0.2s;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="width: 64px; height: 64px; background: #D1FAE5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <h2 style="font-size: 28px; font-weight: 800; color: #1F2937; margin-bottom: 8px; font-family: 'Playfair Display', serif;">Buat Password Baru</h2>
            <p style="color: #6B7280; font-size: 15px;">Identitas Anda telah diverifikasi. Buat password baru yang aman.</p>
        </div>
        
        <form id="newPasswordForm" onsubmit="handleNewPassword(event)" style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Password Field -->
            <div>
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600; font-size: 14px;">Password Baru*</label>
                <div style="position: relative;">
                    <input type="password" id="newPasswordField" name="password" required minlength="8" style="width: 100%; padding: 12px 40px 12px 16px; border: 1.5px solid #E5E7EB; border-radius: 10px; font-size: 15px; transition: all 0.2s; font-family: inherit;" placeholder="Masukkan password baru Anda">
                    <button type="button" onclick="togglePasswordVisibility('newPasswordField')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9CA3AF; cursor: pointer; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                
                <!-- Password Requirements -->
                <div style="margin-top: 12px; padding: 16px; background: #F9FAFB; border-radius: 10px; border: 1px solid #E5E7EB;">
                    <p style="color: #374151; margin-bottom: 12px; font-weight: 600; font-size: 13px;">Password harus mengandung:</p>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span id="lengthCheckNew" style="width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #E5E7EB; color: #9CA3AF; font-size: 11px; font-weight: 700;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </span>
                            <span style="color: #6B7280; font-size: 13px;">Minimal 8 karakter</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span id="caseCheckNew" style="width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #E5E7EB; color: #9CA3AF; font-size: 11px; font-weight: 700;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </span>
                            <span style="color: #6B7280; font-size: 13px;">Huruf besar, huruf kecil & angka</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" id="submitBtnNewPassword" style="width: 100%; padding: 14px; background: #D97706; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s; margin-top: 8px;">
                Reset Password
            </button>
        </form>
    `;
    
    return modal;
}

// Create Email Verification Modal - For Register
function createEmailVerificationModal(email) {
    // PERBAIKAN: Pastikan email valid sebelum membuat modal
    const safeEmail = email || currentRegisterEmail || localStorage.getItem('lastUserEmail') || '';
    
    console.log('🏗️ Creating verification modal with email:', safeEmail);
    
    const modal = document.createElement('div');
    modal.className = 'auth-modal';
    modal.id = 'emailVerificationModalContent';
    modal.style.cssText = `
        background: white;
        border-radius: 20px;
        padding: 40px;
        max-width: 480px;
        width: 90%;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        transform: scale(1);
        opacity: 1;
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
    `;
    
    modal.innerHTML = `
        <button class="auth-modal-close" onclick="closeAuthModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; cursor: pointer; padding: 8px; color: #6B7280; transition: color 0.2s;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.2);">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </div>
            <h2 style="font-size: 28px; font-weight: 800; color: #1F2937; margin-bottom: 8px; font-family: 'Playfair Display', serif;">Verifikasi Email</h2>
            <p style="color: #6B7280; font-size: 15px; line-height: 1.6;">Kami telah mengirimkan kode verifikasi 6 digit ke email Anda</p>
        </div>
        
        <form id="emailVerificationForm" onsubmit="handleEmailVerification(event)" style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Email Display (Readonly) -->
            <div>
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600; font-size: 14px;">Email</label>
                <div style="position: relative;">
                    <input type="email" id="verificationEmail" value="${safeEmail}" readonly style="width: 100%; padding: 12px 16px 12px 44px; border: 1.5px solid #E5E7EB; border-radius: 10px; font-size: 15px; background: #F9FAFB; color: #6B7280; cursor: not-allowed; font-family: inherit;" placeholder="your@email.com">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%);">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </div>
            </div>
            
            <!-- OTP Input -->
            <div>
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600; font-size: 14px;">Kode Verifikasi*</label>
                <input type="text" id="registerOtpCode" name="code" maxlength="6" pattern="[0-9]{6}" required style="width: 100%; padding: 16px; border: 2px solid #E5E7EB; border-radius: 12px; font-size: 28px; letter-spacing: 12px; font-weight: 700; text-align: center; transition: all 0.2s; font-family: monospace; background: #FFFFFF;" placeholder="000000">
                <p style="margin-top: 10px; color: #6B7280; font-size: 13px; text-align: center;">
                    Masukkan kode 6 digit yang telah dikirim ke email Anda
                </p>
                
                <!-- OTP Expiry Timer -->
                <div id="registerOtpExpiryTimer" style="margin-top: 12px; padding: 12px; background: #FEF3C7; border-left: 4px solid #D97706; border-radius: 8px; font-size: 13px; color: #92400E; font-weight: 600; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span>Kode berlaku: <span id="registerOtpTimer">10:00</span></span>
                </div>
                
                <!-- Resend Code Button -->
                <div style="margin-top: 16px; text-align: center;">
                    <button type="button" onclick="resendRegisterVerificationCode()" id="registerResendCodeBtn" style="background: none; border: none; color: #9CA3AF; cursor: not-allowed; font-size: 14px; font-weight: 600; padding: 10px 20px; border-radius: 8px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                        </svg>
                        Kirim Ulang Kode (<span id="registerResendTimer">60</span>s)
                    </button>
                </div>
            </div>
            
            <button type="submit" id="submitBtnEmailVerification" style="width: 100%; padding: 16px; background: #D97706; color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s; margin-top: 8px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);">
                Verifikasi Email
            </button>
        </form>
        
        <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #E5E7EB; text-align: center;">
            <p style="color: #6B7280; font-size: 13px; line-height: 1.6;">
                Tidak menerima kode? Periksa folder spam atau 
                <a href="javascript:void(0)" onclick="switchToRegister()" style="color: #D97706; font-weight: 600; text-decoration: none;">daftar ulang</a>
            </p>
        </div>
    `;
    
    return modal;
}

// Show Reset Password Modal
function showResetPasswordModal(email, userId) {
    const overlay = createAuthOverlay();
    const modal = createResetPasswordModal(email, userId);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Show immediately without animation
    overlay.style.opacity = '1';
    modal.style.opacity = '1';
    modal.style.transform = 'scale(1)';
    
    // Setup close handlers
    setupCloseHandlers(overlay, modal);
    
    // Start countdown
    startResendCountdown();
}

// Create Reset Password Modal
function createResetPasswordModal(email, userId) {
    const modal = document.createElement('div');
    modal.className = 'auth-modal';
    modal.style.cssText = `
        background: #2D3748;
        color: white;
        border-radius: 20px;
        padding: 40px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        transform: scale(1);
        opacity: 1;
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
    `;
    
    modal.innerHTML = `
        <button class="auth-modal-close" onclick="closeAuthModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; cursor: pointer; padding: 8px; color: #A0AEC0; transition: color 0.2s;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <div style="margin-bottom: 32px;">
            <h2 style="font-size: 24px; font-weight: 700; color: white; margin-bottom: 12px;">Verify your email and enter a new password.</h2>
            <p style="color: #A0AEC0; font-size: 15px; margin-bottom: 4px;">
                We've sent a code to 
                <span style="color: white; font-weight: 600;">${email}</span>
                <a href="javascript:void(0)" onclick="switchToForgotPassword('${email}')" style="color: #D97706; margin-left: 8px; text-decoration: underline;">Edit</a>
            </p>
        </div>
        
        <form id="resetPasswordForm" onsubmit="handleResetPassword(event, '${email}', '${userId}')" style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; color: #E2E8F0; font-weight: 600; font-size: 14px;">Code*</label>
                <div style="position: relative;">
                    <input type="text" name="code" required maxlength="6" style="width: 100%; padding: 12px 40px 12px 16px; border: 1.5px solid #4A5568; background: #1A202C; color: white; border-radius: 10px; font-size: 15px; transition: all 0.2s; font-family: inherit;" placeholder="Enter 6-digit code">
                    <button type="button" onclick="resendCode('${email}', '${userId}')" id="resendBtn" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #A0AEC0; cursor: not-allowed; font-size: 20px; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;" disabled>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                        </svg>
                    </button>
                </div>
                <p id="resendTimer" style="margin-top: 8px; color: #A0AEC0; font-size: 13px; text-align: right;">Resend code in 8s</p>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; color: #E2E8F0; font-weight: 600; font-size: 14px;">New Password*</label>
                <div style="position: relative;">
                    <input type="password" id="newPassword" name="password" required minlength="8" style="width: 100%; padding: 12px 40px 12px 16px; border: 1.5px solid #4A5568; background: #1A202C; color: white; border-radius: 10px; font-size: 15px; transition: all 0.2s; font-family: inherit;" placeholder="Enter new password">
                    <button type="button" onclick="togglePasswordVisibility('newPassword')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #A0AEC0; cursor: pointer; padding: 0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
                <div style="margin-top: 12px; display: flex; flex-direction: column; gap: 6px;">
                    <div style="display: flex; align-items: center; gap: 8px; color: #718096; font-size: 13px;">
                        <span id="lengthCheck">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display: inline-block; vertical-align: middle;">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </span> Minimum of 8 characters
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; color: #718096; font-size: 13px;">
                        <span id="caseCheck">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display: inline-block; vertical-align: middle;">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </span> Uppercase, lowercase letters and one number
                    </div>
                </div>
            </div>
            
            <button type="submit" id="saveBtn" style="width: 100%; padding: 14px; background: #48BB78; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s; margin-top: 8px;">
                Save
            </button>
            
            <button type="button" onclick="switchToLogin()" style="width: 100%; padding: 14px; background: transparent; color: #E2E8F0; border: 2px solid #4A5568; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s;">
                Cancel
            </button>
        </form>
    `;
    
    return modal;
}

// Switch between modals
function switchToRegister() {
    closeAuthModal();
    showRegisterModal();
}

function switchToLogin() {
    closeAuthModal();
    showLoginModal();
}

function switchToForgotPassword(email = '') {
    closeAuthModal();
    
    // If no email provided, try to get from login form
    if (!email) {
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            const usernameInput = loginForm.querySelector('input[name="username"]');
            if (usernameInput && usernameInput.value) {
                email = usernameInput.value;
            }
        }
    }
    
    showForgotPasswordModal(email);
}

// Handle Login
function handleLogin(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = {
        action: 'login',
        username: formData.get('username'),
        password: formData.get('password')
    };
    
    // Show loading
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Memuat...';
    submitBtn.disabled = true;
    
    // Send AJAX request
    // Get base URL dynamically (works from any folder)
    const baseUrl = window.location.pathname.includes('/customer/') || window.location.pathname.includes('/admin/') ? '../' : '';
    
    fetch(baseUrl + 'auth/process-auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Save email to localStorage for forgot password
            if (result.email) {
                localStorage.setItem('lastUserEmail', result.email);
            }
            
            closeAuthModal();
            // Redirect berdasarkan role
            if (result.redirect_url) {
                // Pastikan redirect URL benar dengan baseUrl
                window.location.href = baseUrl + result.redirect_url;
            } else {
                window.location.reload();
            }
        } else {
            toastError(result.message);
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        toastError('Terjadi kesalahan. Silakan coba lagi.');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

// Handle Register
let currentRegisterEmail = null;

function handleRegister(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const password = formData.get('password');
    
    // Validate password
    if (password.length < 8) {
        toastError('Password minimal 8 karakter!');
        return;
    }
    
    if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
        toastError('Password harus mengandung huruf besar, huruf kecil, dan angka!');
        return;
    }
    
    const email = formData.get('email');
    
    const data = {
        action: 'register',
        username: formData.get('username'),
        password: password,
        full_name: formData.get('full_name'),
        email: email
    };
    
    // Save email to localStorage for future use
    if (email) {
        localStorage.setItem('lastUserEmail', email);
    }
    
    // Show loading
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Membuat Akun...';
    submitBtn.disabled = true;
    
    // Send AJAX request
    // Get base URL dynamically (works from any folder)
    const baseUrl = window.location.pathname.includes('/customer/') || window.location.pathname.includes('/admin/') ? '../' : '';
    
    fetch(baseUrl + 'auth/process-auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        console.log('📧 Register Response:', result);
        
        if (result.success) {
            // Show success toast with email status
            if (result.dev_code) {
                // Development mode - show code in toast
                console.log('🔑 Development Mode - OTP Code:', result.dev_code);
                console.log('📧 Email Status:', result.email_sent ? 'Sent' : 'Failed');
                toastSuccess('Kode verifikasi: ' + result.dev_code + ' (Check email untuk kode asli)');
            } else {
                // Production mode - normal message
                console.log('📧 Email sent successfully to:', email);
                toastSuccess(result.message || 'Kode verifikasi telah dikirim ke email Anda!');
            }
            
            // PERBAIKAN: Gunakan email dari form, bukan dari result
            currentRegisterEmail = email;
            
            console.log('💾 Stored email for verification:', currentRegisterEmail);
            
            // CRITICAL: Save email to local variable BEFORE closeAuthModal() resets it!
            const savedEmail = email;
            
            // Close register modal and show email verification modal
            closeAuthModal();
            setTimeout(() => {
                // Use savedEmail, NOT currentRegisterEmail (which is now null after closeAuthModal)
                console.log('🔓 Opening verification modal with email:', savedEmail);
                showEmailVerificationModal(savedEmail);
                // Restore currentRegisterEmail after modal is created
                currentRegisterEmail = savedEmail;
            }, 300);
        } else {
            toastError(result.message);
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Register error:', error);
        toastError('Terjadi kesalahan. Silakan coba lagi.');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

// Handle Forgot Password
let currentUserId = null;
let currentEmail = null;
let otpExpiryInterval = null;
let resendCountdownInterval = null;

function handleForgotPassword(event) {
    event.preventDefault();
    
    console.log('📝 Form submitted - handleForgotPassword');
    
    const formData = new FormData(event.target);
    const email = formData.get('email');
    const code = formData.get('code');
    const password = formData.get('password');
    const submitBtn = document.getElementById('submitBtnForgot');
    
    console.log('📋 Form Data:', { email, code, password: password ? '***' : null, currentUserId });
    
    // Check if this is initial email submission or final reset password submission
    if (!currentUserId) {
        // Step 1: Send OTP to email
        const data = {
            action: 'forgot_password',
            email: email
        };
        
        console.log('🔐 Forgot Password - Sending OTP request...', data);
        
        // Show loading
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Mengirim...';
        submitBtn.disabled = true;
        
        // Get base URL dynamically
        const baseUrl = window.location.pathname.includes('/customer/') || window.location.pathname.includes('/admin/') ? '../' : '';
        
        console.log('📡 API URL:', baseUrl + 'auth/process-auth.php');
        
        fetch(baseUrl + 'auth/process-auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            console.log('📥 Response received:', response.status, response.statusText);
            return response.json();
        })
        .then(result => {
            console.log('✅ Server Response:', result);
            
            if (result.success) {
                // Show success toast with email status
                if (result.dev_code) {
                    // Development mode - show code in toast
                    console.log('🔑 Development Mode - OTP Code:', result.dev_code);
                    console.log('📧 Email Status:', result.email_sent ? 'Sent' : 'Failed');
                    toastSuccess('Kode verifikasi: ' + result.dev_code + ' (Check email untuk kode asli)');
                } else {
                    // Production mode - normal message
                    console.log('📧 Email sent successfully to:', email);
                    toastSuccess(result.message || 'Kode verifikasi telah dikirim ke email Anda!');
                }
                
                // Store user info
                currentUserId = result.user_id;
                currentEmail = email;
                
                console.log('💾 User ID stored:', currentUserId);
                
                // Show OTP field only
                showOTPField();
                
                // Start OTP expiry countdown (5 minutes = 300 seconds)
                startOTPExpiryCountdown(300);
                
                // Start resend cooldown (60 seconds)
                startResendCooldown(60);
                
                submitBtn.textContent = 'Verify Code';
                submitBtn.disabled = false;
            } else {
                console.error('❌ Error:', result.message);
                
                // Jika email tidak terdaftar, highlight input field
                if (result.error_type === 'email_not_found') {
                    const emailInput = document.getElementById('forgotEmail');
                    if (emailInput) {
                        emailInput.style.borderColor = '#EF4444';
                        emailInput.style.backgroundColor = '#FEE2E2';
                        
                        // Reset style setelah 3 detik
                        setTimeout(() => {
                            emailInput.style.borderColor = '';
                            emailInput.style.backgroundColor = '';
                        }, 3000);
                    }
                    
                    toastError(result.message || 'Email tidak terdaftar!');
                } else {
                    toastError(result.message || 'Gagal mengirim kode verifikasi!');
                }
                
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('❌ Forgot password error:', error);
            console.error('Error details:', {
                message: error.message,
                stack: error.stack
            });
            toastError('Terjadi kesalahan koneksi. Silakan coba lagi.');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    } else {
        // Step 2: Verify OTP only
        
        if (!code || code.length !== 6) {
            toastError('Masukkan kode verifikasi 6 digit!');
            return;
        }
        
        const data = {
            action: 'verify_otp',
            email: currentEmail,
            user_id: currentUserId,
            code: code
        };
        
        console.log('🔑 Verifying OTP...', data);
        
        // Show loading
        submitBtn.textContent = 'Memverifikasi...';
        submitBtn.disabled = true;
        
        // Get base URL dynamically
        const baseUrl = window.location.pathname.includes('/customer/') || window.location.pathname.includes('/admin/') ? '../' : '';
        
        fetch(baseUrl + 'auth/process-auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            console.log('✅ OTP Verification Response:', result);
            
            if (result.success) {
                toastSuccess('Kode verifikasi benar! Silakan buat password baru.');
                
                // PERBAIKAN BUG: Update currentUserId dan currentEmail dari response verify_otp
                currentUserId = result.user_id;
                currentEmail = result.email;
                
                console.log('💾 Updated from verify_otp - User ID:', currentUserId, 'Email:', currentEmail);
                
                // PENTING: Simpan ke variabel lokal sebelum closeAuthModal() me-reset global variable
                const savedEmail = currentEmail;
                const savedUserId = currentUserId;
                
                console.log('💾 Saved to local variables - User ID:', savedUserId, 'Email:', savedEmail);
                
                // Clear intervals
                clearAllIntervals();
                
                // Close forgot password modal and show new password modal
                closeAuthModal();
                setTimeout(() => {
                    // Restore nilai setelah closeAuthModal()
                    currentUserId = savedUserId;
                    currentEmail = savedEmail;
                    console.log('💾 Restored after closeAuthModal - User ID:', currentUserId, 'Email:', currentEmail);
                    
                    showNewPasswordModal(currentEmail, currentUserId);
                }, 300);
            } else {
                console.error('❌ OTP Verification Failed:', result.message);
                toastError(result.message);
                submitBtn.textContent = 'Verify Code';
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('❌ Verify OTP error:', error);
            toastError('Terjadi kesalahan. Silakan coba lagi.');
            submitBtn.textContent = 'Verify Code';
            submitBtn.disabled = false;
        });
    }
}

// Show OTP field after email is sent
function showOTPField() {
    console.log('🔓 Showing OTP field...');
    
    // Disable email field
    const emailInput = document.getElementById('forgotEmail');
    emailInput.disabled = true;
    emailInput.style.background = '#F3F4F6';
    emailInput.style.cursor = 'not-allowed';
    
    // Update helper text with success message
    const emailHelper = document.getElementById('emailHelper');
    emailHelper.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5" style="display: inline-block; vertical-align: middle; margin-right: 4px;"><polyline points="20 6 9 17 4 12"></polyline></svg><strong style="color: #10B981;">Verification code sent!</strong> Check your email.';
    emailHelper.style.color = '#10B981';
    
    // Show OTP section
    const otpSection = document.getElementById('otpSection');
    otpSection.style.display = 'block';
    
    // Enable OTP field
    const otpField = document.getElementById('otpCode');
    otpField.disabled = false;
    otpField.required = true;
    
    console.log('✅ OTP Field enabled');
    
    // Focus on OTP input
    setTimeout(() => {
        otpField.focus();
        console.log('🎯 Focus set to OTP field');
    }, 100);
}

// Start OTP expiry countdown timer
function startOTPExpiryCountdown(seconds) {
    const timerElement = document.getElementById('otpExpiryTimer');
    let remaining = seconds;
    
    // Clear any existing interval
    if (otpExpiryInterval) {
        clearInterval(otpExpiryInterval);
    }
    
    otpExpiryInterval = setInterval(() => {
        remaining--;
        
        const minutes = Math.floor(remaining / 60);
        const secs = remaining % 60;
        
        if (remaining > 0) {
            timerElement.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Kode berlaku ${minutes}:${secs.toString().padStart(2, '0')}
            `;
            timerElement.style.background = '#FEF3C7';
            timerElement.style.color = '#92400E';
        } else {
            clearInterval(otpExpiryInterval);
            timerElement.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                Kode kadaluarsa! Silakan minta kode baru.
            `;
            timerElement.style.background = '#FEE2E2';
            timerElement.style.color = '#DC2626';
        }
    }, 1000);
}

// Start resend cooldown
function startResendCooldown(seconds) {
    const resendBtn = document.getElementById('resendCodeBtn');
    const timerSpan = document.getElementById('resendTimer');
    let remaining = seconds;
    
    resendBtn.disabled = true;
    resendBtn.style.cursor = 'not-allowed';
    resendBtn.style.color = '#9CA3AF';
    
    // Clear any existing interval
    if (resendCountdownInterval) {
        clearInterval(resendCountdownInterval);
    }
    
    resendCountdownInterval = setInterval(() => {
        remaining--;
        timerSpan.textContent = remaining;
        
        if (remaining <= 0) {
            clearInterval(resendCountdownInterval);
            resendBtn.disabled = false;
            resendBtn.style.cursor = 'pointer';
            resendBtn.style.color = '#D97706';
            resendBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg> Resend Code';
        }
    }, 1000);
}

// Resend verification code (new function for forgot password flow)
function resendVerificationCode() {
    if (!currentUserId || !currentEmail) {
        toastError('Session expired. Please refresh and try again.');
        return;
    }
    
    const resendBtn = document.getElementById('resendCodeBtn');
    resendBtn.disabled = true;
    
    const data = {
        action: 'resend_code',
        email: currentEmail,
        user_id: currentUserId
    };
    
    // Get base URL dynamically
    const baseUrl = window.location.pathname.includes('/customer/') || window.location.pathname.includes('/admin/') ? '../' : '';
    
    fetch(baseUrl + 'auth/process-auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Show success toast
            if (result.dev_code) {
                // Development mode - show code in toast
                toastSuccess('Kode baru: ' + result.dev_code + ' (Check email)');
                console.log('Development Mode - New OTP Code:', result.dev_code);
            } else {
                toastSuccess(result.message || 'Kode verifikasi baru telah dikirim!');
            }
            
            // Clear OTP field for new code
            document.getElementById('otpCode').value = '';
            document.getElementById('otpCode').focus();
            
            // Restart timers
            startOTPExpiryCountdown(300); // 5 minutes
            startResendCooldown(60); // 60 seconds cooldown
        } else {
            toastError(result.message || 'Gagal mengirim kode baru!');
            resendBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Resend code error:', error);
        toastError('Terjadi kesalahan. Silakan coba lagi.');
        resendBtn.disabled = false;
    });
}

// Clear all intervals
function clearAllIntervals() {
    if (otpExpiryInterval) {
        clearInterval(otpExpiryInterval);
        otpExpiryInterval = null;
    }
    if (resendCountdownInterval) {
        clearInterval(resendCountdownInterval);
        resendCountdownInterval = null;
    }
    if (registerOtpExpiryInterval) {
        clearInterval(registerOtpExpiryInterval);
        registerOtpExpiryInterval = null;
    }
    if (registerResendCountdownInterval) {
        clearInterval(registerResendCountdownInterval);
        registerResendCountdownInterval = null;
    }
}

// ===== EMAIL VERIFICATION FOR REGISTER =====
let registerOtpExpiryInterval = null;
let registerResendCountdownInterval = null;

// Handle Email Verification submission
function handleEmailVerification(event) {
    event.preventDefault();
    
    console.log('📧 Email Verification Form submitted');
    
    const formData = new FormData(event.target);
    const code = formData.get('code');
    const emailInput = document.getElementById('verificationEmail');
    
    // PERBAIKAN: Cek berbagai sumber email
    let email = null;
    
    // 1. Coba dari input field
    if (emailInput && emailInput.value && emailInput.value !== 'null' && emailInput.value !== '') {
        email = emailInput.value;
        console.log('✅ Email from input field:', email);
    }
    // 2. Coba dari variable global
    else if (currentRegisterEmail && currentRegisterEmail !== 'null' && currentRegisterEmail !== '') {
        email = currentRegisterEmail;
        console.log('✅ Email from currentRegisterEmail:', email);
    }
    // 3. Coba dari localStorage
    else if (localStorage.getItem('lastUserEmail')) {
        email = localStorage.getItem('lastUserEmail');
        console.log('✅ Email from localStorage:', email);
    }
    
    const submitBtn = document.getElementById('submitBtnEmailVerification');
    
    console.log('🔍 Debug Info:');
    console.log('  - Email Input Element:', emailInput);
    console.log('  - Email Input Value:', emailInput ? emailInput.value : 'N/A');
    console.log('  - currentRegisterEmail:', currentRegisterEmail);
    console.log('  - Final Email:', email);
    
    if (!email || email === 'null' || email === '') {
        toastError('Email tidak ditemukan! Silakan daftar ulang.');
        console.error('❌ All email sources failed!');
        return;
    }
    
    if (!code || code.length !== 6) {
        toastError('Masukkan kode verifikasi 6 digit!');
        return;
    }
    
    const data = {
        action: 'verify_register_otp',
        email: email,
        code: code
    };
    
    console.log('🔑 Verifying Register OTP...', data);
    
    // Show loading
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Memverifikasi...';
    submitBtn.disabled = true;
    
    // Get base URL dynamically
    const baseUrl = window.location.pathname.includes('/customer/') || window.location.pathname.includes('/admin/') ? '../' : '';
    
    fetch(baseUrl + 'auth/process-auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        console.log('✅ Email Verification Response:', result);
        
        if (result.success) {
            toastSuccess('Registrasi berhasil! Anda akan otomatis login...');
            
            // Clear intervals
            clearAllIntervals();
            
            // Reset state
            currentRegisterEmail = null;
            
            // Close modal
            closeAuthModal();
            
            // Auto-login: Reload halaman untuk membuat session aktif
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            console.error('❌ Email Verification Failed:', result.message);
            toastError(result.message);
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('❌ Email Verification error:', error);
        toastError('Terjadi kesalahan. Silakan coba lagi.');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

// Start Register OTP expiry countdown timer
function startRegisterOTPExpiryCountdown(seconds) {
    const timerElement = document.getElementById('registerOtpTimer');
    const timerBox = document.getElementById('registerOtpExpiryTimer');
    
    if (!timerElement || !timerBox) return;
    
    let remainingTime = seconds;
    
    function updateTimer() {
        const minutes = Math.floor(remainingTime / 60);
        const secs = remainingTime % 60;
        timerElement.textContent = `${minutes}:${secs.toString().padStart(2, '0')}`;
        
        // Change color based on remaining time
        if (remainingTime <= 60) {
            timerBox.style.background = '#FEE2E2';
            timerBox.style.borderLeftColor = '#DC2626';
            timerBox.style.color = '#991B1B';
        } else if (remainingTime <= 180) {
            timerBox.style.background = '#FED7AA';
            timerBox.style.borderLeftColor = '#EA580C';
            timerBox.style.color = '#9A3412';
        }
        
        if (remainingTime <= 0) {
            clearInterval(registerOtpExpiryInterval);
            timerElement.textContent = 'EXPIRED';
            timerBox.style.background = '#FEE2E2';
            timerBox.style.borderLeftColor = '#DC2626';
            timerBox.style.color = '#991B1B';
            toastError('⏰ Kode verifikasi telah kadaluarsa! Silakan kirim ulang kode.');
        }
        
        remainingTime--;
    }
    
    updateTimer();
    registerOtpExpiryInterval = setInterval(updateTimer, 1000);
}

// Start Register Resend cooldown timer
function startRegisterResendCooldown(seconds) {
    const resendBtn = document.getElementById('registerResendCodeBtn');
    const resendTimer = document.getElementById('registerResendTimer');
    
    if (!resendBtn || !resendTimer) return;
    
    let remainingTime = seconds;
    
    resendBtn.disabled = true;
    resendBtn.style.cursor = 'not-allowed';
    resendBtn.style.color = '#9CA3AF';
    
    function updateResendTimer() {
        resendTimer.textContent = remainingTime;
        
        if (remainingTime <= 0) {
            clearInterval(registerResendCountdownInterval);
            resendBtn.disabled = false;
            resendBtn.style.cursor = 'pointer';
            resendBtn.style.color = '#D97706';
            resendBtn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                Kirim Ulang Kode
            `;
            
            // Add hover effect
            resendBtn.addEventListener('mouseover', function() {
                this.style.background = '#FEF3C7';
            });
            resendBtn.addEventListener('mouseout', function() {
                this.style.background = 'none';
            });
        }
        
        remainingTime--;
    }
    
    updateResendTimer();
    registerResendCountdownInterval = setInterval(updateResendTimer, 1000);
}

// Resend Register verification code
function resendRegisterVerificationCode() {
    const email = document.getElementById('verificationEmail').value;
    
    if (!email) {
        toastError('Email tidak ditemukan!');
        return;
    }
    
    const resendBtn = document.getElementById('registerResendCodeBtn');
    const originalHTML = resendBtn.innerHTML;
    
    resendBtn.disabled = true;
    resendBtn.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;">
            <polyline points="23 4 23 10 17 10"></polyline>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
        </svg>
        Mengirim...
    `;
    
    const data = {
        action: 'resend_register_otp',
        email: email
    };
    
    console.log('📧 Resending Register OTP...', data);
    
    // Get base URL dynamically
    const baseUrl = window.location.pathname.includes('/customer/') || window.location.pathname.includes('/admin/') ? '../' : '';
    
    fetch(baseUrl + 'auth/process-auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        console.log('✅ Resend Register OTP Response:', result);
        
        if (result.success) {
            if (result.dev_code) {
                console.log('🔑 Development Mode - New OTP Code:', result.dev_code);
                toastSuccess('Kode baru: ' + result.dev_code);
            } else {
                toastSuccess(result.message);
            }
            
            // Clear OTP field for new code
            const otpField = document.getElementById('registerOtpCode');
            if (otpField) {
                otpField.value = '';
                otpField.focus();
            }
            
            // Restart timers
            clearInterval(registerOtpExpiryInterval);
            clearInterval(registerResendCountdownInterval);
            startRegisterOTPExpiryCountdown(600); // 10 minutes
            startRegisterResendCooldown(60); // 60 seconds
        } else {
            toastError(result.message);
            resendBtn.disabled = false;
            resendBtn.innerHTML = originalHTML;
        }
    })
    .catch(error => {
        console.error('❌ Resend Register OTP error:', error);
        toastError('Terjadi kesalahan. Silakan coba lagi.');
        resendBtn.disabled = false;
        resendBtn.innerHTML = originalHTML;
    });
}

// Handle New Password submission - Step 2
function handleNewPassword(event) {
    event.preventDefault();
    
    console.log('📝 New Password Form submitted');
    
    const formData = new FormData(event.target);
    const password = formData.get('password');
    const submitBtn = document.getElementById('submitBtnNewPassword');
    
    console.log('📋 Password validation...');
    
    // PERBAIKAN BUG: Validasi currentUserId dan currentEmail sebelum submit
    if (!currentUserId || !currentEmail) {
        console.error('❌ Missing user data:', { currentUserId, currentEmail });
        toastError('Session expired! Silakan ulangi proses forgot password dari awal.');
        closeAuthModal();
        setTimeout(() => {
            showForgotPasswordModal();
        }, 1000);
        return;
    }
    
    // Validate password
    if (!password || password.length < 8) {
        toastError('Password minimal 8 karakter!');
        return;
    }
    
    if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
        toastError('Password harus mengandung huruf besar, huruf kecil, dan angka!');
        return;
    }
    
    const data = {
        action: 'reset_password',
        email: currentEmail,
        user_id: currentUserId,
        password: password
    };
    
    console.log('🔐 Resetting password...', { email: currentEmail, user_id: currentUserId });
    
    // Show loading
    submitBtn.textContent = 'Resetting...';
    submitBtn.disabled = true;
    
    // Get base URL dynamically
    const baseUrl = window.location.pathname.includes('/customer/') || window.location.pathname.includes('/admin/') ? '../' : '';
    
    fetch(baseUrl + 'auth/process-auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        console.log('✅ Reset Password Response:', result);
        
        if (result.success) {
            toastSuccess(result.message);
            
            // Reset state
            currentUserId = null;
            currentEmail = null;
            
            // Close modal and show login
            closeAuthModal();
            setTimeout(() => {
                showLoginModal();
            }, 1000);
        } else {
            console.error('❌ Reset Password Failed:', result.message);
            toastError(result.message);
            submitBtn.textContent = 'Reset Password';
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('❌ Reset password error:', error);
        toastError('Terjadi kesalahan. Silakan coba lagi.');
        submitBtn.textContent = 'Reset Password';
        submitBtn.disabled = false;
    });
}

// Handle Reset Password
function handleResetPassword(event, email, userId) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const password = formData.get('password');
    
    // Validate password
    if (password.length < 8) {
        toastError('Password minimal 8 karakter!');
        return;
    }
    
    if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
        toastError('Password harus mengandung huruf besar, huruf kecil, dan angka!');
        return;
    }
    
    const data = {
        action: 'reset_password',
        email: email,
        user_id: userId,
        code: formData.get('code'),
        password: password
    };
    
    // Show loading
    const submitBtn = event.target.querySelector('#saveBtn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Saving...';
    submitBtn.disabled = true;
    
    // Send AJAX request
    // Get base URL dynamically (works from any folder)
    const baseUrl = window.location.pathname.includes('/customer/') || window.location.pathname.includes('/admin/') ? '../' : '';
    
    fetch(baseUrl + 'auth/process-auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            toastSuccess(result.message);
            closeAuthModal();
            setTimeout(() => {
                showLoginModal();
            }, 1000);
        } else {
            toastError(result.message);
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Reset password error:', error);
        toastError('Terjadi kesalahan. Silakan coba lagi.');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

// Toggle Password Visibility
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    
    if (input.type === 'password') {
        input.type = 'text';
        // Change to eye icon (mata terbuka) - password terlihat
        button.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        `;
    } else {
        input.type = 'password';
        // Change to eye-off icon (mata tertutup) - password tersembunyi
        button.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
        `;
    }
}

// Start Resend Countdown
let countdownInterval;
function startResendCountdown() {
    let seconds = 8;
    const resendBtn = document.getElementById('resendBtn');
    const resendTimer = document.getElementById('resendTimer');
    
    countdownInterval = setInterval(() => {
        seconds--;
        if (seconds > 0) {
            resendTimer.textContent = `Resend code in ${seconds}s`;
        } else {
            clearInterval(countdownInterval);
            resendTimer.textContent = '';
            resendBtn.disabled = false;
            resendBtn.style.cursor = 'pointer';
            resendBtn.style.color = '#D97706';
        }
    }, 1000);
}

// Resend Code
function resendCode(email, userId) {
    const resendBtn = document.getElementById('resendBtn');
    resendBtn.disabled = true;
    resendBtn.style.cursor = 'not-allowed';
    resendBtn.style.color = '#A0AEC0';
    
    const data = {
        action: 'resend_code',
        email: email,
        user_id: userId
    };
    
    // Get base URL dynamically (works from any folder)
    const baseUrl = window.location.pathname.includes('/customer/') || window.location.pathname.includes('/admin/') ? '../' : '';
    
    fetch(baseUrl + 'auth/process-auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            toastSuccess('Kode verifikasi telah dikirim ulang!');
            startResendCountdown();
        } else {
            toastError(result.message);
            resendBtn.disabled = false;
            resendBtn.style.cursor = 'pointer';
            resendBtn.style.color = '#D97706';
        }
    })
    .catch(error => {
        console.error('Resend code error:', error);
        toastError('Terjadi kesalahan. Silakan coba lagi.');
        resendBtn.disabled = false;
        resendBtn.style.cursor = 'pointer';
        resendBtn.style.color = '#D97706';
    });
}

// ===== REAL-TIME USERNAME & EMAIL VALIDATION =====
let usernameCheckTimeout = null;
let emailCheckTimeout = null;

function checkUsernameAvailability(username) {
    const validationDiv = document.getElementById('usernameValidation');
    const usernameInput = document.getElementById('registerUsername');
    
    if (!validationDiv || !usernameInput) return;
    
    // Reset jika username kosong
    if (!username || username.length < 3) {
        validationDiv.style.display = 'none';
        usernameInput.style.borderColor = '#E5E7EB';
        return;
    }
    
    // Show checking status
    validationDiv.style.display = 'block';
    validationDiv.style.color = '#6B7280';
    validationDiv.innerHTML = '⏳ Memeriksa ketersediaan...';
    
    // Get base URL
    const baseUrl = window.location.pathname.includes('/customer/') || window.location.pathname.includes('/admin/') ? '../' : '';
    
    // Send AJAX request
    fetch(baseUrl + 'auth/check-availability.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'check_username',
            username: username
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.available) {
            // Username tersedia
            validationDiv.style.display = 'block';
            validationDiv.style.color = '#10B981';
            validationDiv.innerHTML = '✅ ' + result.message;
            usernameInput.style.borderColor = '#10B981';
        } else {
            // Username sudah digunakan
            validationDiv.style.display = 'block';
            validationDiv.style.color = '#EF4444';
            validationDiv.innerHTML = '❌ ' + result.message;
            usernameInput.style.borderColor = '#EF4444';
        }
    })
    .catch(error => {
        console.error('Username check error:', error);
        validationDiv.style.display = 'none';
    });
}

function checkEmailAvailability(email) {
    const validationDiv = document.getElementById('emailValidation');
    const emailInput = document.getElementById('registerEmail');
    
    if (!validationDiv || !emailInput) return;
    
    // Reset jika email kosong
    if (!email) {
        validationDiv.style.display = 'none';
        emailInput.style.borderColor = '#E5E7EB';
        return;
    }
    
    // Validasi format email terlebih dahulu
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        validationDiv.style.display = 'block';
        validationDiv.style.color = '#EF4444';
        validationDiv.innerHTML = '❌ Format email tidak valid';
        emailInput.style.borderColor = '#EF4444';
        return;
    }
    
    // Show checking status
    validationDiv.style.display = 'block';
    validationDiv.style.color = '#6B7280';
    validationDiv.innerHTML = '⏳ Memeriksa ketersediaan...';
    
    // Get base URL
    const baseUrl = window.location.pathname.includes('/customer/') || window.location.pathname.includes('/admin/') ? '../' : '';
    
    // Send AJAX request
    fetch(baseUrl + 'auth/check-availability.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'check_email',
            email: email
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.available) {
            // Email tersedia
            validationDiv.style.display = 'block';
            validationDiv.style.color = '#10B981';
            validationDiv.innerHTML = '✅ ' + result.message;
            emailInput.style.borderColor = '#10B981';
        } else {
            // Email sudah digunakan atau diblokir
            validationDiv.style.display = 'block';
            validationDiv.style.color = '#EF4444';
            validationDiv.innerHTML = '❌ ' + result.message;
            emailInput.style.borderColor = '#EF4444';
        }
    })
    .catch(error => {
        console.error('Email check error:', error);
        validationDiv.style.display = 'none';
    });
}

// Password validation real-time
document.addEventListener('DOMContentLoaded', function() {
    // This will be triggered for both register and reset password modals
    document.addEventListener('input', function(e) {
        // Real-time username validation
        if (e.target.id === 'registerUsername') {
            const username = e.target.value.trim();
            
            // Clear previous timeout
            if (usernameCheckTimeout) {
                clearTimeout(usernameCheckTimeout);
            }
            
            // Set timeout untuk debounce (tunggu 500ms setelah user berhenti mengetik)
            usernameCheckTimeout = setTimeout(() => {
                checkUsernameAvailability(username);
            }, 500);
        }
        
        // Real-time email validation
        if (e.target.id === 'registerEmail') {
            const email = e.target.value.trim();
            
            // Clear previous timeout
            if (emailCheckTimeout) {
                clearTimeout(emailCheckTimeout);
            }
            
            // Set timeout untuk debounce (tunggu 500ms setelah user berhenti mengetik)
            emailCheckTimeout = setTimeout(() => {
                checkEmailAvailability(email);
            }, 500);
        }
        
        // Reset Password Modal (Dark theme)
        if (e.target.id === 'newPassword') {
            const password = e.target.value;
            const lengthCheck = document.getElementById('lengthCheck');
            const caseCheck = document.getElementById('caseCheck');
            
            if (lengthCheck && caseCheck) {
                // Check length
                if (password.length >= 8) {
                    lengthCheck.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display: inline-block; vertical-align: middle;"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                    lengthCheck.parentElement.style.color = '#48BB78'; // Green
                } else {
                    lengthCheck.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display: inline-block; vertical-align: middle;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
                    lengthCheck.parentElement.style.color = password.length > 0 ? '#EF4444' : '#718096'; // Red if typed, Gray if empty
                }
                
                // Check uppercase, lowercase, and number
                if (/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
                    caseCheck.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display: inline-block; vertical-align: middle;"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                    caseCheck.parentElement.style.color = '#48BB78'; // Green
                } else {
                    caseCheck.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display: inline-block; vertical-align: middle;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
                    caseCheck.parentElement.style.color = password.length > 0 ? '#EF4444' : '#718096'; // Red if typed, Gray if empty
                }
            }
        }
        
        // Register Password Modal (Light theme)
        if (e.target.id === 'registerPassword') {
            const password = e.target.value;
            const lengthCheck = document.getElementById('registerLengthCheck');
            const caseCheck = document.getElementById('registerCaseCheck');
            
            if (lengthCheck && caseCheck) {
                // Check length
                if (password.length >= 8) {
                    lengthCheck.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display: inline-block; vertical-align: middle;"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                    lengthCheck.parentElement.style.color = '#10B981'; // Green
                } else {
                    lengthCheck.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display: inline-block; vertical-align: middle;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
                    lengthCheck.parentElement.style.color = password.length > 0 ? '#EF4444' : '#9CA3AF'; // Red if typed, Gray if empty
                }
                
                // Check uppercase, lowercase, and number
                if (/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
                    caseCheck.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display: inline-block; vertical-align: middle;"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                    caseCheck.parentElement.style.color = '#10B981'; // Green
                } else {
                    caseCheck.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display: inline-block; vertical-align: middle;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
                    caseCheck.parentElement.style.color = password.length > 0 ? '#EF4444' : '#9CA3AF'; // Red if typed, Gray if empty
                }
            }
        }
        
        // New Password Modal - Password Field
        if (e.target.id === 'newPasswordField') {
            const password = e.target.value;
            const lengthCheck = document.getElementById('lengthCheckNew');
            const caseCheck = document.getElementById('caseCheckNew');
            
            if (lengthCheck && caseCheck) {
                // Check length
                if (password.length >= 8) {
                    lengthCheck.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                    lengthCheck.style.background = '#10B981';
                    lengthCheck.style.color = 'white';
                    lengthCheck.parentElement.querySelector('span:last-child').style.color = '#10B981';
                } else {
                    lengthCheck.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
                    lengthCheck.style.background = password.length > 0 ? '#EF4444' : '#E5E7EB';
                    lengthCheck.style.color = password.length > 0 ? 'white' : '#9CA3AF';
                    lengthCheck.parentElement.querySelector('span:last-child').style.color = password.length > 0 ? '#EF4444' : '#6B7280';
                }
                
                // Check uppercase, lowercase, and number
                if (/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
                    caseCheck.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                    caseCheck.style.background = '#10B981';
                    caseCheck.style.color = 'white';
                    caseCheck.parentElement.querySelector('span:last-child').style.color = '#10B981';
                } else {
                    caseCheck.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
                    caseCheck.style.background = password.length > 0 ? '#EF4444' : '#E5E7EB';
                    caseCheck.style.color = password.length > 0 ? 'white' : '#9CA3AF';
                    caseCheck.parentElement.querySelector('span:last-child').style.color = password.length > 0 ? '#EF4444' : '#6B7280';
                }
            }
        }
    });
});

// Export functions globally
window.showLoginModal = showLoginModal;
window.showRegisterModal = showRegisterModal;
window.showForgotPasswordModal = showForgotPasswordModal;
window.showNewPasswordModal = showNewPasswordModal;
window.showResetPasswordModal = showResetPasswordModal;
window.closeAuthModal = closeAuthModal;
window.switchToRegister = switchToRegister;
window.switchToLogin = switchToLogin;
window.switchToForgotPassword = switchToForgotPassword;
window.handleLogin = handleLogin;
window.handleRegister = handleRegister;
window.handleForgotPassword = handleForgotPassword;
window.handleNewPassword = handleNewPassword;
window.handleResetPassword = handleResetPassword;
window.togglePasswordVisibility = togglePasswordVisibility;
window.resendCode = resendCode;
window.resendVerificationCode = resendVerificationCode;

// Force reload check - v1.3
console.log('🔐 Auth Modal JS Loaded - Version 1.3 - 2 Panel Forgot Password with SVG Icons');
