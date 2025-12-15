<?php
/**
 * Halaman Notifikasi Customer
 * Menampilkan semua notifikasi terkait pesanan dengan filter dan aksi
 * RetroLoved E-Commerce System
 */

// Mulai session
session_start();

// Include koneksi database
require_once '../config/database.php';

// Validasi: Hanya customer yang bisa akses halaman ini
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header('Location: ../index.php');
    exit();
}

// Ambil ID user yang sedang login
$user_id = $_SESSION['user_id'];
$base_url = '../';

// ===== PROSES HAPUS NOTIFIKASI =====
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $notification_id = escape($_GET['delete']);
    // Hapus notifikasi berdasarkan ID dan pastikan milik user ini
    query("DELETE FROM notifications WHERE notification_id = '$notification_id' AND user_id = '$user_id'");
    // Redirect kembali dengan mempertahankan filter
    header('Location: notifications.php' . (isset($_GET['filter']) ? '?filter=' . $_GET['filter'] : ''));
    exit();
}

// ===== PROSES HAPUS SEMUA NOTIFIKASI =====
if(isset($_GET['delete_all'])) {
    $filter = isset($_GET['filter']) ? escape($_GET['filter']) : '';
    // Jika ada filter tertentu, hapus hanya notifikasi dengan type tersebut
    if($filter && $filter != 'all') {
        query("DELETE FROM notifications WHERE user_id = '$user_id' AND type = '$filter'");
    } else {
        // Hapus semua notifikasi user
        query("DELETE FROM notifications WHERE user_id = '$user_id'");
    }
    header('Location: notifications.php' . ($filter ? '?filter=' . $filter : ''));
    exit();
}

// ===== PROSES TANDAI NOTIFIKASI SEBAGAI SUDAH DIBACA =====
if(isset($_GET['read']) && is_numeric($_GET['read'])) {
    $notification_id = escape($_GET['read']);
    // Update status is_read menjadi 1 (sudah dibaca)
    query("UPDATE notifications SET is_read = 1 WHERE notification_id = '$notification_id' AND user_id = '$user_id'");
    header('Location: notifications.php' . (isset($_GET['filter']) ? '?filter=' . $_GET['filter'] : ''));
    exit();
}

// ===== PROSES TANDAI SEMUA SEBAGAI SUDAH DIBACA =====
if(isset($_GET['read_all'])) {
    $filter = isset($_GET['filter']) ? escape($_GET['filter']) : '';
    // Jika ada filter tertentu, tandai hanya notifikasi dengan type tersebut
    if($filter && $filter != 'all') {
        query("UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id' AND type = '$filter'");
    } else {
        // Tandai semua notifikasi user sebagai sudah dibaca
        query("UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id'");
    }
    header('Location: notifications.php' . ($filter ? '?filter=' . $filter : ''));
    exit();
}

// Get filter
$filter = isset($_GET['filter']) ? escape($_GET['filter']) : 'all';

// Build query based on filter
$notifications_query = "SELECT * FROM notifications WHERE user_id = '$user_id'";
if($filter != 'all') {
    $notifications_query .= " AND type = '$filter'";
}
$notifications_query .= " ORDER BY created_at DESC";
$notifications = query($notifications_query);

// Count unread
$unread_count_query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = '$user_id' AND is_read = 0";
if($filter != 'all') {
    $unread_count_query .= " AND type = '$filter'";
}
// Hitung notifikasi yang belum dibaca
$unread_result = query($unread_count_query);
$unread_count = $unread_result ? mysqli_fetch_assoc($unread_result)['count'] : 0;

// Hitung notifikasi berdasarkan tipe untuk badge filter
$count_all_result = query("SELECT COUNT(*) as count FROM notifications WHERE user_id = '$user_id'");
$count_all = $count_all_result ? mysqli_fetch_assoc($count_all_result)['count'] : 0;

$count_pending_result = query("SELECT COUNT(*) as count FROM notifications WHERE user_id = '$user_id' AND type = 'order_pending'");
$count_pending = $count_pending_result ? mysqli_fetch_assoc($count_pending_result)['count'] : 0;

$count_confirmed_result = query("SELECT COUNT(*) as count FROM notifications WHERE user_id = '$user_id' AND type = 'order_confirmed'");
$count_confirmed = $count_confirmed_result ? mysqli_fetch_assoc($count_confirmed_result)['count'] : 0;

$count_shipped_result = query("SELECT COUNT(*) as count FROM notifications WHERE user_id = '$user_id' AND type = 'order_shipped'");
$count_shipped = $count_shipped_result ? mysqli_fetch_assoc($count_shipped_result)['count'] : 0;

$count_delivered_result = query("SELECT COUNT(*) as count FROM notifications WHERE user_id = '$user_id' AND type = 'order_delivered'");
$count_delivered = $count_delivered_result ? mysqli_fetch_assoc($count_delivered_result)['count'] : 0;

$count_cancelled_result = query("SELECT COUNT(*) as count FROM notifications WHERE user_id = '$user_id' AND type = 'order_cancelled'");
$count_cancelled = $count_cancelled_result ? mysqli_fetch_assoc($count_cancelled_result)['count'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - RetroLoved</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/toast.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/performance.css">
    <style>
        body {
            background: white !important;
        }
        
        .notifications-wrapper {
            min-height: calc(100vh - 80px);
            background: #F9FAFB;
            padding: 40px 0;
        }
        
        .notifications-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .notifications-header h1 {
            font-size: 32px;
            color: #1F2937;
        }
        
        .mark-all-read {
            padding: 10px 20px;
            background: #D97706;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .mark-all-read:hover {
            background: #B45309;
            transform: translateY(-2px);
        }
        
        .notification-item {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .notification-item.unread {
            background: #FEF3C7;
            border-left-color: #D97706;
        }
        
        .notification-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
        }
        
        .notification-icon.success {
            background: #D1FAE5;
            color: #059669;
        }
        
        .notification-icon.warning {
            background: #FEF3C7;
            color: #D97706;
        }
        
        .notification-icon.danger {
            background: #FEE2E2;
            color: #DC2626;
        }
        
        .notification-icon.info {
            background: #DBEAFE;
            color: #2563EB;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-size: 16px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 4px;
        }
        
        .notification-message {
            font-size: 14px;
            color: #6B7280;
            line-height: 1.5;
        }
        
        .notification-time {
            font-size: 12px;
            color: #9CA3AF;
            margin-top: 8px;
        }
        
        .notification-actions {
            display: flex;
            gap: 10px;
            margin-top: 12px;
        }
        
        .notification-actions a {
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-view {
            background: #D97706;
            color: white;
        }
        
        .btn-view:hover {
            background: #B45309;
        }
        
        .btn-mark-read {
            background: #E5E7EB;
            color: #374151;
        }
        
        .btn-mark-read:hover {
            background: #D1D5DB;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .empty-state svg {
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 20px;
            color: #1F2937;
            margin-bottom: 8px;
        }
        
        .empty-state p {
            color: #6B7280;
        }
        
        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
            overflow-x: auto;
            padding-bottom: 10px;
            flex-wrap: wrap; /* Allow wrapping instead of scroll */
        }
        
        .filter-tab {
            padding: 10px 20px;
            background: white;
            border: 2px solid #E5E7EB;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #6B7280;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0; /* Prevent shrinking on wrap */
        }
        
        .filter-tab:hover {
            border-color: #D97706;
            color: #D97706;
            transform: translateY(-2px);
        }
        
        .filter-tab.active {
            background: #D97706;
            border-color: #D97706;
            color: white;
        }
        
        .filter-badge {
            background: rgba(0,0,0,0.1);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .filter-tab.active .filter-badge {
            background: rgba(255,255,255,0.2);
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-delete-all {
            padding: 10px 20px;
            background: #DC2626;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .btn-delete-all:hover {
            background: #B91C1C;
            transform: translateY(-2px);
        }
        
        .btn-delete {
            background: #DC2626;
            color: white;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
        }
        
        .btn-delete:hover {
            background: #B91C1C;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .notifications-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .action-buttons {
                width: 100%;
            }
            
            .mark-all-read,
            .btn-delete-all {
                width: 100%;
                text-align: center;
            }
            
            .filter-tabs {
                gap: 8px;
            }
            
            .filter-tab {
                padding: 8px 16px;
                font-size: 13px;
            }
            
            .notification-actions {
                flex-direction: column;
            }
            
            .notification-actions a {
                text-align: center;
            }
        }
        
        /* Delete confirmation */
        .delete-confirm {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .delete-confirm.show {
            display: flex;
            opacity: 1;
            pointer-events: auto;
        }
        
        .delete-confirm-box {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 25px rgba(0,0,0,0.3);
        }
        
        .delete-confirm-box h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #1F2937;
        }
        
        .delete-confirm-box p {
            color: #6B7280;
            margin-bottom: 20px;
        }
        
        .delete-confirm-actions {
            display: flex;
            gap: 10px;
        }
        
        .delete-confirm-actions button,
        .delete-confirm-actions a {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn-cancel {
            background: #E5E7EB;
            color: #374151;
        }
        
        .btn-cancel:hover {
            background: #D1D5DB;
        }
        
        .btn-confirm-delete {
            background: #DC2626;
            color: white;
        }
        
        .btn-confirm-delete:hover {
            background: #B91C1C;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- BREADCRUMB -->
    <div class="breadcrumb-container">
        <div class="container">
            <nav class="breadcrumb">
                <a href="../index.php" class="breadcrumb-item">Beranda</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-item active">Notifikasi</span>
            </nav>
        </div>
    </div>
    
    <div class="notifications-wrapper">
    <div class="notifications-container">
        <div class="notifications-header">
            <div>
                <h1>Notifikasi</h1>
                <?php if($unread_count > 0): ?>
                    <p class="notifications-unread-count"><?php echo $unread_count; ?> notifikasi belum dibaca</p>
                <?php endif; ?>
            </div>
            <div class="action-buttons">
                <?php if($unread_count > 0): ?>
                    <a href="notifications.php?read_all=1<?php echo $filter != 'all' ? '&filter=' . $filter : ''; ?>" class="mark-all-read">Tandai Semua Dibaca</a>
                <?php endif; ?>
                <?php if(mysqli_num_rows($notifications) > 0): ?>
                    <button onclick="showDeleteConfirm('all')" class="btn-delete-all">Hapus Semua</button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="notifications.php?filter=all" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">
                Semua Notifikasi
                <?php if($count_all > 0): ?>
                    <span class="filter-badge"><?php echo $count_all; ?></span>
                <?php endif; ?>
            </a>
            <a href="notifications.php?filter=order_pending" class="filter-tab <?php echo $filter == 'order_pending' ? 'active' : ''; ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Pending
                <?php if($count_pending > 0): ?>
                    <span class="filter-badge"><?php echo $count_pending; ?></span>
                <?php endif; ?>
            </a>
            <a href="notifications.php?filter=order_confirmed" class="filter-tab <?php echo $filter == 'order_confirmed' ? 'active' : ''; ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Dikonfirmasi
                <?php if($count_confirmed > 0): ?>
                    <span class="filter-badge"><?php echo $count_confirmed; ?></span>
                <?php endif; ?>
            </a>
            <a href="notifications.php?filter=order_shipped" class="filter-tab <?php echo $filter == 'order_shipped' ? 'active' : ''; ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
                Dikirim
                <?php if($count_shipped > 0): ?>
                    <span class="filter-badge"><?php echo $count_shipped; ?></span>
                <?php endif; ?>
            </a>
            <a href="notifications.php?filter=order_delivered" class="filter-tab <?php echo $filter == 'order_delivered' ? 'active' : ''; ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                Diterima
                <?php if($count_delivered > 0): ?>
                    <span class="filter-badge"><?php echo $count_delivered; ?></span>
                <?php endif; ?>
            </a>
            <a href="notifications.php?filter=order_cancelled" class="filter-tab <?php echo $filter == 'order_cancelled' ? 'active' : ''; ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                Dibatalkan
                <?php if($count_cancelled > 0): ?>
                    <span class="filter-badge"><?php echo $count_cancelled; ?></span>
                <?php endif; ?>
            </a>
        </div>
        
        <?php if(mysqli_num_rows($notifications) > 0): ?>
            <?php while($notif = mysqli_fetch_assoc($notifications)): ?>
                <div class="notification-item <?php echo $notif['is_read'] == 0 ? 'unread' : ''; ?>">
                    <div class="notification-flex-wrapper">
                        <div class="notification-icon <?php 
                            if($notif['type'] == 'order_pending') echo 'warning';
                            elseif($notif['type'] == 'order_confirmed') echo 'success';
                            elseif($notif['type'] == 'order_shipped') echo 'info';
                            elseif($notif['type'] == 'order_delivered') echo 'success';
                            elseif($notif['type'] == 'order_cancelled') echo 'danger';
                            else echo 'warning';
                        ?>">
                            <?php if($notif['type'] == 'order_pending'): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            <?php elseif($notif['type'] == 'order_confirmed'): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            <?php elseif($notif['type'] == 'order_shipped'): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="1" y="3" width="15" height="13"></rect>
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                </svg>
                            <?php elseif($notif['type'] == 'order_delivered'): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                            <?php elseif($notif['type'] == 'order_cancelled'): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title"><?php echo $notif['title']; ?></div>
                            <div class="notification-message"><?php echo $notif['message']; ?></div>
                            <div class="notification-time"><?php echo time_ago($notif['created_at']); ?></div>
                            
                            <div class="notification-actions">
                                <?php if($notif['order_id']): ?>
                                    <a href="orders.php?notification_id=<?php echo $notif['notification_id']; ?><?php echo $filter != 'all' ? '&filter=' . $filter : ''; ?>" class="btn-view">Lihat Pesanan</a>
                                <?php endif; ?>
                                <?php if($notif['is_read'] == 0): ?>
                                    <a href="notifications.php?read=<?php echo $notif['notification_id']; ?><?php echo $filter != 'all' ? '&filter=' . $filter : ''; ?>" class="btn-mark-read">Tandai Dibaca</a>
                                <?php endif; ?>
                                <button onclick="showDeleteConfirm(<?php echo $notif['notification_id']; ?>)" class="btn-delete">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <h3>Tidak Ada Notifikasi</h3>
                <p>Anda belum memiliki notifikasi apapun.</p>
            </div>
        <?php endif; ?>
    </div>
    </div>
    
    <script src="<?php echo $base_url; ?>assets/js/toast.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/modal.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/lazy-load.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/script.js"></script>
    <script>
        function showDeleteConfirm(id) {
            let message = '';
            let deleteUrl = '';
            
            if(id === 'all') {
                message = 'Apakah Anda yakin ingin menghapus semua notifikasi <?php echo $filter != "all" ? "yang difilter" : ""; ?>? Tindakan ini tidak dapat dibatalkan.';
                deleteUrl = 'notifications.php?delete_all=1<?php echo $filter != "all" ? "&filter=" . $filter : ""; ?>';
            } else {
                message = 'Apakah Anda yakin ingin menghapus notifikasi ini? Tindakan ini tidak dapat dibatalkan.';
                deleteUrl = 'notifications.php?delete=' + id + '<?php echo $filter != "all" ? "&filter=" . $filter : ""; ?>';
            }
            
            confirmModal(message, function() {
                window.location.href = deleteUrl;
            }, null, {
                title: 'Hapus Notifikasi?',
                confirmText: 'Hapus',
                cancelText: 'Batal',
                iconType: 'warning'
            });
        }
    </script>
</body>
</html>
