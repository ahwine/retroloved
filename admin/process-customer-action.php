<?php
/**
 * Process Customer Actions - Block/Unblock
 * Handle admin actions for customer management
 * RetroLoved E-Commerce System
 */

session_start();
require_once '../config/database.php';

// Set header JSON untuk response
header('Content-Type: application/json');

// Validasi: Harus login sebagai admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Ambil input JSON dari request
$input = json_decode(file_get_contents('php://input'), true);

// Validasi request
if (!$input || !isset($input['action']) || !isset($input['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid']);
    exit();
}

$action = $input['action'];
$user_id = escape($input['user_id']);
$admin_id = $_SESSION['user_id'];

// Cek apakah user yang akan diblokir adalah customer
$user_check = query("SELECT user_id, username, email, full_name, role, is_active FROM users WHERE user_id = '$user_id'");

if (mysqli_num_rows($user_check) == 0) {
    echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
    exit();
}

$user = mysqli_fetch_assoc($user_check);

// Tidak boleh blokir admin
if ($user['role'] == 'admin') {
    echo json_encode(['success' => false, 'message' => 'Tidak dapat memblokir akun admin']);
    exit();
}

// Tidak boleh blokir diri sendiri
if ($user_id == $admin_id) {
    echo json_encode(['success' => false, 'message' => 'Tidak dapat memblokir akun Anda sendiri']);
    exit();
}

// ===== PROSES BLOCK USER =====
if ($action === 'block') {
    // Set is_active = 0 (blocked)
    $update = query("UPDATE users SET is_active = 0 WHERE user_id = '$user_id'");
    
    if ($update) {
        // Log activity
        error_log("Admin #{$admin_id} blocked user #{$user_id} ({$user['username']})");
        
        echo json_encode([
            'success' => true,
            'message' => 'Customer "' . $user['full_name'] . '" berhasil diblokir',
            'new_status' => 'blocked'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal memblokir customer. Silakan coba lagi.'
        ]);
    }
}

// ===== PROSES UNBLOCK USER =====
elseif ($action === 'unblock') {
    // Set is_active = 1 (active)
    $update = query("UPDATE users SET is_active = 1 WHERE user_id = '$user_id'");
    
    if ($update) {
        // Log activity
        error_log("Admin #{$admin_id} unblocked user #{$user_id} ({$user['username']})");
        
        echo json_encode([
            'success' => true,
            'message' => 'Customer "' . $user['full_name'] . '" berhasil diunblock',
            'new_status' => 'active'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal unblock customer. Silakan coba lagi.'
        ]);
    }
}

// ===== ACTION TIDAK VALID =====
else {
    echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
}
?>
