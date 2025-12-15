<?php
/**
 * Halaman Pesanan Customer (My Orders)
 * Menampilkan daftar pesanan, upload bukti pembayaran, dan cancel order
 * RetroLoved E-Commerce System
 */

// Mulai session
session_start();

// Set PHP upload limits using ini_set() as fallback if .htaccess doesn't work
// This is necessary when server uses PHP-FPM or CGI mode instead of mod_php
@ini_set('upload_max_filesize', '10M');
@ini_set('post_max_size', '12M');
@ini_set('max_execution_time', '300');
@ini_set('max_input_time', '300');

// Log current PHP settings for debugging
error_log("PHP Upload Settings - upload_max_filesize: " . ini_get('upload_max_filesize'));
error_log("PHP Upload Settings - post_max_size: " . ini_get('post_max_size'));

// Validasi: Hanya customer yang bisa akses halaman ini
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header('Location: ../index.php');
    exit();
}

// Include koneksi database
require_once '../config/database.php';
require_once '../config/shipping.php';
$base_url = '../';

// Ambil ID user yang sedang login
$user_id = $_SESSION['user_id'];

// Otomatis tandai notifikasi sebagai sudah dibaca jika datang dari link notifikasi
if(isset($_GET['notification_id']) && is_numeric($_GET['notification_id'])) {
    $notification_id = escape($_GET['notification_id']);
    // Update status notifikasi menjadi sudah dibaca
    query("UPDATE notifications SET is_read = 1 WHERE notification_id = '$notification_id' AND user_id = '$user_id'");
    
    // Redirect ke URL bersih (hapus parameter notification_id)
    $clean_url = 'orders.php';
    if(isset($_GET['filter'])) {
        $clean_url .= '?filter=' . urlencode($_GET['filter']);
    }
    header('Location: ' . $clean_url);
    exit();
}

// HANDLE UPLOAD PAYMENT PROOF
if(isset($_POST['upload_payment'])) {
    error_log("=== UPLOAD PAYMENT PROOF TRIGGERED ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
    
    $order_id = escape($_POST['order_id']);
    error_log("Order ID: " . $order_id);
    
    // Verify order belongs to user dan cek payment deadline
    $check_order = mysqli_fetch_assoc(query("SELECT * FROM orders WHERE order_id = '$order_id' AND user_id = '$user_id'"));
    
    if(!$check_order) {
        error_log("ERROR: Order not found for user");
        set_message('error', 'Order tidak ditemukan!');
    } elseif(!empty($check_order['payment_deadline']) && strtotime($check_order['payment_deadline']) < time()) {
        // Payment deadline sudah lewat
        error_log("ERROR: Payment deadline expired");
        set_message('error', 'Waktu pembayaran telah habis! Order ini sudah tidak dapat diproses.');
    } else {
        error_log("Order found, checking file upload...");
        
        // Check if POST data was lost due to post_max_size exceeded
        if(empty($_POST) && empty($_FILES)) {
            error_log("ERROR: POST and FILES are empty - likely post_max_size exceeded");
            set_message('error', 'File terlalu besar! Ukuran maksimal adalah 5MB. Silakan kompres file Anda terlebih dahulu.');
        }
        // Handle file upload
        else if(isset($_FILES['payment_proof'])) {
            $upload_error = $_FILES['payment_proof']['error'];
            error_log("File upload error code: " . $upload_error);
            
            // Check for upload errors
            if($upload_error !== UPLOAD_ERR_OK) {
                switch($upload_error) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        error_log("ERROR: File exceeds upload_max_filesize or MAX_FILE_SIZE");
                        set_message('error', 'Ukuran file terlalu besar! Maksimal 5MB.');
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        error_log("ERROR: File was only partially uploaded");
                        set_message('error', 'Upload file gagal! File hanya terupload sebagian. Silakan coba lagi.');
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        error_log("ERROR: No file was uploaded");
                        set_message('error', 'Silakan pilih file terlebih dahulu!');
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        error_log("ERROR: Missing temporary folder");
                        set_message('error', 'Terjadi kesalahan server! Silakan hubungi administrator.');
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        error_log("ERROR: Failed to write file to disk");
                        set_message('error', 'Gagal menyimpan file! Silakan coba lagi.');
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        error_log("ERROR: File upload stopped by extension");
                        set_message('error', 'Upload dihentikan oleh ekstensi server!');
                        break;
                    default:
                        error_log("ERROR: Unknown upload error: " . $upload_error);
                        set_message('error', 'Terjadi kesalahan saat upload! Silakan coba lagi.');
                }
            } else {
                error_log("File upload detected, processing...");
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['payment_proof']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if(!in_array(strtolower($filetype), $allowed)) {
                    set_message('error', 'Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan!');
                } else {
                    // Validate file size (max 5MB)
                    $maxSize = 5 * 1024 * 1024; // 5MB
                    if($_FILES['payment_proof']['size'] > $maxSize) {
                        set_message('error', 'Ukuran file maksimal 5MB!');
                    } else {
                        // Generate unique filename
                        $new_filename = 'payment_' . $order_id . '_' . time() . '.' . $filetype;
                        $upload_path = '../assets/images/payments/' . $new_filename;
                        
                        // Create folder if not exists
                        if(!file_exists('../assets/images/payments/')) {
                            mkdir('../assets/images/payments/', 0777, true);
                        }
                        
                        if(move_uploaded_file($_FILES['payment_proof']['tmp_name'], $upload_path)) {
                            error_log("File uploaded successfully: " . $upload_path);
                            
                            // Verify file actually exists after upload
                            if(file_exists($upload_path)) {
                                // Update database only if file successfully uploaded
                                // Update order dengan payment proof, status jadi Pending (15%)
                                $update = query("UPDATE orders SET payment_proof = '$new_filename', status = 'Pending' WHERE order_id = '$order_id'");
                                
                                // Log ke order_history
                                if($update) {
                                    require_once '../config/database.php';
                                    $user_id = $_SESSION['user_id'];
                                    $notes = 'Customer uploaded payment proof. Waiting for admin confirmation.';
                                    query("INSERT INTO order_history (order_id, status, status_detail, notes, changed_by, created_at) 
                                           VALUES ('$order_id', 'Pending', NULL, '$notes', '$user_id', NOW())");
                                }
                                
                                if($update) {
                                    error_log("Database updated successfully");
                                    set_message('success', 'Bukti pembayaran berhasil diupload! Menunggu konfirmasi admin.');
                                } else {
                                    error_log("ERROR: Database update failed");
                                    // Delete uploaded file if database update fails
                                    if(file_exists($upload_path)) {
                                        unlink($upload_path);
                                    }
                                    set_message('error', 'Gagal mengupdate database!');
                                }
                            } else {
                                error_log("ERROR: File upload verification failed - file doesn't exist");
                                set_message('error', 'Gagal memverifikasi file! Silakan coba lagi.');
                            }
                        } else {
                            error_log("ERROR: move_uploaded_file failed");
                            set_message('error', 'Gagal mengupload file! Silakan coba lagi.');
                        }
                    }
                }
            }
        } else {
            error_log("ERROR: payment_proof not set in FILES array");
            set_message('error', 'Silakan pilih file terlebih dahulu!');
        }
    }
    error_log("=== REDIRECTING TO orders.php ===");
    header('Location: orders.php');
    exit();
} else {
    error_log("upload_payment POST not set");
}

// CANCEL ORDER (hanya untuk order Pending)
if(isset($_GET['cancel'])) {
    $order_id = escape($_GET['cancel']);
    
    // Cek apakah order milik user ini dan masih pending
    $order_check = mysqli_fetch_assoc(query("SELECT * FROM orders WHERE order_id = '$order_id' AND user_id = '$user_id' AND status = 'Pending'"));
    
    if($order_check) {
        // Update status jadi Cancelled
        query("UPDATE orders SET status = 'Cancelled' WHERE order_id = '$order_id'");
        
        // Return products ke available (set is_sold = 0)
        $order_items = query("SELECT product_id FROM order_items WHERE order_id = '$order_id'");
        while($item = mysqli_fetch_assoc($order_items)) {
            query("UPDATE products SET is_sold = 0 WHERE product_id = '{$item['product_id']}'");
        }
        
        set_message('success', 'Order berhasil dibatalkan. Produk telah dikembalikan ke katalog.');
    } else {
        set_message('error', 'Order tidak dapat dibatalkan atau tidak ditemukan.');
    }
    header('Location: orders.php');
    exit();
}

// AUTO-CANCEL EXPIRED ORDERS
// Otomatis cancel order yang payment deadline-nya sudah habis
$expired_orders = query("SELECT o.order_id, o.user_id 
                        FROM orders o
                        WHERE o.user_id = '$user_id'
                        AND o.status = 'Pending' 
                        AND o.payment_proof IS NULL 
                        AND o.payment_deadline IS NOT NULL
                        AND o.payment_deadline < NOW()");

if(mysqli_num_rows($expired_orders) > 0) {
    while($expired = mysqli_fetch_assoc($expired_orders)) {
        $expired_order_id = $expired['order_id'];
        
        // Update status jadi Cancelled
        query("UPDATE orders SET status = 'Cancelled' WHERE order_id = '$expired_order_id'");
        
        // Return products ke available (set is_sold = 0)
        $expired_items = query("SELECT product_id FROM order_items WHERE order_id = '$expired_order_id'");
        while($item = mysqli_fetch_assoc($expired_items)) {
            query("UPDATE products SET is_sold = 0 WHERE product_id = '{$item['product_id']}'");
        }
        
        // Log ke order_history
        $notes = 'Order automatically cancelled due to payment deadline expired.';
        query("INSERT INTO order_history (order_id, status, status_detail, notes, changed_by, created_at) 
               VALUES ('$expired_order_id', 'Cancelled', NULL, '$notes', '$user_id', NOW())");
    }
}

// Ambil semua order user dengan payment deadline
// Note: archived column might not exist yet, so we use COALESCE to handle NULL
$orders = query("SELECT *, 
                 CASE 
                     WHEN payment_deadline IS NOT NULL AND payment_deadline > NOW() 
                     THEN TIMESTAMPDIFF(SECOND, NOW(), payment_deadline)
                     ELSE 0 
                 END as seconds_remaining
                 FROM orders 
                 WHERE user_id = '$user_id' 
                 ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - RetroLoved</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/performance.css">
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
                <span class="breadcrumb-item active">Pesanan Saya</span>
            </nav>
        </div>
    </div>

    <!-- ORDERS SECTION -->
    <section class="orders-section">
        <div class="container">
            <div class="orders-header">
                <div class="orders-title-wrapper">
                    <h1 class="orders-title">Pesanan Saya</h1>
                    <p class="orders-subtitle">Riwayat pesanan Anda</p>
                </div>
            </div>
            
            <!-- Filter Tabs -->
            <div class="orders-filter-tabs">
                <button class="filter-tab active" data-filter="recently" onclick="filterOrders('recently')">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Terbaru
                </button>
                <button class="filter-tab" data-filter="archived" onclick="filterOrders('archived')">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="21 8 21 21 3 21 3 8"></polyline>
                        <rect x="1" y="3" width="22" height="5"></rect>
                        <line x1="10" y1="12" x2="14" y2="12"></line>
                    </svg>
                    Diarsipkan
                </button>
            </div>
            
            <div class="orders-list">
        
        <?php if(mysqli_num_rows($orders) > 0): ?>
            
            <!-- Bulk Selection Header (Hidden by default) -->
            <div class="orders-bulk-header" id="bulkHeader" style="display: none;">
                <div class="bulk-header-content">
                    <div class="bulk-select-info">
                        <input type="checkbox" id="selectAllOrders" onchange="toggleSelectAll(this)">
                        <label for="selectAllOrders">
                            <span id="selectedCountText">1 dipilih</span>
                        </label>
                    </div>
                    <div class="bulk-actions">
                        <button type="button" class="btn-bulk-action" onclick="bulkArchive()" id="bulkArchiveBtn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="21 8 21 21 3 21 3 8"></polyline>
                                <rect x="1" y="3" width="22" height="5"></rect>
                                <line x1="10" y1="12" x2="14" y2="12"></line>
                            </svg>
                            <span id="bulkArchiveText">Arsipkan</span>
                        </button>
                        <button type="button" class="btn-bulk-clear" onclick="clearAllSelections()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            Bersihkan
                        </button>
                    </div>
                </div>
            </div>
            
            <?php while($order = mysqli_fetch_assoc($orders)): ?>
                <?php
                // Get all items for this order
                $order_items_query = query("SELECT oi.*, p.product_name, p.image_url, p.category, p.condition_item 
                             FROM order_items oi 
                             JOIN products p ON oi.product_id = p.product_id 
                             WHERE oi.order_id = '{$order['order_id']}'");
                
                $order_items = [];
                while($item = mysqli_fetch_assoc($order_items_query)) {
                    $order_items[] = $item;
                }
                $items_count = count($order_items);
                
                $order_status = trim($order['status']);
                
                // Status display text
                $status_text = '';
                $status_icon = '';
                if($order_status == 'Pending' && empty($order['payment_proof'])) {
                    $status_text = 'Menunggu pembayaran';
                    $status_icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
                } elseif($order_status == 'Pending' && !empty($order['payment_proof'])) {
                    $status_text = 'Menunggu konfirmasi admin';
                    $status_icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
                } elseif($order_status == 'Processing') {
                    $status_text = 'Sedang diproses';
                    $status_icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="9 11 12 14 16 10"></polyline></svg>';
                } elseif($order_status == 'Shipped') {
                    $estimated_date = date('D, d M', strtotime($order['created_at'] . ' +3 days'));
                    $status_text = 'Estimasi tiba ' . $estimated_date;
                    $status_icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>';
                } elseif($order_status == 'Delivered') {
                    $status_text = 'Paket sudah sampai';
                    $status_icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                } elseif($order_status == 'Completed') {
                    $status_text = 'Pesanan selesai';
                    $status_icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                } elseif($order_status == 'Cancelled') {
                    $status_text = 'Order dibatalkan';
                    $status_icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
                }
                ?>
                
                <!-- SIMPLE ORDER CARD -->
                <div class="order-card-simple" data-order-id="<?php echo $order['order_id']; ?>" data-archived="<?php echo isset($order['archived']) && $order['archived'] == 1 ? 'true' : 'false'; ?>">
                    <!-- Left Area: Checkbox + Content (Clickable for selection) -->
                    <div class="order-left-area" onclick="toggleOrderCheckbox(<?php echo $order['order_id']; ?>)">
                        <!-- Checkbox -->
                        <div class="order-checkbox-simple">
                            <input type="checkbox" 
                                   class="order-select-checkbox" 
                                   id="order_<?php echo $order['order_id']; ?>"
                                   value="<?php echo $order['order_id']; ?>"
                                   onchange="updateBulkActions()"
                                   onclick="event.stopPropagation()">
                        </div>
                        
                        <div class="order-content-simple">
                        <!-- Status & Date Header -->
                        <div class="order-header-simple">
                            <div class="order-status-date-wrapper">
                                <div class="order-status-simple status-<?php echo strtolower($order_status); ?>">
                                    <span class="status-dot"></span>
                                    <span class="status-text"><?php echo $order_status; ?></span>
                                </div>
                                <span class="date-separator">|</span>
                                <span class="order-date-simple"><?php echo date('d F Y', strtotime($order['created_at'])); ?></span>
                            </div>
                        </div>
                        
                        <!-- Order Info -->
                        <div class="order-info-simple">
                            <!-- Product Image -->
                            <div class="order-image-simple">
                                <img src="../assets/images/products/<?php echo $order_items[0]['image_url']; ?>" 
                                     alt="<?php echo $order_items[0]['product_name']; ?>"
                                     onerror="this.src='../assets/images/products/placeholder.jpg'">
                                <?php if($items_count > 1): ?>
                                    <span class="items-count-badge">+<?php echo $items_count - 1; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Order Details -->
                            <div class="order-details-simple">
                                <h3 class="order-id-simple">Order ID: <?php echo $order['order_id']; ?></h3>
                                <p class="order-description-simple">
                                    <?php 
                                    $product_names = array_column($order_items, 'product_name');
                                    if($items_count > 1) {
                                        echo $product_names[0] . ' & ' . ($items_count - 1) . ' item lainnya';
                                    } else {
                                        echo $product_names[0];
                                    }
                                    ?>
                                </p>
                                <p class="order-price-simple">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
                                
                                <?php 
                                // Tampilkan countdown timer dan upload button jika order pending dan belum upload payment
                                if($order_status == 'Pending' && empty($order['payment_proof'])): 
                                    $seconds_remaining = isset($order['seconds_remaining']) ? $order['seconds_remaining'] : 0;
                                    $is_expired = $seconds_remaining <= 0;
                                ?>
                                    <?php if(!empty($order['payment_deadline'])): ?>
                                        <div class="payment-deadline-info <?php echo $is_expired ? 'expired' : ''; ?>" 
                                             data-order-id="<?php echo $order['order_id']; ?>"
                                             data-seconds="<?php echo max(0, $seconds_remaining); ?>">
                                            <?php if($is_expired): ?>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                                </svg>
                                                <span>Waktu pembayaran habis</span>
                                            <?php else: ?>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <polyline points="12 6 12 12 16 14"></polyline>
                                                </svg>
                                                <span class="countdown-timer">
                                                    Bayar dalam: <strong class="timer-display">--:--</strong>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if(!$is_expired): ?>
                                        <button type="button" 
                                                onclick="event.stopPropagation(); openUploadModal(<?php echo $order['order_id']; ?>, <?php echo $order['total_amount']; ?>)" 
                                                class="btn-upload-payment">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="17 8 12 3 7 8"></polyline>
                                                <line x1="12" y1="3" x2="12" y2="15"></line>
                                            </svg>
                                            Upload Bukti Pembayaran
                                        </button>
                                    <?php endif; ?>
                                <?php elseif($order_status == 'Pending' && !empty($order['payment_proof'])): ?>
                                    <div class="payment-status-info waiting">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <span>Menunggu konfirmasi admin...</span>
                                    </div>
                                <?php elseif($order_status == 'Cancelled'): ?>
                                    <div class="payment-status-info cancelled">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg>
                                        <span>Order dibatalkan - Produk dikembalikan ke katalog</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        </div>
                    </div>
                    
                    <!-- Right Area: Arrow Button (Clickable for view details) -->
                    <!-- Hanya tampilkan jika admin sudah accept payment (status bukan Pending) -->
                    <?php if($order_status != 'Pending'): ?>
                        <div class="order-arrow-simple" onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-orders">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <h3>Belum Ada Pesanan</h3>
                        <p>Anda belum memiliki riwayat pesanan. Mulai belanja sekarang!</p>
                        <a href="../shop.php" class="btn btn-primary">Lihat Katalog Produk</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="../assets/js/toast.js"></script>
    <script src="../assets/js/modal.js"></script>
    <script src="../assets/js/loading.js"></script>
    <script src="../assets/js/script.js"></script>
    
    <script>
        // Add loading state to page
        document.addEventListener('DOMContentLoaded', function() {
            // Pastikan modal tertutup saat halaman dimuat
            const modal = document.getElementById('uploadPaymentModal');
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
            }
            
            // Hide loading overlay if exists
            const loadingOverlay = document.querySelector('.loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.remove();
            }
            
            // Add loading to cancel order
            const cancelLinks = document.querySelectorAll('[href*="cancel="]');
            cancelLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const originalHref = this.getAttribute('href');
                    e.preventDefault();
                    
                    confirmModal('Apakah Anda yakin ingin membatalkan order ini? Produk akan dikembalikan ke katalog.', function() {
                        showLoadingOverlay();
                        window.location.href = originalHref;
                    }, null, {
                        confirmText: 'Ya, Batalkan',
                        iconType: 'warning'
                    });
                });
            });
        });
        
        // Filter Orders by Tab
        let currentFilter = 'recently';
        
        function filterOrders(filter) {
            currentFilter = filter;
            
            // Update active tab
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelector(`[data-filter="${filter}"]`).classList.add('active');
            
            // Update bulk action button text
            const bulkArchiveText = document.getElementById('bulkArchiveText');
            if (bulkArchiveText) {
                bulkArchiveText.textContent = filter === 'recently' ? 'Arsipkan yang Dipilih' : 'Kembalikan yang Dipilih';
            }
            
            // Filter order cards
            const orderCards = document.querySelectorAll('.order-card-simple');
            let visibleCount = 0;
            
            orderCards.forEach(card => {
                const isArchived = card.getAttribute('data-archived') === 'true';
                
                if (filter === 'recently' && !isArchived) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else if (filter === 'archived' && isArchived) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show empty state if no orders
            const existingEmpty = document.querySelector('.empty-orders-dynamic');
            if (existingEmpty) {
                existingEmpty.remove();
            }
            
            if (visibleCount === 0) {
                const ordersList = document.querySelector('.orders-list');
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'empty-orders empty-orders-dynamic';
                emptyDiv.innerHTML = `
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <polyline points="21 8 21 21 3 21 3 8"></polyline>
                        <rect x="1" y="3" width="22" height="5"></rect>
                        <line x1="10" y1="12" x2="14" y2="12"></line>
                    </svg>
                    <h3>Tidak Ada Pesanan ${filter === 'archived' ? 'Diarsipkan' : 'Terbaru'}</h3>
                    <p>${filter === 'archived' ? 'Anda tidak memiliki pesanan yang diarsipkan.' : 'Anda tidak memiliki pesanan terbaru.'}</p>
                `;
                ordersList.appendChild(emptyDiv);
            }
            
            // Reset bulk selection
            document.getElementById('selectAllOrders').checked = false;
            updateBulkActions();
        }
        
        // Bulk Selection Functions
        function toggleSelectAll(checkbox) {
            const visibleCheckboxes = Array.from(document.querySelectorAll('.order-select-checkbox'))
                .filter(cb => cb.closest('.order-card-simple').style.display !== 'none');
            
            visibleCheckboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
            updateBulkActions();
        }

        function updateBulkActions() {
            const visibleCheckboxes = Array.from(document.querySelectorAll('.order-select-checkbox'))
                .filter(cb => cb.closest('.order-card-simple').style.display !== 'none');
            const checkedCheckboxes = visibleCheckboxes.filter(cb => cb.checked);
            const count = checkedCheckboxes.length;
            
            const bulkHeader = document.getElementById('bulkHeader');
            const selectedCountText = document.getElementById('selectedCountText');
            const bulkArchiveText = document.getElementById('bulkArchiveText');
            
            // Show/hide bulk header
            if (count > 0) {
                bulkHeader.style.display = 'block';
                selectedCountText.textContent = `${count} dipilih`;
                
                // Update archive button text based on filter
                if (bulkArchiveText) {
                    bulkArchiveText.textContent = currentFilter === 'recently' ? 'Arsipkan' : 'Kembalikan';
                }
            } else {
                bulkHeader.style.display = 'none';
            }
            
            // Update select all checkbox
            const selectAll = document.getElementById('selectAllOrders');
            if (selectAll) {
                selectAll.checked = count === visibleCheckboxes.length && count > 0;
            }
        }

        // Clear all selections
        function clearAllSelections() {
            const checkboxes = document.querySelectorAll('.order-select-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = false;
            });
            updateBulkActions();
        }

        // Bulk Actions
        function bulkArchive() {
            const selected = getSelectedOrders();
            if (selected.length === 0) return;
            
            const action = currentFilter === 'recently' ? 'archive' : 'unarchive';
            const actionText = currentFilter === 'recently' ? 'Arsipkan' : 'Kembalikan';
            const confirmButtonText = currentFilter === 'recently' ? 'Ya, Arsipkan' : 'Ya, Kembalikan';
            
            confirmModal(`${actionText} ${selected.length} pesanan?`, function() {
                // TODO: Implement archive/unarchive functionality
                console.log(`${actionText} orders:`, selected);
                
                // Update UI
                selected.forEach(orderId => {
                    const card = document.querySelector(`[data-order-id="${orderId}"]`);
                    if (card) {
                        card.setAttribute('data-archived', action === 'archive' ? 'true' : 'false');
                    }
                });
                
                toastSuccess(`${selected.length} pesanan berhasil di${action === 'archive' ? 'arsipkan' : 'kembalikan'}!`);
                
                // Refresh filter
                setTimeout(() => {
                    filterOrders(currentFilter);
                }, 500);
            }, null, {
                confirmText: confirmButtonText,
                iconType: 'info'
            });
        }

        function getSelectedOrders() {
            const checkboxes = Array.from(document.querySelectorAll('.order-select-checkbox:checked'))
                .filter(cb => cb.closest('.order-card-simple').style.display !== 'none');
            return checkboxes.map(cb => cb.value);
        }

        // Toggle checkbox when clicking left area
        function toggleOrderCheckbox(orderId) {
            const checkbox = document.getElementById(`order_${orderId}`);
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                updateBulkActions();
            }
        }

        // Individual Actions
        function viewOrderDetails(orderId) {
            window.location.href = `order-tracking.php?id=${orderId}`;
        }

        function editOrder(orderId) {
            toastInfo('Edit order feature coming soon!');
        }

        function archiveOrder(orderId) {
            const card = document.querySelector(`[data-order-id="${orderId}"]`);
            const isArchived = card.getAttribute('data-archived') === 'true';
            const actionText = isArchived ? 'Kembalikan' : 'Arsipkan';
            const confirmButtonText = isArchived ? 'Ya, Kembalikan' : 'Ya, Arsipkan';
            
            confirmModal(`${actionText} pesanan ini?`, function() {
                // TODO: Implement archive/unarchive
                console.log(`${actionText} order:`, orderId);
                
                // Update UI
                card.setAttribute('data-archived', isArchived ? 'false' : 'true');
                toastSuccess(`Pesanan berhasil di${isArchived ? 'kembalikan' : 'arsipkan'}!`);
                
                // Refresh filter
                setTimeout(() => {
                    filterOrders(currentFilter);
                }, 500);
            }, null, {
                confirmText: confirmButtonText,
                iconType: 'info'
            });
        }
        
        // Countdown Timer untuk Payment Deadline
        function initCountdownTimers() {
            const deadlineInfos = document.querySelectorAll('.payment-deadline-info:not(.expired)');
            
            deadlineInfos.forEach(info => {
                let secondsRemaining = parseInt(info.getAttribute('data-seconds'));
                const orderId = info.getAttribute('data-order-id');
                const timerDisplay = info.querySelector('.timer-display');
                
                if (secondsRemaining <= 0) {
                    info.classList.add('expired');
                    info.innerHTML = `
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        <span>Waktu pembayaran habis</span>
                    `;
                    return;
                }
                
                // Update timer setiap detik
                const interval = setInterval(() => {
                    secondsRemaining--;
                    
                    if (secondsRemaining <= 0) {
                        clearInterval(interval);
                        info.classList.add('expired');
                        info.innerHTML = `
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            <span>Waktu pembayaran habis</span>
                        `;
                        
                        // Reload page untuk update status
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                        return;
                    }
                    
                    // Format waktu MM:SS
                    const minutes = Math.floor(secondsRemaining / 60);
                    const seconds = secondsRemaining % 60;
                    const timeString = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                    
                    if (timerDisplay) {
                        timerDisplay.textContent = timeString;
                        
                        // Ubah warna jika kurang dari 1 menit
                        if (secondsRemaining < 60) {
                            timerDisplay.style.color = '#DC2626';
                            info.style.background = '#FEE2E2';
                            info.style.borderColor = '#FCA5A5';
                        }
                    }
                }, 1000);
            });
        }

        // Initialize filter on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Don't auto-filter on load, let PHP handle initial display
            // Just update button text
            const bulkArchiveText = document.getElementById('bulkArchiveText');
            if (bulkArchiveText) {
                bulkArchiveText.textContent = 'Arsipkan yang Dipilih';
            }
            
            // Initialize countdown timers
            initCountdownTimers();
        });
    </script>

    <!-- Upload Payment Modal -->
    <div id="uploadPaymentModal" class="upload-modal">
        <div class="upload-modal-content">
            <div class="upload-modal-header">
                <h3>Upload Bukti Pembayaran</h3>
                <button type="button" class="modal-close" onclick="closeUploadModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <div class="upload-modal-body">
                <!-- Order Info -->
                <div class="modal-order-info">
                    <div class="modal-order-item">
                        <span class="modal-order-label">ID Pesanan:</span>
                        <span class="modal-order-value" id="modalOrderId"></span>
                    </div>
                    <div class="modal-order-item">
                        <span class="modal-order-label">Total Pembayaran:</span>
                        <span class="modal-order-value" id="modalOrderTotal"></span>
                    </div>
                </div>
                
                <!-- Payment Guide -->
                <div class="payment-guide-compact">
                    <div class="payment-guide-header-compact">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <strong>Panduan Upload Bukti Pembayaran</strong>
                    </div>
                    <ul class="payment-guide-list-compact">
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Detail transaksi yang jelas
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Jumlah harus sesuai dengan total order
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Tanggal dan waktu terlihat
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            JPG, PNG, GIF (max 5MB)
                        </li>
                    </ul>
                </div>
                
                <!-- Upload Form -->
                <form action="orders.php" method="POST" enctype="multipart/form-data" id="uploadPaymentForm" onsubmit="handleUploadSubmit(event); return false;">
                    <input type="hidden" name="order_id" id="uploadOrderId" value="">
                    
                    <div class="file-upload-wrapper-modal">
                        <div class="file-upload-content-modal" id="uploadContentModal" onclick="document.getElementById('paymentProofInput').click()">
                            <svg class="file-upload-icon-modal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            <div class="file-upload-text-modal">Klik untuk upload bukti pembayaran</div>
                            <div class="file-upload-hint-modal">JPG, PNG atau GIF (max 5MB)</div>
                        </div>
                        <input type="file" id="paymentProofInput" name="payment_proof" accept="image/*" onchange="previewPaymentImage(this)" class="payment-proof-input-hidden">
                        <div class="image-preview-modal" id="imagePreviewModal">
                            <img id="previewImage" src="" alt="Preview">
                            <button type="button" class="btn-change-photo-modal" onclick="document.getElementById('paymentProofInput').click()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                                Ganti Foto
                            </button>
                        </div>
                    </div>
                    
                    <div class="upload-modal-actions">
                        <button type="button" class="btn-modal-cancel" onclick="closeUploadModal()">Batal</button>
                        <button type="submit" name="upload_payment" class="btn-modal-upload" id="uploadSubmitBtn" disabled>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            Upload Bukti Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <style>
        /* ===== FILTER TABS ===== */
        .orders-filter-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #F3F4F6;
        }

        .filter-tab {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: white;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            color: #6B7280;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-tab svg {
            flex-shrink: 0;
        }

        .filter-tab:hover {
            background: #FFF7ED;
            border-color: #FED7AA;
            color: #EA580C;
        }

        .filter-tab.active {
            background: #F97316;
            border-color: #F97316;
            color: white;
        }

        .filter-tab.active svg {
            stroke: white;
        }

        /* ===== CENTERED CONTAINER ===== */
        .orders-section .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        /* ===== BULK HEADER ===== */
        .orders-bulk-header {
            padding: 16px 20px;
            background: linear-gradient(135deg, #FFF7ED 0%, #FFEDD5 100%);
            border: 2px solid #F97316;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(249, 115, 22, 0.1);
        }

        .bulk-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .bulk-select-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .bulk-select-info input[type="checkbox"] {
            width: 18px !important;
            height: 18px !important;
            min-width: 18px !important;
            min-height: 18px !important;
            max-width: 18px !important;
            max-height: 18px !important;
            cursor: pointer;
            accent-color: #F97316;
            margin: 0 !important;
            padding: 0 !important;
        }

        .bulk-select-info label {
            font-weight: 700;
            color: #92400E;
            cursor: pointer;
            user-select: none;
            font-size: 15px;
        }

        #selectedCountText {
            color: #92400E;
        }

        .bulk-actions {
            display: flex;
            gap: 8px;
        }

        .btn-bulk-action {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: #F97316;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-bulk-action:hover {
            background: #EA580C;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .btn-bulk-clear {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: white;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #6B7280;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-bulk-clear:hover {
            background: #F3F4F6;
            border-color: #D1D5DB;
            color: #374151;
        }

        /* ===== SIMPLE ORDER CARD ===== */
        .order-card-simple {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            margin-bottom: 12px;
            transition: all 0.2s ease;
        }

        .order-card-simple:hover {
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border-color: #D1D5DB;
        }

        /* Left Area - Clickable for selection */
        .order-left-area {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
            cursor: pointer;
            min-width: 0;
        }

        /* Checkbox */
        .order-checkbox-simple {
            flex-shrink: 0;
        }

        .order-checkbox-simple input[type="checkbox"] {
            width: 16px !important;
            height: 16px !important;
            min-width: 16px !important;
            min-height: 16px !important;
            max-width: 16px !important;
            max-height: 16px !important;
            cursor: pointer;
            accent-color: #F97316;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Content */
        .order-content-simple {
            flex: 1;
            min-width: 0;
        }

        /* Header with Status & Date */
        .order-header-simple {
            margin-bottom: 14px;
        }

        .order-status-date-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .order-status-simple {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .date-separator {
            color: #D1D5DB;
            font-size: 14px;
            font-weight: 400;
        }

        .order-status-simple.status-pending {
            background: #FEF3C7;
        }

        .order-status-simple.status-pending .status-dot {
            background: #F59E0B;
        }

        .order-status-simple.status-pending .status-text {
            color: #92400E;
        }

        .order-status-simple.status-processing {
            background: #DBEAFE;
        }

        .order-status-simple.status-processing .status-dot {
            background: #3B82F6;
        }

        .order-status-simple.status-processing .status-text {
            color: #1E40AF;
        }

        .order-status-simple.status-shipped {
            background: #EDE9FE;
        }

        .order-status-simple.status-shipped .status-dot {
            background: #8B5CF6;
        }

        .order-status-simple.status-shipped .status-text {
            color: #6B21A8;
        }

        .order-status-simple.status-delivered {
            background: #D1FAE5;
        }

        .order-status-simple.status-delivered .status-dot {
            background: #10B981;
        }

        .order-status-simple.status-delivered .status-text {
            color: #065F46;
        }

        .order-status-simple.status-completed {
            background: #D1FAE5;
        }

        .order-status-simple.status-completed .status-dot {
            background: #10B981;
        }

        .order-status-simple.status-completed .status-text {
            color: #065F46;
        }

        .order-status-simple.status-cancelled {
            background: #FEE2E2;
        }

        .order-status-simple.status-cancelled .status-dot {
            background: #EF4444;
        }

        .order-status-simple.status-cancelled .status-text {
            color: #991B1B;
        }

        .order-date-simple {
            font-size: 12px;
            color: #9CA3AF;
            font-weight: 500;
        }

        /* Order Info */
        .order-info-simple {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .order-image-simple {
            position: relative;
            width: 80px;
            height: 80px;
            flex-shrink: 0;
            border-radius: 8px;
            overflow: hidden;
            background: #F3F4F6;
        }

        .order-image-simple img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .items-count-badge {
            position: absolute;
            bottom: 4px;
            right: 4px;
            background: rgba(0, 0, 0, 0.75);
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .order-details-simple {
            flex: 1;
            min-width: 0;
        }

        .order-id-simple {
            font-size: 15px;
            font-weight: 700;
            color: #1F2937;
            margin: 0 0 4px 0;
        }

        .order-description-simple {
            font-size: 14px;
            color: #6B7280;
            margin: 0 0 6px 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .order-price-simple {
            font-size: 15px;
            font-weight: 700;
            color: #1F2937;
            margin: 0 0 8px 0;
        }

        /* Payment Deadline Info */
        .payment-deadline-info {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: #FEF3C7;
            border: 1px solid #FDE68A;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #92400E;
            margin-top: 8px;
        }

        .payment-deadline-info svg {
            flex-shrink: 0;
        }

        .payment-deadline-info.expired {
            background: #FEE2E2;
            border-color: #FCA5A5;
            color: #991B1B;
        }

        .payment-deadline-info .timer-display {
            color: #D97706;
            font-size: 13px;
        }

        .payment-deadline-info.expired .timer-display {
            color: #DC2626;
        }

        /* Upload Payment Button */
        .btn-upload-payment {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 16px;
            background: #F97316;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
        }

        .btn-upload-payment:hover {
            background: #EA580C;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .btn-upload-payment svg {
            flex-shrink: 0;
        }

        /* Payment Status Info (Waiting for admin) */
        .payment-status-info {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            background: #DBEAFE;
            border: 1px solid #BFDBFE;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #1E40AF;
            margin-top: 8px;
        }

        .payment-status-info.waiting {
            background: #DBEAFE;
            border-color: #BFDBFE;
            color: #1E40AF;
        }

        .payment-status-info.cancelled {
            background: #FEE2E2;
            border-color: #FCA5A5;
            color: #991B1B;
        }

        .payment-status-info svg {
            flex-shrink: 0;
        }

        /* Arrow - Clickable for view details */
        .order-arrow-simple {
            flex-shrink: 0;
            color: #9CA3AF;
            transition: all 0.2s;
            cursor: pointer;
            padding: 8px;
            margin: -8px;
            border-radius: 8px;
        }

        .order-arrow-simple:hover {
            color: #F97316;
            background: #FFF7ED;
            transform: translateX(4px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .orders-bulk-header {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }
            
            .bulk-actions {
                justify-content: stretch;
            }
            
            .btn-bulk-action {
                flex: 1;
            }
            
            .order-card-simple {
                padding: 16px;
            }
            
            .order-info-simple {
                gap: 12px;
            }
            
            .order-image-simple {
                width: 60px;
                height: 60px;
            }
            
            .order-id-simple {
                font-size: 14px;
            }
            
            .order-description-simple {
                font-size: 13px;
            }
            
            .order-price-simple {
                font-size: 14px;
            }
            
            .btn-upload-payment {
                font-size: 13px;
                padding: 10px 14px;
            }
            
            .payment-deadline-info {
                font-size: 11px;
            }
        }
        
        /* Upload Modal Styles */
        .upload-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.75);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            padding: 20px;
            overflow-y: auto;
        }
        
        .upload-modal.active {
            display: flex;
        }
        
        .upload-modal-content {
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .upload-modal-header {
            padding: 24px 24px 16px;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .upload-modal-header h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
        }
        
        .modal-close {
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: #6B7280;
            transition: all 0.2s;
            border-radius: 6px;
        }
        
        .modal-close:hover {
            background: #F3F4F6;
            color: #1a1a1a;
        }
        
        .upload-modal-body {
            padding: 24px;
        }
        
        .modal-order-info {
            background: #F9FAFB;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
        
        .modal-order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .modal-order-item:last-child {
            margin-bottom: 0;
        }
        
        .modal-order-label {
            font-size: 14px;
            color: #6B7280;
            font-weight: 500;
        }
        
        .modal-order-value {
            font-size: 14px;
            color: #1a1a1a;
            font-weight: 700;
        }
        
        .payment-guide-compact {
            background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
            border: 2px solid #F59E0B;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
        
        .payment-guide-header-compact {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            color: #92400E;
        }
        
        .payment-guide-header-compact strong {
            font-size: 14px;
            font-weight: 700;
        }
        
        .payment-guide-list-compact {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .payment-guide-list-compact li {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 13px;
            color: #92400E;
        }
        
        .payment-guide-list-compact li:last-child {
            margin-bottom: 0;
        }
        
        .payment-guide-list-compact li svg {
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .file-upload-wrapper-modal {
            border: 3px dashed #D1D5DB;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 20px;
            transition: all 0.3s;
            cursor: pointer;
            background: #F9FAFB;
            position: relative;
        }
        
        .file-upload-wrapper-modal:hover {
            border-color: #D97706;
            background: #FEF3C7;
        }
        
        .file-upload-content-modal {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .file-upload-icon-modal {
            width: 48px;
            height: 48px;
            color: #D97706;
            margin-bottom: 12px;
        }
        
        .file-upload-text-modal {
            font-size: 15px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 4px;
        }
        
        .file-upload-hint-modal {
            font-size: 13px;
            color: #6B7280;
        }
        
        .payment-proof-input-hidden {
            display: none;
        }
        
        .image-preview-modal {
            display: none;
            flex-direction: column;
            align-items: center;
        }
        
        .image-preview-modal img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 16px;
        }
        
        .btn-change-photo-modal {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #F3F4F6;
            color: #1a1a1a;
            border: 2px solid #D1D5DB;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-change-photo-modal:hover {
            background: #E5E7EB;
            border-color: #D97706;
            color: #D97706;
        }
        
        .upload-modal-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
        
        .btn-modal-cancel {
            flex: 1;
            padding: 14px;
            background: #F3F4F6;
            color: #1a1a1a;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-modal-cancel:hover {
            background: #E5E7EB;
        }
        
        .btn-modal-upload {
            flex: 2;
            padding: 14px;
            background: #D97706;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
        }
        
        .btn-modal-upload:hover:not(:disabled) {
            background: #B45309;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(217, 119, 6, 0.4);
        }
        
        .btn-modal-upload:disabled {
            background: #D1D5DB;
            cursor: not-allowed;
            opacity: 0.6;
            box-shadow: none;
        }
        
        body.modal-open {
            overflow: hidden;
        }
        
        @media (max-width: 640px) {
            .upload-modal-content {
                max-width: 100%;
                border-radius: 20px 20px 0 0;
                max-height: 95vh;
            }
            
            .upload-modal-header {
                padding: 20px 20px 12px;
            }
            
            .upload-modal-body {
                padding: 20px;
            }
            
            .file-upload-wrapper-modal {
                padding: 30px 15px;
            }
            
            .upload-modal-actions {
                flex-direction: column-reverse;
            }
        }
    </style>
    
    <script>
        function openUploadModal(orderId, totalAmount) {
            // Check apakah order sudah expired
            const orderCard = document.querySelector(`[data-order-id="${orderId}"]`);
            const deadlineInfo = orderCard ? orderCard.querySelector('.payment-deadline-info') : null;
            
            if (deadlineInfo && deadlineInfo.classList.contains('expired')) {
                toastError('Waktu pembayaran telah habis! Order ini tidak dapat diproses.');
                return;
            }
            
            document.getElementById('modalOrderId').textContent = '#' + orderId;
            document.getElementById('modalOrderTotal').textContent = 'Rp ' + totalAmount.toLocaleString('id-ID');
            document.getElementById('uploadOrderId').value = orderId;
            
            const modal = document.getElementById('uploadPaymentModal');
            modal.style.display = ''; // Remove inline style jika ada
            modal.classList.add('active');
            document.body.classList.add('modal-open');
            
            // Reset form
            document.getElementById('uploadPaymentForm').reset();
            document.getElementById('uploadContentModal').style.display = 'flex';
            document.getElementById('imagePreviewModal').style.display = 'none';
            document.getElementById('uploadSubmitBtn').disabled = true;
        }
        
        function closeUploadModal() {
            const modal = document.getElementById('uploadPaymentModal');
            modal.classList.remove('active');
            modal.style.display = 'none'; // Force hide
            document.body.classList.remove('modal-open');
            document.body.style.overflow = ''; // Reset overflow
            
            // Reset form state
            const form = document.getElementById('uploadPaymentForm');
            if (form) {
                form.reset();
            }
            
            // Reset preview
            document.getElementById('uploadContentModal').style.display = 'flex';
            document.getElementById('imagePreviewModal').style.display = 'none';
            document.getElementById('uploadSubmitBtn').disabled = true;
        }
        
        function previewPaymentImage(input) {
            const preview = document.getElementById('previewImage');
            const uploadContent = document.getElementById('uploadContentModal');
            const imagePreview = document.getElementById('imagePreviewModal');
            const uploadBtn = document.getElementById('uploadSubmitBtn');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    toastError('Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan!');
                    input.value = '';
                    return;
                }
                
                // Validate file size (5MB max)
                const maxSize = 5 * 1024 * 1024; // 5242880 bytes
                
                if (file.size > maxSize) {
                    const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
                    toastError('Ukuran file maksimal 5MB! File Anda: ' + fileSizeMB + ' MB');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    uploadContent.style.display = 'none';
                    imagePreview.style.display = 'flex';
                    uploadBtn.disabled = false;
                    
                    // Show success toast
                    toastSuccess('File berhasil dipilih! Siap untuk diupload.');
                };
                reader.readAsDataURL(file);
            } else {
            }
        }
        
        function handleUploadSubmit(event) {
            
            // Prevent default form submission
            event.preventDefault();
            
            const form = document.getElementById('uploadPaymentForm');
            const fileInput = document.getElementById('paymentProofInput');
            const orderId = document.getElementById('uploadOrderId').value;
            
            // Validate file
            if (!fileInput.files || fileInput.files.length === 0) {
                toastError('Silakan pilih file terlebih dahulu!');
                return false;
            }
            
            const selectedFile = fileInput.files[0];
            
            // Validate file size (max 5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes = 5242880 bytes
            const fileSize = selectedFile.size;
            const fileSizeMB = (fileSize / 1024 / 1024).toFixed(2);
            
            if (fileSize > maxSize) {
                toastError('Ukuran file terlalu besar! Maksimal 5MB. File Anda: ' + fileSizeMB + ' MB');
                return false;
            }
            
            // Disable button and show loading
            const submitBtn = document.getElementById('uploadSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10"></circle></svg> Mengupload...';
            
            // Show loading overlay
            if (typeof showLoadingOverlay === 'function') {
                showLoadingOverlay();
            }
            
            // Show toast notification
            toastInfo('Sedang mengupload bukti pembayaran...', '', 0);
            
            // Create FormData object
            const formData = new FormData();
            formData.append('upload_payment', '1');
            formData.append('order_id', orderId);
            formData.append('payment_proof', fileInput.files[0]);
            
            // Send using Fetch API
            fetch('orders.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                
                if (response.ok || response.redirected) {
                    window.location.href = 'orders.php';
                } else {
                    return response.text().then(text => {
                        throw new Error('Upload failed');
                    });
                }
            })
            .catch(error => {
                toastError('Gagal mengupload! Silakan coba lagi.');
                
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg> Upload Bukti Pembayaran';
            });
        }
        
        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('uploadPaymentModal');
            if (event.target === modal) {
                closeUploadModal();
            }
        });
        
        // Add spin animation for loading
        const style = document.createElement('style');
        style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    </script>
    
    <?php 
    // Display flash messages from session AFTER all scripts loaded
    display_message(); 
    ?>
    
    

    <?php include '../includes/footer.php'; ?>
    
