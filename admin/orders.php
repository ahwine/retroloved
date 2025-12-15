<?php
/**
 * Halaman Manajemen Pesanan - Admin Panel
 * Mengelola semua pesanan customer: view orders dan navigate ke detail
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

// Mark Orders page as visited - save to database for persistent tracking
$user_id = $_SESSION['user_id'];
query("INSERT INTO admin_page_visits (user_id, page_name, last_visit_at) 
       VALUES ('$user_id', 'orders', NOW()) 
       ON DUPLICATE KEY UPDATE last_visit_at = NOW()");

// BUILD FILTERS & SEARCH QUERY
$where_conditions = ["1=1"];
$filter_status = '';
$filter_date_from = '';
$filter_date_to = '';
$search_query = '';

// Status Filter
if(isset($_GET['status']) && !empty($_GET['status'])) {
    $filter_status = escape($_GET['status']);
    $where_conditions[] = "o.status = '$filter_status'";
}

// Date Range Filter
if(isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $filter_date_from = escape($_GET['date_from']);
    $where_conditions[] = "DATE(o.created_at) >= '$filter_date_from'";
}

if(isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $filter_date_to = escape($_GET['date_to']);
    $where_conditions[] = "DATE(o.created_at) <= '$filter_date_to'";
}

// Search (Order ID or Customer Name)
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = escape($_GET['search']);
    $where_conditions[] = "(o.order_id LIKE '%$search_query%' OR u.full_name LIKE '%$search_query%' OR o.customer_name LIKE '%$search_query%')";
}

$where_clause = implode(" AND ", $where_conditions);

// READ with Filters
$orders = query("SELECT o.*, u.full_name, u.email 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.user_id 
                 WHERE $where_clause
                 ORDER BY o.created_at DESC");

// Count total and filtered
$total_orders = mysqli_num_rows(query("SELECT order_id FROM orders"));
$filtered_orders = mysqli_num_rows($orders);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - RetroLoved Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=5.4">
    <link rel="stylesheet" href="../assets/css/toast.css">
</head>
<body class="admin-body">
    <script src="../assets/js/modal.js"></script>
    <script src="../assets/js/toast.js"></script>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php $page_title = "Orders"; include 'includes/navbar.php'; ?>

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
                        <li style="color: #D97706; font-weight: 600;">
                            Orders
                        </li>
                    </ol>
                </nav>
                
                <!-- Page Title -->
                <div style="margin-bottom: 24px;">
                    <h1 style="font-size: 32px; font-weight: 800; color: #1a1a1a; margin: 0 0 8px 0;">Manajemen Pesanan</h1>
                    <p style="font-size: 15px; color: #6b7280; margin: 0;">Lacak dan kelola pesanan customer</p>
                </div>
                
                <!-- Filters & Search Toolbar -->
                <div class="filter-section">
                <form method="GET" action="orders.php" class="filter-form">
                    <!-- Status Filter -->
                    <div class="filter-form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">Semua Status</option>
                            <option value="Pending" <?php echo ($filter_status == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Processing" <?php echo ($filter_status == 'Processing') ? 'selected' : ''; ?>>Diproses</option>
                            <option value="Shipped" <?php echo ($filter_status == 'Shipped') ? 'selected' : ''; ?>>Dikirim</option>
                            <option value="Delivered" <?php echo ($filter_status == 'Delivered') ? 'selected' : ''; ?>>Diterima</option>
                            <option value="Completed" <?php echo ($filter_status == 'Completed') ? 'selected' : ''; ?>>Selesai</option>
                            <option value="Cancelled" <?php echo ($filter_status == 'Cancelled') ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                    </div>
                    
                    <!-- Date From -->
                    <div class="filter-form-group">
                        <label>Date From</label>
                        <input type="date" name="date_from" value="<?php echo $filter_date_from; ?>">
                    </div>
                    
                    <!-- Date To -->
                    <div class="filter-form-group">
                        <label>Date To</label>
                        <input type="date" name="date_to" value="<?php echo $filter_date_to; ?>">
                    </div>
                    
                    <!-- Search -->
                    <div class="filter-form-group wide">
                        <label>Cari</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="ID Pesanan atau Nama Customer...">
                    </div>
                    
                    <!-- Actions -->
                    <div class="filter-actions">
                        <button type="submit">Apply Filters</button>
                        <a href="orders.php" class="btn-clear">Clear</a>
                    </div>
                </form>
                
                <!-- Filter Summary -->
                <?php if($filter_status || $filter_date_from || $filter_date_to || $search_query): ?>
                    <div class="filter-summary">
                        <strong>Menampilkan <?php echo $filtered_orders; ?></strong> dari <?php echo $total_orders; ?> pesanan
                        <?php if($filter_status): ?>
                            <span class="filter-tag">Status: <strong><?php echo $filter_status; ?></strong></span>
                        <?php endif; ?>
                        <?php if($filter_date_from || $filter_date_to): ?>
                            <span class="filter-tag">
                                Date: <strong><?php echo $filter_date_from ?: 'Start'; ?></strong> to <strong><?php echo $filter_date_to ?: 'End'; ?></strong>
                            </span>
                        <?php endif; ?>
                        <?php if($search_query): ?>
                            <span class="filter-tag">Pencarian: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Orders Table -->
            <div class="content-card">
                <div class="card-header">
                    <div class="header-with-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        <div class="header-text">
                            <h3>
                                All Orders
                                <span class="count-badge"><?php echo mysqli_num_rows($orders); ?></span>
                            </h3>
                            <p>Lihat dan kelola pesanan customer</p>
                        </div>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Pembayaran</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = mysqli_fetch_assoc($orders)): ?>
                                <tr>
                                    <td data-label="ID Pesanan">
                                        <strong style="color: #3B82F6;">#<?php echo $order['order_id']; ?></strong>
                                    </td>
                                    <td data-label="Customer">
                                        <div class="user-info">
                                            <div class="avatar-circle info">
                                                <?php echo strtoupper(substr($order['full_name'], 0, 1)); ?>
                                            </div>
                                            <div class="user-details">
                                                <div class="user-name"><?php echo $order['full_name']; ?></div>
                                                <div class="user-email"><?php echo $order['email']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Total">
                                        <strong>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong>
                                    </td>
                                    <td data-label="Pembayaran">
                                        <?php echo $order['payment_method']; ?>
                                    </td>
                                    <td data-label="Status">
                                        <?php
                                        $status = trim($order['status']);
                                        $status_colors = [
                                            'Pending' => ['bg' => '#FEF3C7', 'text' => '#D97706'],
                                            'Processing' => ['bg' => '#DBEAFE', 'text' => '#2563EB'],
                                            'Shipped' => ['bg' => '#E0E7FF', 'text' => '#6366F1'],
                                            'Delivered' => ['bg' => '#D1FAE5', 'text' => '#059669'],
                                            'Completed' => ['bg' => '#D1FAE5', 'text' => '#10B981'],
                                            'Cancelled' => ['bg' => '#FEE2E2', 'text' => '#DC2626']
                                        ];
                                        $color = $status_colors[$status] ?? ['bg' => '#F3F4F6', 'text' => '#6B7280'];
                                        ?>
                                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; background: <?php echo $color['bg']; ?>; color: <?php echo $color['text']; ?>;">
                                            <?php echo $status; ?>
                                        </span>
                                    </td>
                                    <td data-label="Date">
                                        <?php echo date('d M Y', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td data-label="Action">
                                        <div class="table-actions">
                                            <a href="order-detail.php?id=<?php echo $order['order_id']; ?>" class="btn-action btn-view">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                                View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            
                            <?php if(mysqli_num_rows($orders) == 0): ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="empty-state">
                                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                                            </svg>
                                            <h3>No Orders Yet</h3>
                                            <p>Orders will appear here when customers make purchases</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Mobile Card View for Orders -->
                <div class="mobile-card-list">
                    <?php 
                    mysqli_data_seek($orders, 0);
                    while($order = mysqli_fetch_assoc($orders)): 
                        $status = trim($order['status']);
                        $status_colors = [
                            'Pending' => ['bg' => '#FEF3C7', 'text' => '#D97706'],
                            'Processing' => ['bg' => '#DBEAFE', 'text' => '#2563EB'],
                            'Shipped' => ['bg' => '#E0E7FF', 'text' => '#6366F1'],
                            'Delivered' => ['bg' => '#D1FAE5', 'text' => '#059669'],
                            'Completed' => ['bg' => '#D1FAE5', 'text' => '#10B981'],
                            'Cancelled' => ['bg' => '#FEE2E2', 'text' => '#DC2626']
                        ];
                        $color = $status_colors[$status] ?? ['bg' => '#F3F4F6', 'text' => '#6B7280'];
                    ?>
                        <div class="mobile-card-item">
                            <div class="mobile-card-header">
                                <div class="avatar-circle info" style="width: 50px; height: 50px; font-size: 18px;">
                                    <?php echo strtoupper(substr($order['full_name'], 0, 1)); ?>
                                </div>
                                <div class="mobile-card-info">
                                    <div class="mobile-card-title">#<?php echo $order['order_id']; ?> - <?php echo $order['full_name']; ?></div>
                                    <div class="mobile-card-subtitle" style="font-weight: 700; color: #D97706; font-size: 15px;">
                                        Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                    </div>
                                    <div style="margin-top: 8px;">
                                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; background: <?php echo $color['bg']; ?>; color: <?php echo $color['text']; ?>;">
                                            <?php echo $status; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mobile-card-meta">
                                <div class="mobile-card-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                    </svg>
                                    <span><?php echo $order['payment_method']; ?></span>
                                </div>
                                <div class="mobile-card-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <span><?php echo date('d M Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="mobile-card-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo $order['email']; ?></span>
                                </div>
                            </div>
                            
                            <div class="mobile-card-actions">
                                <a href="order-detail.php?id=<?php echo $order['order_id']; ?>" 
                                   class="btn-action"
                                   style="background: #3B82F6; color: white; border: 1px solid #2563EB; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 13px; font-weight: 600; border-radius: 8px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <span>Lihat Detail</span>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <?php if(mysqli_num_rows($orders) == 0): ?>
                        <div style="text-align: center; padding: 60px 20px; color: #999;">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 24px; opacity: 0.3;">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                            <h3 style="font-size: 18px; font-weight: 700; color: #6B7280; margin-bottom: 8px;">No Orders Yet</h3>
                            <p style="color: #9CA3AF;">Orders will appear here when customers make purchases</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
