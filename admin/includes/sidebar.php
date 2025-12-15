<!-- SIDEBAR NAVIGASI ADMIN -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="min-width: 20px;">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
            RetroLoved
        </h2>
        <p>Panel Admin</p>
    </div>
    
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>Dashboard</span>
        </a>
        
        <a href="products.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
            <span>Produk</span>
        </a>
        
        <a href="orders.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <span>Pesanan</span>
            <?php
            // Cek kapan terakhir admin mengunjungi halaman Orders
            $visit_check = query("SELECT last_visit_at FROM admin_page_visits WHERE user_id = '{$_SESSION['user_id']}' AND page_name = 'orders'");
            $last_visit = mysqli_num_rows($visit_check) > 0 ? mysqli_fetch_assoc($visit_check)['last_visit_at'] : null;
            
            // Hitung pesanan baru (Pending) + pesanan dengan bukti pembayaran (Processing)
            if($last_visit) {
                // Hitung pesanan baru setelah kunjungan terakhir
                $new_orders_result = query("SELECT COUNT(*) as count FROM orders 
                    WHERE (status = 'Pending' AND created_at > '$last_visit') 
                    OR (status = 'Processing' AND payment_proof IS NOT NULL AND created_at > '$last_visit')");
                $new_orders_with_payment = $new_orders_result ? mysqli_fetch_assoc($new_orders_result)['count'] : 0;
            } else {
                // Kunjungan pertama - tampilkan semua pending + processing dengan payment
                $new_orders_result = query("SELECT COUNT(*) as count FROM orders 
                    WHERE status = 'Pending' 
                    OR (status = 'Processing' AND payment_proof IS NOT NULL)");
                $new_orders_with_payment = $new_orders_result ? mysqli_fetch_assoc($new_orders_result)['count'] : 0;
            }
            
            if($new_orders_with_payment > 0):
            ?>
                <span class="badge badge-orders"><?php echo $new_orders_with_payment; ?></span>
            <?php 
            endif;
            ?>
        </a>
        
        <a href="customers.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span>Customer</span>
            <?php
            // Cek kapan terakhir admin mengunjungi halaman Customers
            $visit_check = query("SELECT last_visit_at FROM admin_page_visits WHERE user_id = '{$_SESSION['user_id']}' AND page_name = 'customers'");
            $last_visit = mysqli_num_rows($visit_check) > 0 ? mysqli_fetch_assoc($visit_check)['last_visit_at'] : null;
            
            // Hitung customer baru yang terdaftar setelah kunjungan terakhir atau dalam 7 hari terakhir
            if($last_visit) {
                // Hitung customer yang terdaftar setelah kunjungan terakhir
                $new_customers_result = query("SELECT COUNT(*) as count FROM users WHERE role = 'customer' AND created_at > '$last_visit'");
                $new_customers = $new_customers_result ? mysqli_fetch_assoc($new_customers_result)['count'] : 0;
            } else {
                // Kunjungan pertama - tampilkan customer dari 7 hari terakhir
                $new_customers_result = query("SELECT COUNT(*) as count FROM users WHERE role = 'customer' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                $new_customers = $new_customers_result ? mysqli_fetch_assoc($new_customers_result)['count'] : 0;
            }
            
            if($new_customers > 0):
            ?>
                <span class="badge badge-customers"><?php echo $new_customers; ?></span>
            <?php 
            endif;
            ?>
        </a>
        
        <div style="margin: 20px 0; border-top: 1px solid #E5E7EB;"></div>
        
        <a href="../index.php" class="nav-item" target="_blank">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                <polyline points="15 3 21 3 21 9"></polyline>
                <line x1="10" y1="14" x2="21" y2="3"></line>
            </svg>
            <span>Lihat Website</span>
        </a>
    </nav>
</aside>
