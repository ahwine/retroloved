<?php
session_start();

// Set timezone ke Asia/Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

header('Content-Type: application/json');

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON response
ini_set('log_errors', 1);

// Check if email config exists
if (!file_exists(__DIR__ . '/config/email.php')) {
    echo json_encode(['success' => false, 'message' => 'Konfigurasi email tidak ditemukan']);
    exit;
}

require_once 'config/email.php';

// Check if SUPPORT_EMAIL is defined
if (!defined('SUPPORT_EMAIL')) {
    echo json_encode(['success' => false, 'message' => 'Email support tidak dikonfigurasi']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data (no need mysqli_real_escape_string since we're not using database)
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Nama harus diisi';
}

if (empty($email)) {
    $errors[] = 'Email harus diisi';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid';
}

if (empty($subject)) {
    $errors[] = 'Subjek harus diisi';
}

if (empty($message)) {
    $errors[] = 'Pesan harus diisi';
} elseif (strlen($message) < 10) {
    $errors[] = 'Pesan minimal 10 karakter';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Generate unique support ID for tracking (tanpa database)
$support_id = 'CS-' . date('Ymd') . '-' . substr(md5($email . time()), 0, 6);

// Kirim email ke admin
$email_subject = 'Contact Support Request #' . $support_id;

// Generate email content
$email_content = "
    <div class='info-box'>
        <h3 style='margin-top: 0; color: #D97706;'>Pesan Contact Support Baru</h3>
        <p><strong>Support ID:</strong> #$support_id</p>
    </div>
    
    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
        <tr style='background: #F3F4F6;'>
            <td style='padding: 12px; font-weight: 600; width: 30%; border: 1px solid #E5E7EB;'>Dari:</td>
            <td style='padding: 12px; border: 1px solid #E5E7EB;'>{$name}</td>
        </tr>
        <tr>
            <td style='padding: 12px; font-weight: 600; border: 1px solid #E5E7EB;'>Email:</td>
            <td style='padding: 12px; border: 1px solid #E5E7EB;'><a href='mailto:{$email}' style='color: #D97706;'>{$email}</a></td>
        </tr>
        <tr style='background: #F3F4F6;'>
            <td style='padding: 12px; font-weight: 600; border: 1px solid #E5E7EB;'>Subject:</td>
            <td style='padding: 12px; border: 1px solid #E5E7EB;'><strong>{$subject}</strong></td>
        </tr>
        <tr>
            <td style='padding: 12px; font-weight: 600; border: 1px solid #E5E7EB;'>Waktu:</td>
            <td style='padding: 12px; border: 1px solid #E5E7EB;'>" . date('d F Y, H:i:s') . "</td>
        </tr>
    </table>
    
    <div style='background: #F9FAFB; padding: 20px; border-radius: 8px; border: 1px solid #E5E7EB; margin: 20px 0;'>
        <h4 style='margin-top: 0; color: #1F2937;'>Pesan:</h4>
        <div style='line-height: 1.8; color: #4B5563;'>" . nl2br(htmlspecialchars($message)) . "</div>
    </div>
    
    <p style='color: #6B7280; font-size: 14px;'>
        Silakan balas langsung ke email customer untuk merespon pesan ini.
    </p>
";

$email_body = EmailHelper::getTemplate('Pesan Contact Support - RetroLoved', $email_content);

// Kirim email ke retroloved.ofc@gmail.com
try {
    error_log("Attempting to send contact support email to: " . SUPPORT_EMAIL);
    error_log("Support ID: $support_id");
    error_log("From: $name <$email>");
    error_log("Subject: $subject");
    
    $mail_sent = EmailHelper::send(SUPPORT_EMAIL, $email_subject, $email_body, $email);
    
    if ($mail_sent) {
        error_log("✅ Contact Support Email sent successfully to " . SUPPORT_EMAIL . " for support #$support_id");
        
        // Success response - email sent
        echo json_encode([
            'success' => true, 
            'message' => 'Pesan berhasil dikirim ke email admin! Tim kami akan segera membalas via email.',
            'support_id' => $support_id
        ]);
    } else {
        error_log("❌ EmailHelper::send returned false");
        throw new Exception('Email failed to send - EmailHelper returned false');
    }
} catch (Exception $e) {
    // Mail failed
    error_log("❌ Contact Support Email failed: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Gagal mengirim email. Silakan coba lagi atau hubungi kami langsung di ' . SUPPORT_EMAIL
    ]);
}
?>