<?php
/**
 * Halaman Detail Pesanan - Admin Panel
 * Menampilkan detail pesanan lengkap, update status, tracking, dan riwayat
 * RetroLoved E-Commerce System
 */

// Mulai session
session_start();

// Validasi: Hanya admin yang bisa akses halaman ini
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

// Include koneksi database
require_once '../config/database.php';
require_once '../config/shipping.php';

// Ambil ID pesanan dari parameter URL
$order_id = isset($_GET['id']) ? escape($_GET['id']) : null;

// Jika tidak ada ID pesanan, redirect ke halaman orders
if(!$order_id) {
    header('Location: orders.php');
    exit();
}

// AJAX Check - Untuk auto-refresh status
if(isset($_GET['ajax_check']) && $_GET['ajax_check'] == '1') {
    header('Content-Type: application/json');
    $check_order = mysqli_fetch_assoc(query("SELECT status FROM orders WHERE order_id = '$order_id'"));
    echo json_encode(['status' => trim($check_order['status'])]);
    exit();
}

// Ambil detail pesanan dengan informasi customer
$order = mysqli_fetch_assoc(query("SELECT o.*, u.full_name, u.email, u.username 
                                   FROM orders o 
                                   JOIN users u ON o.user_id = u.user_id 
                                   WHERE o.order_id = '$order_id'"));

// Jika pesanan tidak ditemukan, redirect ke halaman orders
if(!$order) {
    header('Location: orders.php');
    exit();
}

// Ambil item-item dalam pesanan ini dengan informasi produk
$order_items = query("SELECT oi.*, p.product_name, p.image_url 
                      FROM order_items oi 
                      JOIN products p ON oi.product_id = p.product_id 
                      WHERE oi.order_id = '$order_id'");

// Ambil riwayat perubahan status pesanan
$order_history = get_order_history($order_id);

// Inisialisasi variabel untuk pesan
$success = '';
$error = '';

// ===== PROSES UPDATE STATUS PESANAN =====
if(isset($_POST['update_status'])) {
    // PROTEKSI: Cek apakah order baru saja di-confirm oleh customer (dalam 30 detik terakhir)
    $check_recent_complete = mysqli_fetch_assoc(query("SELECT created_at FROM order_history WHERE order_id = '$order_id' AND status = 'Completed' ORDER BY created_at DESC LIMIT 1"));
    
    if($check_recent_complete) {
        $time_diff = time() - strtotime($check_recent_complete['created_at']);
        if($time_diff < 30) {
            // Customer baru saja confirm dalam 30 detik terakhir, jangan override!
            error_log("ADMIN UPDATE BLOCKED: Order #$order_id was just confirmed by customer $time_diff seconds ago");
            $error = "⚠️ Order baru saja dikonfirmasi oleh customer! Status tidak dapat diubah.";
            
            // Refresh untuk ensure latest data
            $order = mysqli_fetch_assoc(query("SELECT o.*, u.full_name, u.email, u.username 
                                               FROM orders o 
                                               JOIN users u ON o.user_id = u.user_id 
                                               WHERE o.order_id = '$order_id'"));
        } else {
            // Proceed dengan update normal
            $new_status = escape($_POST['status']);
            $tracking_number = isset($_POST['tracking_number']) ? escape($_POST['tracking_number']) : null;
            $admin_notes = isset($_POST['admin_notes']) ? escape($_POST['admin_notes']) : null;
            
            // Update status menggunakan fungsi helper (otomatis kirim notifikasi ke customer)
            if(update_order_status($order_id, $new_status, $tracking_number, $admin_notes, $_SESSION['user_id'])) {
                $success = "✅ Status pesanan berhasil diupdate! Customer telah menerima notifikasi.";
                
                // Refresh order data
                $order = mysqli_fetch_assoc(query("SELECT o.*, u.full_name, u.email, u.username 
                                                   FROM orders o 
                                                   JOIN users u ON o.user_id = u.user_id 
                                                   WHERE o.order_id = '$order_id'"));
                $order_history = get_order_history($order_id);
            } else {
                $error = "❌ Gagal mengupdate status pesanan!";
            }
        }
    } else {
        // Tidak ada history Completed, proceed normal
        $new_status = escape($_POST['status']);
        $tracking_number = isset($_POST['tracking_number']) ? escape($_POST['tracking_number']) : null;
        $admin_notes = isset($_POST['admin_notes']) ? escape($_POST['admin_notes']) : null;
        
        // Update status menggunakan fungsi helper (otomatis kirim notifikasi ke customer)
        if(update_order_status($order_id, $new_status, $tracking_number, $admin_notes, $_SESSION['user_id'])) {
            $success = "✅ Status pesanan berhasil diupdate! Customer telah menerima notifikasi.";
            
            // Refresh order data
            $order = mysqli_fetch_assoc(query("SELECT o.*, u.full_name, u.email, u.username 
                                               FROM orders o 
                                               JOIN users u ON o.user_id = u.user_id 
                                               WHERE o.order_id = '$order_id'"));
            $order_history = get_order_history($order_id);
        } else {
            $error = "❌ Gagal mengupdate status pesanan!";
        }
    }
}

// UPDATE ADMIN NOTES ONLY
if(isset($_POST['update_notes'])) {
    $admin_notes = escape($_POST['admin_notes']);
    
    if(query("UPDATE orders SET admin_notes = '$admin_notes' WHERE order_id = '$order_id'")) {
        $success = "Admin notes updated successfully!";
        $order['admin_notes'] = $admin_notes;
    } else {
        $error = "Failed to update admin notes!";
    }
}

// CONFIRM PAYMENT
if(isset($_POST['confirm_payment'])) {
    // PERBAIKAN: Status otomatis jadi Processing (25%) setelah confirm payment
    if(query("UPDATE orders SET status = 'Processing' WHERE order_id = '$order_id'")) {
        // AUTO-GENERATE: Tracking Number & Courier Info
        auto_generate_tracking_and_courier($order_id);
        
        // Log to history dengan notes
        log_order_history($order_id, 'Processing', null, 'Payment confirmed and verified by admin. Order is now being processed.', $_SESSION['user_id']);
        
        // Send notification to customer
        send_order_status_notification($order_id, 'Processing');
        
        $success = "✅ Payment confirmed! Order status automatically changed to Processing (25%). Tracking number & courier info telah di-generate otomatis. Customer has been notified.";
        $order['status'] = 'Processing';
        
        // Refresh order data to hide buttons
        $order = mysqli_fetch_assoc(query("SELECT o.*, u.full_name, u.email, u.username 
                                           FROM orders o 
                                           JOIN users u ON o.user_id = u.user_id 
                                           WHERE o.order_id = '$order_id'"));
        
        // Refresh order history
        $order_history = get_order_history($order_id);
    } else {
        $error = "Failed to confirm payment!";
    }
}

// REJECT PAYMENT
if(isset($_POST['reject_payment'])) {
    // PERBAIKAN: Reject payment = status jadi Cancelled (0%)
    if(query("UPDATE orders SET status = 'Cancelled', payment_proof = NULL WHERE order_id = '$order_id'")) {
        // Log to history
        log_order_history($order_id, 'Cancelled', null, 'Payment proof rejected by admin. Order has been cancelled.', $_SESSION['user_id']);
        
        // Send notification to customer
        send_order_status_notification($order_id, 'Cancelled');
        
        $success = "❌ Payment rejected! Order status changed to Cancelled (0%). Customer has been notified.";
        $order['status'] = 'Cancelled';
        $order['payment_proof'] = NULL;
        
        // Refresh order data to hide buttons
        $order = mysqli_fetch_assoc(query("SELECT o.*, u.full_name, u.email, u.username 
                                           FROM orders o 
                                           JOIN users u ON o.user_id = u.user_id 
                                           WHERE o.order_id = '$order_id'"));
        
        // Refresh order history
        $order_history = get_order_history($order_id);
    } else {
        $error = "Failed to reject payment!";
    }
}

// DEMO MODE DIHAPUS - Sekarang menggunakan manual progression via Update Status
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo $order_id; ?> - RetroLoved Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=5.4">
    <link rel="stylesheet" href="../assets/css/toast.css">
</head>
<body class="admin-body">
    <script src="../assets/js/modal.js"></script>
    <script src="../assets/js/toast.js"></script>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php $page_title = "Detail Pesanan #" . $order_id; include 'includes/navbar.php'; ?>

        <div class="content-area">
            <div class="content-wrapper">
                <!-- Breadcrumbs -->
                <nav style="margin-bottom: 24px;">
                    <ol style="display: flex; align-items: center; gap: 8px; list-style: none; padding: 0; margin: 0; font-size: 14px;">
                        <li>
                            <a href="dashboard.php" style="color: #6b7280; text-decoration: none; font-weight: 500; transition: color 0.2s;" onmouseover="this.style.color='#D97706'" onmouseout="this.style.color='#6b7280'">Dashboard</a>
                        </li>
                        <li style="color: #d1d5db;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </li>
                        <li>
                            <a href="orders.php" style="color: #6b7280; text-decoration: none; font-weight: 500; transition: color 0.2s;" onmouseover="this.style.color='#D97706'" onmouseout="this.style.color='#6b7280'">Pesanan</a>
                        </li>
                        <li style="color: #d1d5db;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </li>
                        <li style="color: #D97706; font-weight: 600;">
                            Pesanan #<?php echo $order_id; ?>
                        </li>
                    </ol>
                </nav>
                
                <!-- Page Title -->
                <div style="margin-bottom: 24px;">
                    <h1 style="font-size: 32px; font-weight: 800; color: #1a1a1a; margin: 0 0 8px 0;">Detail Pesanan #<?php echo $order_id; ?></h1>
                    <p style="font-size: 15px; color: #6b7280; margin: 0;">Lihat informasi lengkap pesanan dan kelola status</p>
                </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
                <!-- Left Column -->
                <div>
                    <!-- Order Items -->
                    <div class="content-card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h3>Item Pesanan</h3>
                        </div>
                        
                        <div style="padding: 28px;">
                            <?php while($item = mysqli_fetch_assoc($order_items)): ?>
                                <div style="display: flex; gap: 16px; padding: 16px 0; border-bottom: 1px solid var(--border);">
                                    <img src="../assets/images/products/<?php echo $item['image_url']; ?>" 
                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);"
                                         onerror="this.src='../assets/images/products/placeholder.jpg'">
                                    <div style="flex: 1;">
                                        <strong style="font-size: 16px;"><?php echo $item['product_name']; ?></strong>
                                        <p style="color: var(--text-gray); margin-top: 4px;">
                                            Rp <?php echo number_format($item['price'], 0, ',', '.'); ?>
                                        </p>
                                    </div>
                                    <div style="text-align: right;">
                                        <strong style="font-size: 16px;">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></strong>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            
                            <div style="display: flex; justify-content: space-between; padding: 20px 0; margin-top: 16px; border-top: 2px solid var(--border);">
                                <strong style="font-size: 18px;">Total</strong>
                                <strong style="font-size: 20px; color: var(--primary);">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Info -->
                    <div class="content-card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h3>Informasi Pengiriman</h3>
                        </div>
                        
                        <div style="padding: 28px;">
                            <div style="margin-bottom: 16px;">
                                <label style="font-weight: 600; color: var(--text-gray); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Nomor Telepon</label>
                                <p style="font-size: 15px; margin-top: 4px;"><?php echo $order['phone']; ?></p>
                            </div>
                            
                            <div>
                                <label style="font-weight: 600; color: var(--text-gray); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Alamat Pengiriman</label>
                                <p style="font-size: 15px; margin-top: 4px; line-height: 1.6;"><?php echo nl2br($order['shipping_address']); ?></p>
                            </div>
                        </div>
                    </div>

                    
                    <!-- Order Timeline/History -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                Timeline Pesanan
                            </h3>
                            <p style="color: #6b7280; font-size: 13px; margin-top: 4px;">Riwayat lengkap perubahan status</p>
                        </div>
                        
                        <div style="padding: 28px;">
                            <?php if(mysqli_num_rows($order_history) > 0): ?>
                                <?php 
                                $histories = [];
                                mysqli_data_seek($order_history, 0);
                                while($history = mysqli_fetch_assoc($order_history)) {
                                    $histories[] = $history;
                                }
                                $total = count($histories);
                                $half = ceil($total / 2);
                                $left_items = array_slice($histories, 0, $half);
                                $right_items = array_slice($histories, $half);
                                ?>
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                                    <!-- Left Column Timeline -->
                                    <div class="timeline">
                                        <?php foreach($left_items as $index => $history): ?>
                                            <div class="timeline-item">
                                                <div class="timeline-marker <?php echo 'marker-' . strtolower($history['status']); ?>"></div>
                                                <div class="timeline-content">
                                                    <div class="timeline-header">
                                                        <strong class="timeline-status"><?php echo $history['status']; ?></strong>
                                                        <span class="timeline-date"><?php echo date('d M Y, H:i', strtotime($history['created_at'])); ?></span>
                                                    </div>
                                                    
                                                    <?php if($history['tracking_number']): ?>
                                                        <div class="timeline-detail">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <rect x="1" y="3" width="15" height="13"></rect>
                                                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                                            </svg>
                                                            Tracking: <strong><?php echo htmlspecialchars($history['tracking_number']); ?></strong>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($history['notes']): ?>
                                                        <div class="timeline-notes">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                                            </svg>
                                                            <?php echo nl2br(htmlspecialchars($history['notes'])); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($history['admin_name']): ?>
                                                        <div class="timeline-admin">
                                                            Changed by: <strong><?php echo htmlspecialchars($history['admin_name']); ?></strong>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Right Column Timeline -->
                                    <div class="timeline">
                                        <?php foreach($right_items as $index => $history): ?>
                                            <div class="timeline-item">
                                                <div class="timeline-marker <?php echo 'marker-' . strtolower($history['status']); ?>"></div>
                                                <div class="timeline-content">
                                                    <div class="timeline-header">
                                                        <strong class="timeline-status"><?php echo $history['status']; ?></strong>
                                                        <span class="timeline-date"><?php echo date('d M Y, H:i', strtotime($history['created_at'])); ?></span>
                                                    </div>
                                                    
                                                    <?php if($history['tracking_number']): ?>
                                                        <div class="timeline-detail">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <rect x="1" y="3" width="15" height="13"></rect>
                                                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                                            </svg>
                                                            Tracking: <strong><?php echo htmlspecialchars($history['tracking_number']); ?></strong>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($history['notes']): ?>
                                                        <div class="timeline-notes">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                                            </svg>
                                                            <?php echo nl2br(htmlspecialchars($history['notes'])); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($history['admin_name']): ?>
                                                        <div class="timeline-admin">
                                                            Changed by: <strong><?php echo htmlspecialchars($history['admin_name']); ?></strong>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p style="text-align: center; color: #6b7280; padding: 20px;">No history available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>

                                    <!-- Payment Proof -->
                    <?php if(!empty($order['payment_proof'])): ?>
                    <div class="content-card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h3>Bukti Pembayaran</h3>
                        </div>
                        
                        <div style="padding: 28px;">
                            <img src="../assets/images/payments/<?php echo $order['payment_proof']; ?>" 
                                 style="width: 100%; border-radius: 12px; border: 1px solid var(--border);"
                                 alt="Payment Proof">
                            
                            <?php if($order['status'] == 'Pending'): ?>
                                <div style="margin-top: 20px; display: flex; gap: 12px;">
                                    <form method="POST" style="flex: 1;">
                                        <button type="button" class="btn-action btn-view" style="width: 100%;" 
                                                onclick="confirmModal('Konfirmasi pembayaran ini? Status order akan berubah menjadi Processing.', function() { document.querySelector('button[name=confirm_payment]').click(); }, null, { confirmText: 'Ya, Konfirmasi', iconType: 'success' })">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                            Konfirmasi Pembayaran
                                        </button>
                                        <button type="submit" name="confirm_payment" style="display: none;"></button>
                                    </form>
                                    <form method="POST" style="flex: 1;">
                                        <button type="button" class="btn-action btn-delete" style="width: 100%;"
                                                onclick="confirmModal('Tolak pembayaran ini? Customer perlu upload ulang bukti pembayaran.', function() { document.querySelector('button[name=reject_payment]').click(); }, null, { confirmText: 'Ya, Tolak', iconType: 'warning' })">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                            Tolak Pembayaran
                                        </button>
                                        <button type="submit" name="reject_payment" style="display: none;"></button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Order Summary -->
                    <div class="content-card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h3>Ringkasan Pesanan</h3>
                        </div>
                        
                        <div style="padding: 28px;">
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; color: var(--text-gray); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">ID Pesanan</label>
                                <p style="font-size: 16px; margin-top: 4px; font-weight: 700;">#<?php echo $order['order_id']; ?></p>
                            </div>
                            
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; color: var(--text-gray); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Tanggal Pesanan</label>
                                <p style="font-size: 15px; margin-top: 4px;"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></p>
                            </div>
                            
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; color: var(--text-gray); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Metode Pembayaran</label>
                                <p style="font-size: 15px; margin-top: 4px;"><?php echo $order['payment_method']; ?></p>
                            </div>
                            
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; color: var(--text-gray); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Status</label>
                                <div style="margin-top: 8px;">
                                    <?php
                                    $status_class = '';
                                    switch($order['status']) {
                                        case 'Pending': $status_class = 'status-pending'; break;
                                        case 'Processing': $status_class = 'status-processing'; break;
                                        case 'Shipped': $status_class = 'status-shipped'; break;
                                        case 'Delivered': $status_class = 'status-delivered'; break;
                                        case 'Cancelled': $status_class = 'status-cancelled'; break;
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if(!empty($order['tracking_number'])): ?>
                            <div>
                                <label style="font-weight: 600; color: var(--text-gray); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Nomor Resi</label>
                                <div style="margin-top: 8px; padding: 12px; background: #E0E7FF; border-radius: 8px; border: 1px solid #6366F1;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2">
                                            <rect x="1" y="3" width="15" height="13"></rect>
                                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                        </svg>
                                        <strong style="color: #4338CA; font-size: 16px; letter-spacing: 0.5px;"><?php echo htmlspecialchars($order['tracking_number']); ?></strong>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="content-card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h3>Info Customer</h3>
                        </div>
                        
                        <div style="padding: 28px;">
                            <div style="margin-bottom: 16px;">
                                <label style="font-weight: 600; color: var(--text-gray); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Name</label>
                                <p style="font-size: 15px; margin-top: 4px; font-weight: 600;"><?php echo $order['full_name']; ?></p>
                            </div>
                            
                            <div style="margin-bottom: 16px;">
                                <label style="font-weight: 600; color: var(--text-gray); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Email</label>
                                <p style="font-size: 15px; margin-top: 4px;"><?php echo $order['email']; ?></p>
                            </div>
                            
                            <div>
                                <label style="font-weight: 600; color: var(--text-gray); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Username</label>
                                <p style="font-size: 15px; margin-top: 4px;"><?php echo $order['username']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Update Status -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3>Update Status</h3>
                        </div>
                        
                        <div style="padding: 28px;">
                            <form method="POST" action="">
                                <div class="form-group" style="margin-bottom: 16px;">
                                    <label>Pilih Status Baru</label>
                                    <select name="status" class="form-input" required>
                                        <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="Completed" <?php echo $order['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                
                                <div class="form-group" style="margin-bottom: 16px;">
                                    <label>Nomor Resi</label>
                                    <input type="text" name="tracking_number" class="form-input" 
                                           value="<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>"
                                           placeholder="contoh: JNE123456789">
                                    <small style="color: #6b7280; font-size: 13px;">Masukkan nomor resi untuk pengiriman</small>
                                </div>
                                
                                <div class="form-group" style="margin-bottom: 20px;">
                                    <label>Catatan Status / Alasan</label>
                                    <textarea name="admin_notes" class="form-input" rows="3" 
                                              placeholder="Masukkan alasan atau catatan tentang update status ini (contoh: 'Paket sedang dalam perjalanan ke Jakarta')"></textarea>
                                    <small style="color: #6b7280; font-size: 13px;">Opsional: Tambahkan catatan tentang mengapa status diupdate</small>
                                </div>
                                
                                <div style="background: #E0E7FF; border: 1px solid #6366F1; border-radius: 8px; padding: 12px; margin-bottom: 20px;">
                                    <div style="display: flex; align-items: start; gap: 8px;">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4338CA" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="16" x2="12" y2="12"></line>
                                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                        </svg>
                                        <div style="flex: 1;">
                                            <p style="color: #4338CA; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Tracking Progress</p>
                                            <p style="color: #4F46E5; font-size: 12px; line-height: 1.5;">
                                                <strong>Pending (15%)</strong> → Confirm Payment → <strong>Processing (25%)</strong> → <strong>Shipped (75%)</strong> → <strong>Delivered (100%)</strong> → <strong>Completed (100%)</strong><br>
                                                Admin dapat mengubah status kapan saja termasuk Completed
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" name="update_status" class="btn-action btn-view" style="width: 100%;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Update Status
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 32px;">
                <a href="orders.php" class="btn-action" style="background: var(--text-gray);">← Kembali ke Pesanan</a>
            </div>
            </div>
        </div>
    </div>

    <script>
        // Show toast notifications for success/error messages
        <?php if($success): ?>
            window.addEventListener('DOMContentLoaded', function() {
                toastSuccess('<?php echo addslashes($success); ?>');
            });
        <?php endif; ?>
        
        <?php if($error): ?>
            window.addEventListener('DOMContentLoaded', function() {
                toastError('<?php echo addslashes($error); ?>');
            });
        <?php endif; ?>
        
        // AUTO-CHECK: Polling untuk mendeteksi jika customer confirm order
        <?php if(trim($order['status']) == 'Delivered'): ?>
        (function() {
            let lastStatus = '<?php echo trim($order['status']); ?>';
            let checkInterval = null;
            
            // Check setiap 3 detik jika ada perubahan status
            checkInterval = setInterval(function() {
                fetch('order-detail.php?id=<?php echo $order_id; ?>&ajax_check=1', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status && data.status !== lastStatus) {
                        console.log('Status changed from', lastStatus, 'to', data.status);
                        
                        if(data.status === 'Completed') {
                            clearInterval(checkInterval);
                            
                            // Show notification
                            toastSuccess('✅ Customer telah konfirmasi pesanan! Redirecting...');
                            
                            // Redirect ke orders page setelah 2 detik
                            setTimeout(function() {
                                window.location.href = 'orders.php';
                            }, 2000);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error checking status:', error);
                });
            }, 3000); // Check every 3 seconds
            
            // Stop checking setelah 5 menit (100 checks)
            setTimeout(function() {
                clearInterval(checkInterval);
                console.log('Status check stopped after 5 minutes');
            }, 300000);
        })();
        <?php endif; ?>
    </script>
</body>
</html>

