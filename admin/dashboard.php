<?php
/**
 * Dashboard Admin - Enhanced Version
 * Halaman utama panel admin dengan statistik lengkap dan insights
 * RetroLoved E-Commerce System
 */

session_start();

// Validasi: Harus login sebagai admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

// Include konfigurasi database
require_once '../config/database.php';

// ===== STATISTIK DASAR =====
// Hitung total produk aktif
$total_products_result = query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
$total_products = $total_products_result ? mysqli_fetch_assoc($total_products_result)['total'] : 0;

// Hitung total pesanan (kecuali yang dibatalkan)
$total_orders_result = query("SELECT COUNT(*) as total FROM orders WHERE status != 'Cancelled'");
$total_orders = $total_orders_result ? mysqli_fetch_assoc($total_orders_result)['total'] : 0;

// Hitung total customer
$total_customers_result = query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$total_customers = $total_customers_result ? mysqli_fetch_assoc($total_customers_result)['total'] : 0;

// Hitung pesanan pending
$pending_orders_result = query("SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
$pending_orders = $pending_orders_result ? mysqli_fetch_assoc($pending_orders_result)['total'] : 0;

// ===== REVENUE SUMMARY =====
// Revenue hari ini
$revenue_today = mysqli_fetch_assoc(query("SELECT COALESCE(SUM(total_amount), 0) as total 
                                           FROM orders 
                                           WHERE DATE(created_at) = CURDATE() 
                                           AND status NOT IN ('Cancelled')"))['total'];

// Revenue minggu ini
$revenue_week = mysqli_fetch_assoc(query("SELECT COALESCE(SUM(total_amount), 0) as total 
                                          FROM orders 
                                          WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
                                          AND status NOT IN ('Cancelled')"))['total'];

// Revenue bulan ini
$revenue_month = mysqli_fetch_assoc(query("SELECT COALESCE(SUM(total_amount), 0) as total 
                                           FROM orders 
                                           WHERE YEAR(created_at) = YEAR(CURDATE()) 
                                           AND MONTH(created_at) = MONTH(CURDATE())
                                           AND status NOT IN ('Cancelled')"))['total'];


// ===== RECENT ACTIVITY / TIMELINE =====
// Gabungkan orders dan products yang baru ditambahkan
$recent_activities = query("
    (SELECT 'order' as type, o.order_id as id, o.created_at as activity_date, 
            u.full_name as actor, o.status as detail, o.total_amount as amount
     FROM orders o 
     JOIN users u ON o.user_id = u.user_id 
     ORDER BY o.created_at DESC 
     LIMIT 10)
    UNION ALL
    (SELECT 'product' as type, p.product_id as id, p.created_at as activity_date,
            'Admin' as actor, p.product_name as detail, p.price as amount
     FROM products p 
     ORDER BY p.created_at DESC 
     LIMIT 5)
    ORDER BY activity_date DESC
    LIMIT 10
");

// ===== PESANAN TERBARU (tetap ada) =====
$recent_orders = query("SELECT o.*, u.full_name 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.user_id 
                        ORDER BY o.created_at DESC 
                        LIMIT 5");
?>

<?php $page_title = "Dashboard"; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - RetroLoved</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=5.4">
</head>
<body class="admin-body">
    <!-- SIDEBAR -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- MAIN CONTENT -->
    <div class="main-content">
        <?php include 'includes/navbar.php'; ?>
        
        <!-- CONTENT AREA -->
        <div class="content-area">
            <div class="content-wrapper">
                <!-- Welcome Card with Quick Actions -->
                <div class="welcome-card">
                    <div class="welcome-content">
                        <h2>Selamat Datang, <?php echo $_SESSION['full_name']; ?>!</h2>
                        <p>Ini adalah dashboard admin RetroLoved. Kelola produk dan pesanan Anda di sini.</p>
                        
                        <!-- Quick Action Buttons -->
                        <div class="quick-actions">
                            <a href="product-add.php" class="quick-btn quick-btn-primary">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                Tambah Produk
                            </a>
                            <a href="orders.php?status=Pending" class="quick-btn quick-btn-warning">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                Pending Orders (<?php echo $pending_orders; ?>)
                            </a>
                            <a href="products.php" class="quick-btn quick-btn-secondary">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                </svg>
                                Kelola Produk
                            </a>
                            <a href="customers.php" class="quick-btn quick-btn-info">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                </svg>
                                Lihat Customer
                            </a>
                        </div>
                    </div>
                    <div class="welcome-icon">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                        </div>
                        <div class="stat-number"><?php echo $total_products; ?></div>
                        <div class="stat-label">Total Produk Aktif</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                        </div>
                        <div class="stat-number"><?php echo $total_orders; ?></div>
                        <div class="stat-label">Total Pesanan Berhasil</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="stat-number"><?php echo $total_customers; ?></div>
                        <div class="stat-label">Total Customer</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="stat-number"><?php echo $pending_orders; ?></div>
                        <div class="stat-label">Pesanan Pending</div>
                    </div>
                </div>

                <!-- Revenue Summary -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                                <line x1="12" y1="20" x2="12" y2="10"></line>
                                <line x1="18" y1="20" x2="18" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="16"></line>
                            </svg>
                            Revenue Summary
                        </h3>
                        <span class="card-subtitle">Ringkasan pendapatan</span>
                    </div>
                    <div class="revenue-grid">
                        <div class="revenue-card">
                            <div class="revenue-label">Hari Ini</div>
                            <div class="revenue-amount">Rp <?php echo number_format($revenue_today, 0, ',', '.'); ?></div>
                            <div class="revenue-subtitle"><?php echo date('d M Y'); ?></div>
                        </div>
                        <div class="revenue-card">
                            <div class="revenue-label">Minggu Ini</div>
                            <div class="revenue-amount">Rp <?php echo number_format($revenue_week, 0, ',', '.'); ?></div>
                            <div class="revenue-subtitle">7 hari terakhir</div>
                        </div>
                        <div class="revenue-card">
                            <div class="revenue-label">Bulan Ini</div>
                            <div class="revenue-amount">Rp <?php echo number_format($revenue_month, 0, ',', '.'); ?></div>
                            <div class="revenue-subtitle"><?php echo date('F Y'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Activity Timeline & Recent Orders Grid -->
                <div class="dashboard-grid-2col">
                    <!-- Recent Activity Timeline -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                Recent Activity
                            </h3>
                            <span class="card-subtitle">Aktivitas terbaru</span>
                        </div>
                        <div class="activity-timeline">
                            <?php while($activity = mysqli_fetch_assoc($recent_activities)): ?>
                                <div class="activity-item">
                                    <div class="activity-icon activity-icon-<?php echo $activity['type']; ?>">
                                        <?php if($activity['type'] == 'order'): ?>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                            </svg>
                                        <?php else: ?>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text">
                                            <?php if($activity['type'] == 'order'): ?>
                                                <strong><?php echo htmlspecialchars($activity['actor']); ?></strong> membuat pesanan 
                                                <span class="activity-id">#<?php echo $activity['id']; ?></span>
                                                <span class="status-badge status-<?php echo strtolower($activity['detail']); ?>"><?php echo $activity['detail']; ?></span>
                                            <?php else: ?>
                                                Produk baru ditambahkan: <strong><?php echo htmlspecialchars($activity['detail']); ?></strong>
                                            <?php endif; ?>
                                        </div>
                                        <div class="activity-time"><?php echo timeAgo($activity['activity_date']); ?></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                <!-- Recent Orders -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Pesanan Terbaru</h3>
                        <a href="orders.php" class="btn-link">Lihat Semua</a>
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                                    <tr>
                                        <td data-label="Order ID"><strong>#<?php echo $order['order_id']; ?></strong></td>
                                        <td data-label="Customer"><?php echo $order['full_name']; ?></td>
                                        <td data-label="Total">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                        <td data-label="Status">
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
                                        </td>
                                        <td data-label="Date"><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                        <td data-label="Action">
                                            <a href="order-detail.php?id=<?php echo $order['order_id']; ?>" class="btn-action btn-view">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                </div><!-- End dashboard-grid-2col -->
            </div>
        </div>
    </div>


</body>
</html>

<?php
/**
 * Helper function: Time ago converter
 * Convert timestamp to "5 menit yang lalu", "2 jam yang lalu", etc.
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if($difference < 60) {
        return 'Baru saja';
    } elseif($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' menit yang lalu';
    } elseif($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' jam yang lalu';
    } elseif($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' hari yang lalu';
    } else {
        return date('d M Y, H:i', $timestamp);
    }
}
?>
