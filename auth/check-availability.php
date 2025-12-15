<?php
/**
 * Check Username/Email Availability
 * Real-time validation untuk registrasi
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

// ===== CEK USERNAME AVAILABILITY =====
if ($action === 'check_username') {
    $username = escape($input['username']);
    
    // Cek apakah username sudah digunakan dan status aktifnya
    $result = query("SELECT user_id, username, is_active FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Cek apakah username tersebut diblokir
        if (isset($user['is_active']) && $user['is_active'] == 0) {
            echo json_encode([
                'available' => false,
                'message' => 'Username ini tidak dapat digunakan (akun diblokir)',
                'blocked' => true
            ]);
        } else {
            echo json_encode([
                'available' => false,
                'message' => 'Username sudah digunakan'
            ]);
        }
    } else {
        echo json_encode([
            'available' => true,
            'message' => 'Username tersedia'
        ]);
    }
}

// ===== CEK EMAIL AVAILABILITY =====
elseif ($action === 'check_email') {
    $email = escape($input['email']);
    
    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'available' => false,
            'message' => 'Format email tidak valid'
        ]);
        exit();
    }
    
    // Cek apakah email sudah digunakan
    $result = query("SELECT user_id, email, is_active FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Cek apakah email tersebut diblokir
        if (isset($user['is_active']) && $user['is_active'] == 0) {
            echo json_encode([
                'available' => false,
                'message' => 'Email ini tidak dapat digunakan (akun diblokir)',
                'blocked' => true
            ]);
        } else {
            echo json_encode([
                'available' => false,
                'message' => 'Email sudah terdaftar'
            ]);
        }
    } else {
        echo json_encode([
            'available' => true,
            'message' => 'Email tersedia'
        ]);
    }
}

// ===== ACTION TIDAK VALID =====
else {
    echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
}
?>
