<?php
/**
 * Order Tracking Page - Customer
 * Menampilkan detail tracking pengiriman untuk customer
 * RetroLoved E-Commerce System
 */

session_start();

// Validasi: Hanya customer yang bisa akses
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/database.php';
require_once '../config/shipping.php';
$base_url = '../';

$order_id = isset($_GET['id']) ? escape($_GET['id']) : null;
$user_id = $_SESSION['user_id'];

if(!$order_id) {
    header('Location: orders.php');
    exit();
}

// HANDLE CONFIRM DELIVERY - Hanya boleh dari status Delivered
if(isset($_POST['confirm_delivery'])) {
    $order_id_escaped = escape($order_id);
    $user_id_escaped = escape($user_id);
    
    // Validasi: Order harus milik user dan status Delivered
    $check_order = mysqli_fetch_assoc(query("SELECT * FROM orders WHERE order_id = '$order_id_escaped' AND user_id = '$user_id_escaped' AND status = 'Delivered'"));
    
    if($check_order) {
        // CRITICAL FIX: Pastikan syntax SQL benar (tidak ada koma setelah WHERE)
        $update_query = "UPDATE orders 
                         SET status = 'Completed',
                             delivered_at = NOW(),
                             current_status_detail = 'completed'
                         WHERE order_id = '$order_id_escaped' 
                         AND user_id = '$user_id_escaped'
                         AND status = 'Delivered'";
        
        error_log("UPDATE QUERY: " . $update_query);
        
        $update_result = query($update_query);
        $affected_rows = mysqli_affected_rows($GLOBALS['conn']);
        
        error_log("UPDATE RESULT: " . ($update_result ? 'TRUE' : 'FALSE') . ", Affected Rows: " . $affected_rows);
        
        if($update_result && $affected_rows > 0) {
            // PROTEKSI: Lock order agar tidak bisa diubah lagi
            // Set flag khusus di session untuk mencegah race condition
            $_SESSION['order_lock_' . $order_id_escaped] = time();
            
            // Log to order_history PERTAMA (sebelum notification)
            query("INSERT INTO order_history 
                   (order_id, status, status_detail, notes, changed_by) 
                   VALUES 
                   ('$order_id_escaped', 
                    'Completed', 
                    'completed',
                    'Pesanan dikonfirmasi telah diterima oleh customer.',
                    '$user_id_escaped')");
            
            // Verify update dengan fresh query
            $verify_order = mysqli_fetch_assoc(query("SELECT status, current_status_detail FROM orders WHERE order_id = '$order_id_escaped'"));
            error_log("CONFIRM DELIVERY SUCCESS: Order #$order_id_escaped - Status: " . $verify_order['status'] . ", Detail: " . $verify_order['current_status_detail']);
            
            // Send notification
            send_order_status_notification($order_id_escaped, 'Completed');
            
            // Set session flag untuk show thank you panel
            $_SESSION['show_thank_you_panel'] = true;
            $_SESSION['order_completed'] = $order_id_escaped;
            
            // Redirect
            header('Location: order-tracking.php?id=' . $order_id);
            exit();
        } else {
            error_log("CONFIRM DELIVERY ERROR: Failed to update order #$order_id_escaped. Affected rows: $affected_rows");
            set_message('error', 'Gagal mengkonfirmasi pesanan. Silakan coba lagi.');
        }
    } else {
        error_log("CONFIRM DELIVERY ERROR: Order #$order_id_escaped validation failed. Not found or status is not Delivered");
        set_message('error', 'Pesanan tidak dapat dikonfirmasi. Status harus Delivered.');
    }
}

// Get order with user verification
$order = mysqli_fetch_assoc(query("SELECT * FROM orders WHERE order_id = '$order_id' AND user_id = '$user_id'"));

if(!$order) {
    set_message('error', 'Order tidak ditemukan!');
    header('Location: orders.php');
    exit();
}

// DEBUG: Log current order status
error_log("ORDER TRACKING PAGE LOAD: Order #$order_id - Status from DB: '" . $order['status'] . "' (trimmed: '" . trim($order['status']) . "')");

// Check if we should show thank you panel
$show_thank_you = false;
if(isset($_SESSION['show_thank_you_panel']) && $_SESSION['show_thank_you_panel'] === true) {
    $show_thank_you = true;
    unset($_SESSION['show_thank_you_panel']);
    
    // Double check status is still Completed
    $verify_status = mysqli_fetch_assoc(query("SELECT status FROM orders WHERE order_id = '$order_id'"));
    error_log("THANK YOU PANEL: Verifying status for Order #$order_id - Status: '" . $verify_status['status'] . "'");
    
    // If status is not Completed, don't show thank you panel (something went wrong)
    if(trim($verify_status['status']) !== 'Completed') {
        error_log("ERROR: Status changed unexpectedly! Expected 'Completed', got: '" . $verify_status['status'] . "'");
        $show_thank_you = false;
    }
}

// Get order items
$order_items = query("SELECT oi.*, p.product_name, p.image_url, p.description 
                      FROM order_items oi 
                      JOIN products p ON oi.product_id = p.product_id 
                      WHERE oi.order_id = '$order_id'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan #<?php echo $order_id; ?> - RetroLoved</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/tracking.css">
    <link rel="stylesheet" href="../assets/css/toast.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- BREADCRUMB -->
    <div class="breadcrumb-container">
        <div class="container">
            <nav class="breadcrumb">
                <a href="../index.php" class="breadcrumb-item">Beranda</a>
                <span class="breadcrumb-separator">/</span>
                <a href="orders.php" class="breadcrumb-item">Pesanan Saya</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-item active">Lacak Pesanan</span>
            </nav>
        </div>
    </div>

    <!-- PAGE HEADER -->
    <section style="background: white; padding: 40px 0 20px 0; border-bottom: 1px solid #E5E7EB;">
        <div class="container" style="max-width: 1000px;">
            <div style="display: flex; align-items: flex-start; gap: 16px;">
                <!-- Back Button -->
                <a href="orders.php" style="flex-shrink: 0; display: flex; align-items: center; justify-content: center; padding-top: 4px; transition: all 0.3s; text-decoration: none;" onmouseover="this.style.transform='translateX(-3px)'" onmouseout="this.style.transform='translateX(0)'">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </a>
                <!-- Icon -->
                <div style="flex-shrink: 0; display: flex; align-items: center; padding-top: 2px;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                </div>
                <!-- Text Content -->
                <div style="flex: 1;">
                    <h1 class="orders-title" style="margin: 0 0 8px 0; line-height: 1.2;">Lacak Pesanan</h1>
                    <p class="orders-subtitle" style="margin: 0; line-height: 1.5;">Monitor status pengiriman pesanan #<?php echo $order_id; ?></p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Main Content -->
    <section class="content-section" style="padding: 60px 0; background: #F9FAFB;">
        <div class="container">
            <div style="max-width: 1000px; margin: 0 auto;">
                
                <!-- Row 1: Order Information + Order Items -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-bottom: 32px;">
                    <!-- Order Information -->
                    <div>
                        <div style="background: white; border-radius: 16px; padding: 32px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); height: 100%;">
                            <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; color: #1F2937;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                </svg>
                                Informasi Pesanan
                            </h3>
                            
                            <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid #F3F4F6;">
                                <label style="font-size: 11px; color: #9CA3AF; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 6px;">ID Pesanan</label>
                                <p style="font-size: 18px; font-weight: 800; margin: 0; color: #1F2937;">#<?php echo $order_id; ?></p>
                            </div>
                            
                            <div style="margin-bottom: 20px;">
                                <label style="font-size: 11px; color: #9CA3AF; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 6px;">Tanggal Pesanan</label>
                                <p style="font-size: 15px; margin: 0; color: #4B5563;"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></p>
                            </div>
                            
                            <div style="margin-bottom: 20px;">
                                <label style="font-size: 11px; color: #9CA3AF; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 6px;">Status</label>
                                <span style="display: inline-block; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 700; background: <?php 
                                    $bg = '#DBEAFE'; $color = '#1E40AF';
                                    $status_trimmed = trim($order['status']);
                                    switch($status_trimmed) {
                                        case 'Pending': $bg = '#FEF3C7'; $color = '#92400E'; break;
                                        case 'Processing': $bg = '#DBEAFE'; $color = '#1E40AF'; break;
                                        case 'Shipped': $bg = '#E0E7FF'; $color = '#4338CA'; break;
                                        case 'Delivered': $bg = '#D1FAE5'; $color = '#065F46'; break;
                                        case 'Completed': $bg = '#D1FAE5'; $color = '#065F46'; break;
                                        case 'Cancelled': $bg = '#FEE2E2'; $color = '#991B1B'; break;
                                    }
                                    echo $bg;
                                ?>; color: <?php echo $color; ?>;">
                                    <?php echo $status_trimmed; ?>
                                </span>
                            </div>
                            
                            <div style="background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); border-radius: 12px; padding: 20px; border: 2px solid #F59E0B;">
                                <label style="font-size: 11px; color: #92400E; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 6px;">Total Pembayaran</label>
                                <p style="font-size: 24px; font-weight: 800; color: #D97706; margin: 0;">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <div>
                        <div style="background: white; border-radius: 16px; padding: 32px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); height: 100%;">
                            <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; color: #1F2937;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                Item Pesanan
                            </h3>
                            <?php 
                            mysqli_data_seek($order_items, 0);
                            while($item = mysqli_fetch_assoc($order_items)): 
                            ?>
                                <div style="padding: 20px; background: #F9FAFB; border-radius: 12px; margin-bottom: 16px; border: 1px solid #E5E7EB;">
                                    <div style="display: flex; align-items: flex-start; gap: 16px; margin-bottom: 12px;">
                                        <img src="../assets/images/products/<?php echo $item['image_url']; ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px; border: 2px solid #E5E7EB; flex-shrink: 0;">
                                        <div style="flex: 1;">
                                            <strong style="display: block; font-size: 17px; margin-bottom: 6px; color: #1F2937;"><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                                <span style="font-size: 13px; color: #6B7280;">Qty: <strong style="color: #1F2937;"><?php echo $item['quantity']; ?></strong></span>
                                                <strong style="font-size: 18px; color: #D97706; font-weight: 800;">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if(!empty($item['description'])): ?>
                                    <div style="padding: 12px; background: white; border-radius: 8px; border-left: 3px solid #D97706;">
                                        <div style="font-size: 11px; color: #9CA3AF; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline points="14 2 14 8 20 8"></polyline>
                                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                            </svg>
                                            Deskripsi Produk
                                        </div>
                                        <p style="font-size: 13px; color: #4B5563; line-height: 1.6; margin: 0;">
                                            <?php 
                                            $description = htmlspecialchars($item['description']);
                                            echo strlen($description) > 150 ? substr($description, 0, 150) . '...' : $description;
                                            ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Row 2: Shipping Details + Informasi Kurir -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-bottom: 32px;">
                    <!-- Shipping Details -->
                    <div>
                        <div style="background: white; border-radius: 16px; padding: 32px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); height: 100%;">
                            <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; color: #1F2937;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                Detail Pengiriman
                            </h3>
                            
                            <!-- Alamat Pengiriman -->
                            <div style="background: #F9FAFB; border-radius: 12px; padding: 20px; margin-bottom: 16px; border: 1px solid #E5E7EB;">
                                <label style="font-size: 11px; color: #9CA3AF; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    Alamat Tujuan
                                </label>
                                <div style="font-size: 14px; color: #1F2937; line-height: 1.8; font-weight: 500;">
                                    <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                                </div>
                            </div>
                            
                            <?php if(!empty($order['phone'])): ?>
                            <!-- Nomor Telepon -->
                            <div style="background: #F9FAFB; border-radius: 12px; padding: 20px; border: 1px solid #E5E7EB;">
                                <label style="font-size: 11px; color: #9CA3AF; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                    Nomor Telepon
                                </label>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                    <strong style="font-size: 16px; color: #1F2937;"><?php echo htmlspecialchars($order['phone']); ?></strong>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Informasi Kurir -->
                    <div>
                        <div style="background: white; border-radius: 16px; padding: 32px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); height: 100%;">
                            <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; color: #1F2937;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <rect x="1" y="3" width="15" height="13"></rect>
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                </svg>
                                Informasi Kurir
                            </h3>
                            
                            <?php if(!empty($order['courier_name']) || !empty($order['courier_phone'])): ?>
                                <?php if(!empty($order['courier_name'])): ?>
                                <!-- Nama Ekspedisi -->
                                <div style="background: #F9FAFB; border-radius: 12px; padding: 20px; margin-bottom: 16px; border: 1px solid #E5E7EB;">
                                    <label style="font-size: 11px; color: #9CA3AF; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                                        Nama Ekspedisi
                                    </label>
                                    <strong style="font-size: 16px; color: #1F2937;"><?php echo htmlspecialchars($order['courier_name']); ?></strong>
                                </div>
                                <?php endif; ?>
                                
                                <?php if(!empty($order['courier_phone'])): ?>
                                <!-- Kontak Kurir -->
                                <div style="background: #F9FAFB; border-radius: 12px; padding: 20px; border: 1px solid #E5E7EB;">
                                    <label style="font-size: 11px; color: #9CA3AF; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                        Kontak Kurir
                                    </label>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2.5">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                        <strong style="font-size: 16px; color: #1F2937;"><?php echo htmlspecialchars($order['courier_phone']); ?></strong>
                                    </div>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div style="background: #F9FAFB; border-radius: 12px; padding: 30px; text-align: center; border: 1px solid #E5E7EB;">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" style="margin: 0 auto 16px;">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    <p style="color: #6B7280; margin: 0; font-size: 14px;">Informasi kurir belum tersedia</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Tracking Timeline -->
                <?php include '../includes/tracking-timeline.php'; ?>
                
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <!-- Thank You Panel Modal -->
    <?php if($show_thank_you): ?>
    <div id="thankYouPanel" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); display: flex; align-items: center; justify-content: center; z-index: 9999; cursor: pointer;">
        <div id="thankYouContent" style="background: white; border-radius: 24px; padding: 60px 80px; text-align: center; max-width: 600px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); animation: slideIn 0.5s ease-out; cursor: default;">
            <!-- Success Icon -->
            <div style="margin-bottom: 32px;">
                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" style="margin: 0 auto; animation: scaleIn 0.6s ease-out;">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            
            <!-- Thank You Message -->
            <h2 style="font-size: 36px; font-weight: 800; color: #1F2937; margin-bottom: 16px; line-height: 1.2; display: flex; align-items: center; justify-content: center; gap: 12px;">
                Terima Kasih! <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
            </h2>
            <p style="font-size: 18px; color: #6B7280; line-height: 1.7; margin-bottom: 8px;">
                Pesanan Anda telah dikonfirmasi sebagai diterima.
            </p>
            <p style="font-size: 16px; color: #9CA3AF; margin-bottom: 32px;">
                Kami senang bisa melayani Anda!
            </p>
            
            <!-- Decorative Elements -->
            <div style="margin-top: 32px; opacity: 0.2; display: flex; justify-content: center; gap: 20px;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#8B5CF6" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#EC4899" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            </div>
            
            <!-- Auto-close countdown -->
            <p id="countdown" style="font-size: 13px; color: #9CA3AF; margin-top: 24px; font-style: italic;">
                Panel akan tertutup otomatis dalam <strong id="seconds">10</strong> detik
            </p>
            <p style="font-size: 13px; color: #9CA3AF; margin-top: 8px; font-style: italic;">
                atau klik di luar panel untuk menutup
            </p>
        </div>
    </div>
    
    <style>
        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
    </style>
    
    <script>
        (function() {
            // Auto-close countdown
            let countdown = 10;
            let countdownInterval = null;
            
            // Close panel function
            function closePanel() {
                if(countdownInterval) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                }
                
                const panel = document.getElementById('thankYouPanel');
                if(panel) {
                    // Add fade out animation
                    panel.style.animation = 'fadeOut 0.3s ease-out';
                    
                    // Remove panel after animation
                    setTimeout(function() {
                        panel.style.display = 'none';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }, 300);
                }
            }
            
            // Start countdown when page loads
            countdownInterval = setInterval(function() {
                countdown--;
                const secondsElement = document.getElementById('seconds');
                if(secondsElement) {
                    secondsElement.textContent = countdown;
                }
                
                if(countdown <= 0) {
                    closePanel();
                }
            }, 1000);
            
            // Click outside panel to close
            const panel = document.getElementById('thankYouPanel');
            if(panel) {
                panel.addEventListener('click', function(e) {
                    if(e.target === panel) {
                        closePanel();
                    }
                });
            }
            
            // Prevent closing when clicking inside the panel content
            const panelContent = document.getElementById('thankYouContent');
            if(panelContent) {
                panelContent.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        })();
    </script>
    <?php endif; ?>
    
    <script src="../assets/js/toast.js"></script>
    <?php display_message(); ?>
</body>
</html>
