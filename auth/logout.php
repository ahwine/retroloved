<?php
/**
 * Proses Logout
 * Menghapus semua data session dan mengarahkan ke halaman utama
 * RetroLoved E-Commerce System
 */

session_start();

// Hapus semua data session pengguna
session_unset();
session_destroy();

// Redirect ke halaman utama
header('Location: ../index.php');
exit();
?>