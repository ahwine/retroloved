<?php
/**
 * Check Account Status
 * Endpoint untuk memeriksa apakah akun user masih aktif atau sudah diblokir
 * RetroLoved E-Commerce System
 */

session_start();
require_once '../config/database.php';

// Set header JSON untuk response
header('Content-Type: application/json');

// Validasi: Harus sudah login
if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Ambil input JSON dari request
$input = json_decode(file_get_contents('php://input'), true);

// Validasi request
if (!$input || !isset($input['action']) || $input['action'] !== 'check_status') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Cek status akun di database
$result = query("SELECT is_active FROM users WHERE user_id = '$user_id'");

if (mysqli_num_rows($result) == 0) {
    // User tidak ditemukan
    echo json_encode([
        'success' => false,
        'blocked' => true,
        'message' => 'Account not found'
    ]);
    exit();
}

$user = mysqli_fetch_assoc($result);

// Cek apakah akun diblokir
if (isset($user['is_active']) && $user['is_active'] == 0) {
    echo json_encode([
        'success' => true,
        'blocked' => true,
        'message' => 'Account is blocked'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'blocked' => false,
        'message' => 'Account is active'
    ]);
}
?>
