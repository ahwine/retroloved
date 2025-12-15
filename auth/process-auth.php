<?php
/**
 * Proses Autentikasi - Login, Register, Reset Password
 * Menangani semua operasi autentikasi melalui AJAX
 * RetroLoved E-Commerce System
 */

session_start();
require_once '../config/database.php';

// Set header JSON untuk response
header('Content-Type: application/json');

// Ambil input JSON dari request
$input = json_decode(file_get_contents('php://input'), true);

// Validasi request
if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid']);
    exit();
}

$action = $input['action'];

// ===== PROSES LOGIN =====
if ($action === 'login') {
    // PERBAIKAN BUG: Hapus double trim() karena escape() sudah melakukan trim()
    // Support login dengan username ATAU email
    $username = escape($input['username']);
    $raw_password = $input['password'];
    $password = md5($raw_password); // CATATAN: Gunakan password_hash() di production
    
    error_log("Login Attempt - Username/Email: '$username', Raw Password Length: " . strlen($raw_password));
    error_log("Login - Input Password MD5: $password");
    
    // First check if user exists (untuk debugging dengan TRIM di query untuk handle whitespace)
    $check_user = query("SELECT user_id, username, email, password, LENGTH(password) as pwd_len 
                         FROM users 
                         WHERE TRIM(username) = '$username' OR TRIM(email) = '$username'");
    
    if (mysqli_num_rows($check_user) > 0) {
        $found_user = mysqli_fetch_assoc($check_user);
        error_log("User Found - ID: " . $found_user['user_id'] . ", Username: '" . $found_user['username'] . "', Email: '" . $found_user['email'] . "'");
        error_log("User Password in DB: " . $found_user['password'] . " (Length: " . $found_user['pwd_len'] . ")");
        error_log("Expected Password: $password (Length: 32)");
        error_log("Passwords Match: " . ($found_user['password'] === $password ? '✅ YES' : '❌ NO'));
        
        // Jika password tidak cocok, coba dengan berbagai kemungkinan
        if ($found_user['password'] !== $password) {
            error_log("❌ Password mismatch detected! Checking alternatives...");
            
            // Cek apakah password di DB memiliki whitespace
            $trimmed_db_pwd = trim($found_user['password']);
            if ($trimmed_db_pwd !== $found_user['password']) {
                error_log("⚠️ WARNING: Password in DB has whitespace!");
            }
            
            // Cek apakah ini MD5 atau format lain
            if (strlen($found_user['password']) != 32) {
                error_log("⚠️ WARNING: Password in DB is not MD5 format (length != 32)");
            }
        }
    } else {
        error_log("❌ User Not Found - No user with username/email: $username");
    }
    
    // Support login dengan username ATAU email - gunakan TRIM untuk handle whitespace
    $query = "SELECT * FROM users 
              WHERE (TRIM(username) = '$username' OR TRIM(email) = '$username') 
              AND password = '$password'";
    error_log("Login Query: $query");
    
    $result = query($query);
    
    error_log("Login Result - Rows found: " . mysqli_num_rows($result));
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // CEK APAKAH USER DIBLOKIR (is_active = 0)
        if (isset($user['is_active']) && $user['is_active'] == 0) {
            error_log("❌ Login Blocked - User ID: " . $user['user_id'] . " is blocked");
            echo json_encode([
                'success' => false,
                'message' => 'Akun Anda telah diblokir oleh admin. Silakan hubungi customer support untuk informasi lebih lanjut.'
            ]);
            exit();
        }
        
        error_log("✅ Login Success - User ID: " . $user['user_id']);
        
        // Set session untuk user yang berhasil login
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        // Tentukan redirect URL berdasarkan role
        $redirect_url = $user['role'] == 'admin' ? 'admin/dashboard.php' : '';
        
        echo json_encode([
            'success' => true,
            'message' => 'Login berhasil! Selamat datang ' . $user['full_name'],
            'role' => $user['role'],
            'email' => $user['email'],
            'redirect_url' => $redirect_url
        ]);
    } else {
        error_log("❌ Login Failed - Password mismatch or user not found");
        echo json_encode([
            'success' => false,
            'message' => 'Username/Email atau password salah!'
        ]);
    }
}

// ===== PROSES REGISTER (STEP 1: SEND OTP) =====
elseif ($action === 'register') {
    // PERBAIKAN BUG: Hapus double trim() karena escape() sudah melakukan trim()
    $username = escape($input['username']);
    $raw_password = $input['password'];
    $full_name = escape($input['full_name']);
    $email = escape($input['email']);
    
    error_log("Register Step 1 - Username: '$username', Email: '$email'");
    
    // Validasi format email
    if (!validate_email($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Format email tidak valid!'
        ]);
        exit();
    }
    
    // Validasi panjang password minimal 8 karakter
    if (strlen($raw_password) < 8) {
        echo json_encode([
            'success' => false,
            'message' => 'Password minimal 8 karakter!'
        ]);
        exit();
    }
    
    // Validasi kompleksitas password (harus ada huruf besar, kecil, dan angka)
    if (!preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $raw_password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Password harus mengandung huruf besar, huruf kecil, dan angka!'
        ]);
        exit();
    }
    
    // Cek apakah username sudah digunakan
    if (mysqli_num_rows(query("SELECT * FROM users WHERE username = '$username'")) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username sudah digunakan!'
        ]);
        exit();
    }
    
    // Cek apakah email sudah terdaftar
    $existing_email = query("SELECT user_id, email, is_active FROM users WHERE email = '$email'");
    if (mysqli_num_rows($existing_email) > 0) {
        $existing_user = mysqli_fetch_assoc($existing_email);
        
        // Cek apakah email tersebut diblokir
        if (isset($existing_user['is_active']) && $existing_user['is_active'] == 0) {
            error_log("❌ Register Failed - Email blocked: $email");
            echo json_encode([
                'success' => false,
                'message' => 'Email ini tidak dapat digunakan untuk registrasi. Akun dengan email ini telah diblokir. Silakan hubungi customer support untuk informasi lebih lanjut.'
            ]);
            exit();
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Email sudah terdaftar!'
        ]);
        exit();
    }
    
    // Hash password (CATATAN: Gunakan password_hash() di production)
    $password = md5($raw_password);
    
    // Generate kode 6 digit untuk verifikasi email
    $verification_code = sprintf("%06d", mt_rand(1, 999999));
    
    // Hapus verifikasi lama untuk email ini
    query("DELETE FROM email_verifications WHERE email = '$email'");
    
    // Simpan data registrasi sementara (berlaku 10 menit)
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $insert = query("INSERT INTO email_verifications (email, full_name, username, password, verification_code, expires_at) 
                     VALUES ('$email', '$full_name', '$username', '$password', '$verification_code', '$expires_at')");
    
    if ($insert) {
        // Kirim kode verifikasi via email
        require_once '../config/email.php';
        
        $subject = 'Verifikasi Email - RetroLoved';
        $email_content = "
            <h2>Selamat Datang, $full_name!</h2>
            <p>Terima kasih telah mendaftar di <strong>RetroLoved</strong>.</p>
            <p>Untuk melanjutkan registrasi, silakan masukkan kode verifikasi berikut:</p>
            <div class='info-box' style='background: #FEF3C7; border-left: 4px solid #D97706; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center;'>
                <h1 style='font-size: 48px; margin: 0; color: #92400E; letter-spacing: 8px; font-family: monospace;'>$verification_code</h1>
            </div>
            <p><strong>Kode ini berlaku selama 10 menit.</strong></p>
            <p>Jika Anda tidak melakukan registrasi, abaikan email ini.</p>
            <hr style='margin: 30px 0; border: none; border-top: 1px solid #E5E7EB;'>
            <p style='color: #6B7280; font-size: 14px;'>Jangan bagikan kode ini kepada siapapun untuk keamanan akun Anda.</p>
        ";
        
        $body = EmailHelper::getTemplate('Verifikasi Email Anda', $email_content);
        
        try {
            $email_sent = EmailHelper::send($email, $subject, $body);
            
            if ($email_sent) {
                error_log("Register - Verification email sent to: $email - Code: $verification_code");
                echo json_encode([
                    'success' => true,
                    'message' => 'Kode verifikasi telah dikirim ke email Anda!',
                    'email' => $email,
                    'email_sent' => true
                ]);
            } else {
                // Email gagal terkirim tapi kode sudah tersimpan (development mode)
                error_log("Register - Email failed but code saved: $email - Code: $verification_code");
                echo json_encode([
                    'success' => true,
                    'message' => 'Kode verifikasi berhasil dibuat. Email gagal terkirim.',
                    'email' => $email,
                    'dev_code' => $verification_code, // HAPUS DI PRODUCTION!
                    'email_sent' => false
                ]);
            }
        } catch (Exception $e) {
            // Error saat kirim email, tapi kode sudah tersimpan
            error_log("Register Email Error: " . $e->getMessage() . " - Email: $email - Code: $verification_code");
            echo json_encode([
                'success' => true,
                'message' => 'Kode verifikasi berhasil dibuat. Email error.',
                'email' => $email,
                'dev_code' => $verification_code, // HAPUS DI PRODUCTION!
                'email_sent' => false,
                'error' => $e->getMessage()
            ]);
        }
    } else {
        error_log("Register Failed - Cannot save verification: $email");
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan. Silakan coba lagi.'
        ]);
    }
}

// ===== PROSES VERIFIKASI EMAIL REGISTER (STEP 2) =====
elseif ($action === 'verify_register_otp') {
    $email = escape($input['email']);
    $code = escape($input['code']);
    
    error_log("========================================");
    error_log("🔍 VERIFY REGISTER OTP");
    error_log("Email: $email");
    error_log("Code: $code");
    error_log("========================================");
    
    // Cek data yang ada di database untuk email ini
    $check_query = "SELECT email, verification_code, expires_at, 
                    TIMESTAMPDIFF(MINUTE, NOW(), expires_at) as minutes_left 
                    FROM email_verifications 
                    WHERE email = '$email'";
    error_log("Check Query: $check_query");
    
    $check_result = query($check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $data = mysqli_fetch_assoc($check_result);
        error_log("✅ Email found in database:");
        error_log("  - Email: " . $data['email']);
        error_log("  - Code in DB: " . $data['verification_code']);
        error_log("  - Code from User: $code");
        error_log("  - Expires at: " . $data['expires_at']);
        error_log("  - Minutes left: " . $data['minutes_left']);
        error_log("  - Code Match: " . ($data['verification_code'] === $code ? '✅ YES' : '❌ NO'));
    } else {
        error_log("❌ No verification data found for email: $email");
    }
    
    // Cek apakah kode verifikasi valid dan belum expired
    $result = query("SELECT * FROM email_verifications 
                     WHERE email = '$email' 
                     AND verification_code = '$code' 
                     AND expires_at > NOW()");
    
    error_log("Verification Query Result: " . mysqli_num_rows($result) . " rows");
    
    if (mysqli_num_rows($result) == 0) {
        error_log("❌ Verify Register OTP Failed - Invalid or expired code");
        error_log("========================================");
        echo json_encode([
            'success' => false,
            'message' => 'Kode verifikasi salah atau sudah kadaluarsa!'
        ]);
        exit();
    }
    
    error_log("✅ OTP verified successfully!");
    error_log("========================================");
    
    $verification = mysqli_fetch_assoc($result);
    
    // Cek lagi apakah username atau email sudah digunakan (double check)
    if (mysqli_num_rows(query("SELECT * FROM users WHERE username = '{$verification['username']}'")) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username sudah digunakan! Silakan daftar ulang dengan username lain.'
        ]);
        exit();
    }
    
    if (mysqli_num_rows(query("SELECT * FROM users WHERE email = '{$verification['email']}'")) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email sudah terdaftar! Silakan login.'
        ]);
        exit();
    }
    
    // Insert user baru ke database dengan email_verified = 1
    $insert_query = "INSERT INTO users (username, password, full_name, email, role, email_verified, verified_at) 
                     VALUES ('{$verification['username']}', '{$verification['password']}', '{$verification['full_name']}', '{$verification['email']}', 'customer', 1, NOW())";
    
    if (query($insert_query)) {
        // Ambil user_id yang baru saja di-insert
        $new_user_id = mysqli_insert_id($GLOBALS['conn']);
        
        // Hapus data verifikasi setelah berhasil
        query("DELETE FROM email_verifications WHERE email = '$email'");
        
        // AUTO-LOGIN: Set session untuk user yang baru register
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['username'] = $verification['username'];
        $_SESSION['full_name'] = $verification['full_name'];
        $_SESSION['email'] = $verification['email'];
        $_SESSION['role'] = 'customer';
        
        error_log("Register Success - Username: '{$verification['username']}', Email: '$email', Auto-login: YES");
        echo json_encode([
            'success' => true,
            'message' => 'Registrasi berhasil! Anda akan otomatis login...'
        ]);
    } else {
        error_log("Register Failed - Cannot insert user: $email");
        echo json_encode([
            'success' => false,
            'message' => 'Registrasi gagal! Silakan coba lagi.'
        ]);
    }
}

// ===== PROSES KIRIM ULANG KODE REGISTER =====
elseif ($action === 'resend_register_otp') {
    $email = escape($input['email']);
    
    error_log("Resend Register OTP - Email: $email");
    
    // Ambil data verifikasi yang ada
    $result = query("SELECT * FROM email_verifications WHERE email = '$email'");
    
    if (mysqli_num_rows($result) == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Data verifikasi tidak ditemukan! Silakan daftar ulang.'
        ]);
        exit();
    }
    
    $verification = mysqli_fetch_assoc($result);
    
    // Generate kode 6 digit baru
    $verification_code = sprintf("%06d", mt_rand(1, 999999));
    
    // Update kode verifikasi dan perpanjang waktu expired (10 menit)
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $update = query("UPDATE email_verifications 
                     SET verification_code = '$verification_code', expires_at = '$expires_at', created_at = NOW() 
                     WHERE email = '$email'");
    
    if ($update) {
        // Kirim kode baru via email
        require_once '../config/email.php';
        
        $subject = 'Kode Verifikasi Baru - RetroLoved';
        $email_content = "
            <h2>Kode Verifikasi Baru</h2>
            <p>Halo, <strong>{$verification['full_name']}</strong>!</p>
            <p>Berikut adalah kode verifikasi baru Anda:</p>
            <div class='info-box' style='background: #FEF3C7; border-left: 4px solid #D97706; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center;'>
                <h1 style='font-size: 48px; margin: 0; color: #92400E; letter-spacing: 8px; font-family: monospace;'>$verification_code</h1>
            </div>
            <p><strong>Kode ini berlaku selama 10 menit.</strong></p>
            <hr style='margin: 30px 0; border: none; border-top: 1px solid #E5E7EB;'>
            <p style='color: #6B7280; font-size: 14px;'>Jangan bagikan kode ini kepada siapapun untuk keamanan akun Anda.</p>
        ";
        
        $body = EmailHelper::getTemplate('Kode Verifikasi Baru', $email_content);
        
        try {
            $email_sent = EmailHelper::send($email, $subject, $body);
            
            if ($email_sent) {
                error_log("Resend Register OTP - Success: $email - Code: $verification_code");
                echo json_encode([
                    'success' => true,
                    'message' => 'Kode verifikasi baru telah dikirim ke email Anda!',
                    'email_sent' => true
                ]);
            } else {
                error_log("Resend Register OTP - Email failed: $email - Code: $verification_code");
                echo json_encode([
                    'success' => true,
                    'message' => 'Kode verifikasi baru berhasil dibuat.',
                    'dev_code' => $verification_code, // HAPUS DI PRODUCTION!
                    'email_sent' => false
                ]);
            }
        } catch (Exception $e) {
            error_log("Resend Register OTP Email Error: " . $e->getMessage());
            echo json_encode([
                'success' => true,
                'message' => 'Kode verifikasi baru berhasil dibuat.',
                'dev_code' => $verification_code, // HAPUS DI PRODUCTION!
                'email_sent' => false
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal membuat kode baru. Silakan coba lagi.'
        ]);
    }
}

// ===== PROSES LUPA PASSWORD =====
elseif ($action === 'forgot_password') {
    $email = escape($input['email']);
    
    // Validasi format email terlebih dahulu
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Format email tidak valid! Pastikan email Anda benar.'
        ]);
        exit();
    }
    
    // Log attempt
    error_log("Forgot Password Attempt - Email: " . $email);
    
    // Cek apakah email terdaftar di database dan status aktifnya
    $result = query("SELECT user_id, email, full_name, is_active FROM users WHERE TRIM(email) = '$email'");
    
    if (mysqli_num_rows($result) == 0) {
        error_log("❌ Forgot Password Failed - Email not registered: " . $email);
        echo json_encode([
            'success' => false,
            'message' => 'Email tidak terdaftar! Email "' . $email . '" tidak ditemukan di sistem kami. Silakan periksa kembali atau daftar terlebih dahulu.',
            'error_type' => 'email_not_found'
        ]);
        exit();
    }
    
    error_log("Forgot Password - Email found in database: " . $email);
    
    $user = mysqli_fetch_assoc($result);
    
    // CEK APAKAH USER DIBLOKIR (is_active = 0)
    if (isset($user['is_active']) && $user['is_active'] == 0) {
        error_log("❌ Forgot Password Blocked - User email blocked: " . $email);
        echo json_encode([
            'success' => false,
            'message' => 'Akun Anda telah diblokir oleh admin. Anda tidak dapat melakukan reset password. Silakan hubungi customer support untuk informasi lebih lanjut.',
            'error_type' => 'account_blocked'
        ]);
        exit();
    }
    
    // Generate kode 6 digit untuk reset password
    $reset_code = sprintf("%06d", mt_rand(1, 999999));
    
    // Hapus kode reset lama untuk user ini
    query("DELETE FROM password_resets WHERE user_id = '{$user['user_id']}'");
    
    // Simpan kode reset ke database (berlaku 5 menit)
    $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $insert = query("INSERT INTO password_resets (user_id, email, reset_code, expires_at) 
                     VALUES ('{$user['user_id']}', '$email', '$reset_code', '$expires_at')");
    
    if ($insert) {
        // Kirim kode reset via email
        require_once '../config/email.php';
        
        $email_subject = 'Reset Password - Kode Verifikasi';
        
        $email_content = "
            <div class='info-box'>
                <h3 style='margin-top: 0; color: #D97706;'>Reset Password Request</h3>
                <p>Hi <strong>{$user['full_name']}</strong>,</p>
                <p>Kami menerima permintaan untuk mereset password akun Anda.</p>
            </div>
            
            <div style='background: #FEF3C7; border: 2px solid #F59E0B; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0;'>
                <p style='color: #78350F; font-size: 14px; margin: 0 0 10px 0; font-weight: 600;'>KODE VERIFIKASI ANDA:</p>
                <div style='font-size: 36px; font-weight: 800; color: #D97706; letter-spacing: 8px; font-family: monospace;'>
                    $reset_code
                </div>
                <p style='color: #92400E; font-size: 12px; margin: 10px 0 0 0;'>Berlaku selama 5 menit</p>
            </div>
            
            <div style='background: #F3F4F6; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                <p style='margin: 0; font-size: 14px; color: #4B5563; display: flex; flex-direction: column; gap: 8px;'>
                    <span style='display: flex; align-items: center; gap: 6px;'>
                        <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><circle cx='12' cy='12' r='10'></circle><polyline points='12 6 12 12 16 14'></polyline></svg>
                        <strong>Kode akan kadaluarsa pada:</strong>
                    </span>
                    <span style='color: #1F2937; font-weight: 600;'>$expires_at</span>
                </p>
            </div>
            
            <p style='color: #6B7280; font-size: 14px; line-height: 1.6;'>
                Masukkan kode ini di halaman reset password untuk melanjutkan proses reset password Anda.
            </p>
            
            <div style='background: #FEE2E2; border-left: 4px solid #EF4444; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                <p style='margin: 0; color: #991B1B; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;'>
                    <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z'></path><line x1='12' y1='9' x2='12' y2='13'></line><line x1='12' y1='17' x2='12.01' y2='17'></line></svg>
                    PERINGATAN KEAMANAN
                </p>
                <p style='margin: 10px 0 0 0; color: #7F1D1D; font-size: 12px; line-height: 1.6;'>
                    Jika Anda tidak meminta reset password, abaikan email ini atau hubungi kami segera jika Anda khawatir tentang keamanan akun Anda.
                </p>
            </div>
        ";
        
        $email_body = EmailHelper::getTemplate('Reset Password Request', $email_content);
        
        try {
            $mail_sent = EmailHelper::send($email, $email_subject, $email_body);
            
            if ($mail_sent) {
                // Email berhasil dikirim
                error_log("Forgot Password Email - Successfully sent to: " . $email);
                echo json_encode([
                    'success' => true,
                    'message' => 'Kode verifikasi telah dikirim ke email Anda! Silakan cek inbox atau folder spam.',
                    'user_id' => $user['user_id'],
                    'email_sent' => true
                ]);
            } else {
                // Email gagal, tapi kode tetap di database
                // Untuk development, tampilkan kode di response
                error_log("Forgot Password Email - Failed to send to: " . $email . " - Code: " . $reset_code);
                echo json_encode([
                    'success' => true,
                    'message' => 'Kode verifikasi berhasil dibuat. Email gagal terkirim. (Development Mode - Kode: ' . $reset_code . ')',
                    'user_id' => $user['user_id'],
                    'dev_code' => $reset_code, // HAPUS DI PRODUCTION!
                    'email_sent' => false
                ]);
            }
        } catch (Exception $e) {
            // Error saat kirim email, tapi kode sudah tersimpan
            error_log("Forgot Password Email Error: " . $e->getMessage() . " - Email: " . $email . " - Code: " . $reset_code);
            echo json_encode([
                'success' => true,
                'message' => 'Kode verifikasi berhasil dibuat. Email error: ' . $e->getMessage() . ' (Development Mode - Kode: ' . $reset_code . ')',
                'user_id' => $user['user_id'],
                'dev_code' => $reset_code, // HAPUS DI PRODUCTION!
                'email_sent' => false,
                'error' => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan. Silakan coba lagi.'
        ]);
    }
}

// ===== PROSES VERIFIKASI OTP =====
elseif ($action === 'verify_otp') {
    $email = escape($input['email']);
    $user_id = escape($input['user_id']);
    $code = escape($input['code']);
    
    error_log("Verify OTP - Email: $email, User ID: $user_id, Code: $code");
    
    // Cek apakah kode verifikasi valid dan belum expired
    $result = query("SELECT * FROM password_resets 
                     WHERE user_id = '$user_id' 
                     AND email = '$email' 
                     AND reset_code = '$code' 
                     AND expires_at > NOW()");
    
    if (mysqli_num_rows($result) == 0) {
        error_log("Verify OTP Failed - Invalid or expired code");
        echo json_encode([
            'success' => false,
            'message' => 'Kode verifikasi salah atau sudah kadaluarsa!'
        ]);
        exit();
    }
    
    error_log("Verify OTP Success - Code valid");
    // OTP valid
    echo json_encode([
        'success' => true,
        'message' => 'Kode verifikasi benar! Silakan buat password baru.',
        'user_id' => $user_id,
        'email' => $email
    ]);
}

// ===== PROSES RESET PASSWORD =====
elseif ($action === 'reset_password') {
    $email = escape($input['email']);
    $user_id = escape($input['user_id']);
    $new_password = $input['password'];
    
    error_log("========================================");
    error_log("🔐 RESET PASSWORD STARTED");
    error_log("Email: $email");
    error_log("User ID: $user_id");
    error_log("Raw Password Length: " . strlen($new_password));
    error_log("========================================");
    
    // Validasi panjang password minimal 8 karakter
    if (strlen($new_password) < 8) {
        error_log("❌ Reset Password Failed - Password too short");
        echo json_encode([
            'success' => false,
            'message' => 'Password minimal 8 karakter!'
        ]);
        exit();
    }
    
    // Validasi kompleksitas password
    if (!preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $new_password)) {
        error_log("❌ Reset Password Failed - Password complexity not met");
        echo json_encode([
            'success' => false,
            'message' => 'Password harus mengandung huruf besar, huruf kecil, dan angka!'
        ]);
        exit();
    }
    
    // Cek password lama sebelum update
    $old_pwd_query = query("SELECT password, username, email FROM users WHERE user_id = '$user_id'");
    if (mysqli_num_rows($old_pwd_query) > 0) {
        $old_data = mysqli_fetch_assoc($old_pwd_query);
        error_log("📋 Before Update:");
        error_log("   - Username: " . $old_data['username']);
        error_log("   - Email: " . $old_data['email']);
        error_log("   - Old Password Hash: " . $old_data['password']);
    } else {
        error_log("❌ User ID $user_id not found in database!");
        echo json_encode([
            'success' => false,
            'message' => 'User tidak ditemukan!'
        ]);
        exit();
    }
    
    // Update password dengan password baru (tidak perlu cek OTP lagi karena sudah diverifikasi)
    $hashed_password = md5($new_password); // CATATAN: Gunakan password_hash() di production
    
    error_log("🔑 New Password Hash (MD5): $hashed_password");
    
    $update_query = "UPDATE users SET password = '$hashed_password' WHERE user_id = '$user_id'";
    error_log("📝 Update Query: $update_query");
    
    $update = query($update_query);
    
    if ($update) {
        error_log("✅ UPDATE query executed successfully");
        
        // Verify the update by checking the password in database
        $verify = query("SELECT password, username, email FROM users WHERE user_id = '$user_id'");
        if (mysqli_num_rows($verify) > 0) {
            $user_data = mysqli_fetch_assoc($verify);
            error_log("📋 After Update:");
            error_log("   - Username: " . $user_data['username']);
            error_log("   - Email: " . $user_data['email']);
            error_log("   - New Password Hash: " . $user_data['password']);
            error_log("   - Hash Match: " . ($user_data['password'] === $hashed_password ? '✅ YES' : '❌ NO'));
            
            if ($user_data['password'] !== $hashed_password) {
                error_log("⚠️ CRITICAL WARNING: Password in DB doesn't match expected hash!");
                error_log("   Expected: $hashed_password");
                error_log("   Got: " . $user_data['password']);
            } else {
                error_log("✅ Password successfully updated and verified in database!");
            }
        }
        
        // Hapus kode reset yang sudah digunakan
        query("DELETE FROM password_resets WHERE user_id = '$user_id'");
        error_log("🗑️ Deleted used reset code from password_resets table");
        
        error_log("========================================");
        error_log("✅ RESET PASSWORD COMPLETED SUCCESSFULLY");
        error_log("========================================");
        
        echo json_encode([
            'success' => true,
            'message' => 'Password berhasil diubah! Silakan login dengan password baru Anda.'
        ]);
    } else {
        error_log("❌ UPDATE query failed! MySQL Error: " . mysqli_error($GLOBALS['conn']));
        error_log("========================================");
        echo json_encode([
            'success' => false,
            'message' => 'Gagal mengubah password. Silakan coba lagi.'
        ]);
    }
}

// ===== PROSES KIRIM ULANG KODE =====
elseif ($action === 'resend_code') {
    $email = escape($input['email']);
    $user_id = escape($input['user_id']);
    
    // Get user info
    $result = query("SELECT full_name FROM users WHERE user_id = '$user_id'");
    if (mysqli_num_rows($result) == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'User tidak ditemukan!'
        ]);
        exit();
    }
    
    $user = mysqli_fetch_assoc($result);
    
    // Generate kode 6 digit baru
    $reset_code = sprintf("%06d", mt_rand(1, 999999));
    
    // Hapus kode reset lama untuk user ini
    query("DELETE FROM password_resets WHERE user_id = '$user_id'");
    
    // Simpan kode reset baru ke database (berlaku 5 menit)
    $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $insert = query("INSERT INTO password_resets (user_id, email, reset_code, expires_at) 
                     VALUES ('$user_id', '$email', '$reset_code', '$expires_at')");
    
    if ($insert) {
        // Kirim kode reset via email
        require_once '../config/email.php';
        
        $email_subject = 'Reset Password - Kode Verifikasi Baru';
        
        $email_content = "
            <div class='info-box'>
                <h3 style='margin-top: 0; color: #D97706;'>Resend Verification Code</h3>
                <p>Hi <strong>{$user['full_name']}</strong>,</p>
                <p>Anda telah meminta kode verifikasi baru untuk reset password.</p>
            </div>
            
            <div style='background: #FEF3C7; border: 2px solid #F59E0B; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0;'>
                <p style='color: #78350F; font-size: 14px; margin: 0 0 10px 0; font-weight: 600;'>KODE VERIFIKASI BARU ANDA:</p>
                <div style='font-size: 36px; font-weight: 800; color: #D97706; letter-spacing: 8px; font-family: monospace;'>
                    $reset_code
                </div>
                <p style='color: #92400E; font-size: 12px; margin: 10px 0 0 0;'>Berlaku selama 5 menit</p>
            </div>
            
            <div style='background: #F3F4F6; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                <p style='margin: 0; font-size: 14px; color: #4B5563; display: flex; flex-direction: column; gap: 8px;'>
                    <span style='display: flex; align-items: center; gap: 6px;'>
                        <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><circle cx='12' cy='12' r='10'></circle><polyline points='12 6 12 12 16 14'></polyline></svg>
                        <strong>Kode akan kadaluarsa pada:</strong>
                    </span>
                    <span style='color: #1F2937; font-weight: 600;'>$expires_at</span>
                </p>
            </div>
            
            <p style='color: #6B7280; font-size: 14px; line-height: 1.6;'>
                Masukkan kode ini di halaman reset password untuk melanjutkan proses reset password Anda.
            </p>
            
            <div style='background: #FEE2E2; border-left: 4px solid #EF4444; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                <p style='margin: 0; color: #991B1B; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;'>
                    <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z'></path><line x1='12' y1='9' x2='12' y2='13'></line><line x1='12' y1='17' x2='12.01' y2='17'></line></svg>
                    PERINGATAN KEAMANAN
                </p>
                <p style='margin: 10px 0 0 0; color: #7F1D1D; font-size: 12px; line-height: 1.6;'>
                    Jika Anda tidak meminta reset password, abaikan email ini atau hubungi kami segera jika Anda khawatir tentang keamanan akun Anda.
                </p>
            </div>
        ";
        
        $email_body = EmailHelper::getTemplate('Reset Password - Kode Verifikasi Baru', $email_content);
        
        try {
            $mail_sent = EmailHelper::send($email, $email_subject, $email_body);
            
            if ($mail_sent) {
                error_log("Resend Code Email - Successfully sent to: " . $email);
                echo json_encode([
                    'success' => true,
                    'message' => 'Kode verifikasi baru telah dikirim ke email Anda!',
                    'email_sent' => true
                ]);
            } else {
                // Email gagal, tapi kode tetap di database (untuk development)
                error_log("Resend Code Email - Failed to send to: " . $email . " - Code: " . $reset_code);
                echo json_encode([
                    'success' => true,
                    'message' => 'Kode verifikasi baru berhasil dibuat. Email gagal terkirim. (Development Mode - Kode: ' . $reset_code . ')',
                    'dev_code' => $reset_code, // HAPUS DI PRODUCTION!
                    'email_sent' => false
                ]);
            }
        } catch (Exception $e) {
            error_log("Resend Code Email Error: " . $e->getMessage() . " - Email: " . $email . " - Code: " . $reset_code);
            echo json_encode([
                'success' => true,
                'message' => 'Kode verifikasi baru berhasil dibuat. Email error: ' . $e->getMessage() . ' (Development Mode - Kode: ' . $reset_code . ')',
                'dev_code' => $reset_code, // HAPUS DI PRODUCTION!
                'email_sent' => false,
                'error' => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan. Silakan coba lagi.'
        ]);
    }
}

// ===== ACTION TIDAK VALID =====
else {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
}
?>
