<?php
/**
 * Halaman Detail Customer - Admin Panel
 * Menampilkan profil customer, statistik pesanan, dan riwayat transaksi
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

// Validasi: Harus ada ID customer di parameter URL
if(!isset($_GET['id'])) {
    header('Location: customers.php');
    exit();
}

// Ambil dan bersihkan ID customer
$customer_id = escape($_GET['id']);

// Ambil data customer dari database
$customer_query = query("SELECT * FROM users WHERE user_id = '$customer_id' AND role = 'customer'");

// Jika customer tidak ditemukan, redirect ke halaman customers
if(mysqli_num_rows($customer_query) == 0) {
    header('Location: customers.php');
    exit();
}
$customer = mysqli_fetch_assoc($customer_query);

// Ambil statistik pesanan customer
$order_stats = mysqli_fetch_assoc(query("
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(total_amount), 0) as total_spent,
        COALESCE(AVG(total_amount), 0) as avg_order_value,
        MAX(created_at) as last_order_date,
        MIN(created_at) as first_order_date
    FROM orders 
    WHERE user_id = '$customer_id'
"));

// Ambil daftar alamat pengiriman customer
$shipping_addresses = query("
    SELECT * FROM shipping_addresses 
    WHERE user_id = '$customer_id' 
    ORDER BY is_default DESC, created_at DESC
");

// Get recent orders
$recent_orders = query("
    SELECT * FROM orders 
    WHERE user_id = '$customer_id' 
    ORDER BY created_at DESC 
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Detail - <?php echo $customer['full_name']; ?> - RetroLoved Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=5.4">
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php $page_title = "Customer Detail"; include 'includes/navbar.php'; ?>

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
                            <a href="customers.php" style="color: #6b7280; text-decoration: none; font-weight: 500; transition: color 0.2s;" onmouseover="this.style.color='#D97706'" onmouseout="this.style.color='#6b7280'">Customers</a>
                        </li>
                        <li style="color: #d1d5db;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </li>
                        <li style="color: #D97706; font-weight: 600;">
                            Customer Detail
                        </li>
                    </ol>
                </nav>
                
                <!-- Page Title -->
                <div style="margin-bottom: 24px;">
                    <h1 style="font-size: 32px; font-weight: 800; color: #1a1a1a; margin: 0 0 8px 0;">Customer Detail</h1>
                    <p style="font-size: 15px; color: #6b7280; margin: 0;">View complete customer information and order history</p>
                </div>
            
            <!-- Customer Profile Card -->
            <div class="content-card" style="margin-bottom: 24px;">
                <div style="padding: 32px;">
                    <div style="display: flex; align-items: flex-start; gap: 24px; flex-wrap: wrap;">
                        <!-- Avatar / Profile Picture -->
                        <?php if(!empty($customer['profile_picture'])): ?>
                            <div style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; flex-shrink: 0; border: 3px solid #e5e7eb;">
                                <img src="../assets/images/profiles/<?php echo $customer['profile_picture']; ?>" 
                                     alt="<?php echo $customer['full_name']; ?>" 
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        <?php else: ?>
                            <div style="width: 100px; height: 100px; border-radius: 50%; background: #10B981; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 40px; flex-shrink: 0;">
                                <?php echo strtoupper(substr($customer['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Info -->
                        <div style="flex: 1; min-width: 250px;">
                            <h2 style="margin: 0 0 8px 0; font-size: 28px; font-weight: 800; color: #1f2937;"><?php echo $customer['full_name']; ?></h2>
                            <p style="margin: 4px 0; color: #6b7280; font-size: 16px;"><?php echo $customer['email']; ?></p>
                            <p style="margin: 4px 0; color: #6b7280; font-size: 15px;">@<?php echo $customer['username']; ?></p>
                            <p style="margin: 12px 0 0 0; color: #9ca3af; font-size: 14px;">Member since <?php echo date('d F Y', strtotime($customer['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px;">
                <!-- Total Orders -->
                <div class="content-card">
                    <div style="padding: 24px;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: #DBEAFE; display: flex; align-items: center; justify-content: center;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                    <line x1="3" y1="6" x2="21" y2="6"></line>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 13px; color: #6b7280; font-weight: 600;">Total Pesanan</div>
                                <div style="font-size: 28px; font-weight: 800; color: #1f2937;"><?php echo $order_stats['total_orders']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Total Spent -->
                <div class="content-card">
                    <div style="padding: 24px;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: #D1FAE5; display: flex; align-items: center; justify-content: center;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 13px; color: #6b7280; font-weight: 600;">Total Spent</div>
                                <div style="font-size: 28px; font-weight: 800; color: #1f2937;">Rp <?php echo number_format($order_stats['total_spent'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Avg Order Value -->
                <div class="content-card">
                    <div style="padding: 24px;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: #FEF3C7; display: flex; align-items: center; justify-content: center;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2">
                                    <line x1="12" y1="20" x2="12" y2="10"></line>
                                    <line x1="18" y1="20" x2="18" y2="4"></line>
                                    <line x1="6" y1="20" x2="6" y2="16"></line>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 13px; color: #6b7280; font-weight: 600;">Avg Order</div>
                                <div style="font-size: 28px; font-weight: 800; color: #1f2937;">Rp <?php echo number_format($order_stats['avg_order_value'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Last Order -->
                <div class="content-card">
                    <div style="padding: 24px;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: #E0E7FF; display: flex; align-items: center; justify-content: center;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 13px; color: #6b7280; font-weight: 600;">Last Order</div>
                                <div style="font-size: 16px; font-weight: 700; color: #1f2937;">
                                    <?php echo $order_stats['last_order_date'] ? date('d M Y', strtotime($order_stats['last_order_date'])) : 'Never'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Information & Addresses -->
            <div class="content-card" style="margin-bottom: 24px;">
                <div class="card-header" style="background: #fff; border-bottom: 2px solid #e5e7eb; padding: 24px 32px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <div>
                            <h3 style="margin: 0; font-size: 22px; font-weight: 700; color: #1f2937;">Contact Information</h3>
                            <p style="margin: 4px 0 0 0; color: #6b7280; font-size: 14px;">Shipping addresses and contact details</p>
                        </div>
                    </div>
                </div>
                
                <div style="padding: 32px;">
                    <?php if(mysqli_num_rows($shipping_addresses) > 0): ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
                            <?php while($address = mysqli_fetch_assoc($shipping_addresses)): ?>
                                <div style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; background: #fafafa; position: relative;">
                                    <?php if($address['is_default']): ?>
                                        <span style="position: absolute; top: 12px; right: 12px; background: #10B981; color: white; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase;">Default</span>
                                    <?php endif; ?>
                                    
                                    <div style="margin-bottom: 16px;">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                            <span style="font-weight: 700; color: #1f2937; font-size: 16px;"><?php echo htmlspecialchars($address['recipient_name']); ?></span>
                                        </div>
                                        
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                            </svg>
                                            <span style="color: #4b5563; font-size: 14px; font-weight: 600;"><?php echo htmlspecialchars($address['phone']); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div style="padding-top: 12px; border-top: 1px solid #e5e7eb;">
                                        <div style="display: flex; align-items: flex-start; gap: 8px; margin-bottom: 8px;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" style="margin-top: 2px; flex-shrink: 0;">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                <circle cx="12" cy="10" r="3"></circle>
                                            </svg>
                                            <div>
                                                <p style="margin: 0; color: #374151; font-size: 14px; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($address['full_address'])); ?></p>
                                                <p style="margin: 8px 0 0 0; color: #6b7280; font-size: 13px; font-weight: 600;">
                                                    <?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['province']); ?> - <?php echo htmlspecialchars($address['postal_code']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: #6b7280;">
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 16px; opacity: 0.3;">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <p style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">No addresses saved</p>
                            <p style="font-size: 14px;">This customer hasn't added any shipping addresses yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Order History -->
            <div class="content-card">
                <div class="card-header" style="background: #fff; border-bottom: 2px solid #e5e7eb; padding: 24px 32px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                        </svg>
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <h3 style="margin: 0; font-size: 22px; font-weight: 700; color: #1f2937;">Order History</h3>
                                <span style="background: #f3f4f6; color: #6b7280; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-weight: 600;"><?php echo $order_stats['total_orders']; ?></span>
                            </div>
                            <p style="margin: 4px 0 0 0; color: #6b7280; font-size: 14px;">Complete order history for this customer</p>
                        </div>
                    </div>
                </div>
                
                <div class="table-container" style="padding: 0;">
                    <table class="data-table" style="margin: 0;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.8px;">Order ID</th>
                                <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.8px;">Date</th>
                                <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.8px;">Total</th>
                                <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.8px;">Payment Method</th>
                                <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.8px;">Status</th>
                                <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.8px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                                <tr style="border-bottom: 1px solid #e9ecef; transition: all 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
                                    <td style="padding: 20px 24px;">
                                        <span style="font-weight: 700; color: #2563EB; font-size: 14px;">#<?php echo $order['order_id']; ?></span>
                                    </td>
                                    <td style="padding: 20px 24px;">
                                        <span style="color: #6b7280; font-size: 14px;"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></span>
                                    </td>
                                    <td style="padding: 20px 24px;">
                                        <span style="font-weight: 700; color: #1f2937; font-size: 15px;">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
                                    </td>
                                    <td style="padding: 20px 24px;">
                                        <span style="color: #4b5563; font-size: 13px;"><?php echo $order['payment_method']; ?></span>
                                    </td>
                                    <td style="padding: 20px 24px;">
                                        <?php
                                        $status_colors = [
                                            'Pending' => ['bg' => '#FEF3C7', 'text' => '#92400E'],
                                            'Processing' => ['bg' => '#DBEAFE', 'text' => '#1E40AF'],
                                            'Shipped' => ['bg' => '#E0E7FF', 'text' => '#4338CA'],
                                            'Delivered' => ['bg' => '#D1FAE5', 'text' => '#065F46'],
                                            'Cancelled' => ['bg' => '#FEE2E2', 'text' => '#991B1B']
                                        ];
                                        $status_color = $status_colors[$order['status']] ?? ['bg' => '#F3F4F6', 'text' => '#6B7280'];
                                        ?>
                                        <span style="display: inline-block; padding: 6px 12px; background: <?php echo $status_color['bg']; ?>; color: <?php echo $status_color['text']; ?>; border-radius: 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 20px 24px;">
                                        <a href="order-detail.php?id=<?php echo $order['order_id']; ?>" 
                                           style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600; transition: all 0.2s;"
                                           onmouseover="this.style.background='#e5e7eb'" 
                                           onmouseout="this.style.background='#f3f4f6'">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            
                            <?php if($order_stats['total_orders'] == 0): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;">
                                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 16px; opacity: 0.3;">
                                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                            <line x1="3" y1="6" x2="21" y2="6"></line>
                                        </svg>
                                        <p style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">No orders yet</p>
                                        <p style="font-size: 14px;">This customer hasn't placed any orders.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        </div>
    </div>
</body>
</html>
