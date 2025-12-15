<?php
/**
 * Manajemen Pelanggan - Clean Redesign
 * Halaman untuk melihat daftar pelanggan dan statistik mereka
 * RetroLoved E-Commerce System
 */

session_start();

// Validasi: Harus login sebagai admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/database.php';

// Mark Customers page as visited - save to database for persistent tracking
$user_id = $_SESSION['user_id'];
query("INSERT INTO admin_page_visits (user_id, page_name, last_visit_at) 
       VALUES ('$user_id', 'customers', NOW()) 
       ON DUPLICATE KEY UPDATE last_visit_at = NOW()");

// Ambil semua data pelanggan dengan statistik pesanan, profile picture, dan status aktif
$customers = query("SELECT u.user_id, u.username, u.email, u.full_name, u.profile_picture, u.created_at, u.role, u.is_active,
                    COUNT(DISTINCT o.order_id) as total_orders,
                    COALESCE(SUM(o.total_amount), 0) as total_spent
                    FROM users u
                    LEFT JOIN orders o ON u.user_id = o.user_id
                    WHERE u.role = 'customer'
                    GROUP BY u.user_id
                    ORDER BY u.created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - RetroLoved Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=5.4">
    <link rel="stylesheet" href="../assets/css/toast.css">
    <style>
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge.active {
            background: #D1FAE5;
            color: #065F46;
        }
        .status-badge.blocked {
            background: #FEE2E2;
            color: #991B1B;
        }
        .status-badge svg {
            width: 12px;
            height: 12px;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .btn-block, .btn-unblock, .btn-email {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-block {
            background: #FEE2E2;
            color: #991B1B;
        }
        .btn-block:hover {
            background: #FCA5A5;
        }
        .btn-unblock {
            background: #D1FAE5;
            color: #065F46;
        }
        .btn-unblock:hover {
            background: #6EE7B7;
        }
        .btn-email {
            background: #DBEAFE;
            color: #1E40AF;
        }
        .btn-email:hover {
            background: #93C5FD;
        }
    </style>
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php $page_title = "Customers"; include 'includes/navbar.php'; ?>

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
                            Customers
                        </li>
                    </ol>
                </nav>
                
                <!-- Page Title -->
                <div style="margin-bottom: 24px;">
                    <h1 style="font-size: 32px; font-weight: 800; color: #1a1a1a; margin: 0 0 8px 0;">Customers</h1>
                    <p style="font-size: 15px; color: #6b7280; margin: 0;">Manage customer accounts and view their order history</p>
                </div>
                
                <!-- Customers Table -->
                <div class="content-card">
                <div class="card-header">
                    <div class="header-with-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <div class="header-text">
                            <h3>
                                All Customers
                                <span class="count-badge"><?php echo mysqli_num_rows($customers); ?></span>
                            </h3>
                            <p>Manage customer accounts and data</p>
                        </div>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Username</th>
                                <th>Status</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Tanggal Bergabung</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($customer = mysqli_fetch_assoc($customers)): ?>
                                <tr>
                                    <td data-label="ID">
                                        <strong>#<?php echo $customer['user_id']; ?></strong>
                                    </td>
                                    <td data-label="Customer">
                                        <a href="customer-detail.php?id=<?php echo $customer['user_id']; ?>" class="user-info">
                                            <div class="avatar-circle success">
                                                <?php if(!empty($customer['profile_picture']) && file_exists('../assets/images/profiles/' . $customer['profile_picture'])): ?>
                                                    <img src="../assets/images/profiles/<?php echo $customer['profile_picture']; ?>" 
                                                         alt="<?php echo $customer['full_name']; ?>"
                                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;"
                                                         onerror="this.style.display='none'; this.parentElement.innerHTML='<?php echo strtoupper(substr($customer['full_name'], 0, 1)); ?>';">
                                                <?php else: ?>
                                                    <?php echo strtoupper(substr($customer['full_name'], 0, 1)); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="user-details">
                                                <div class="user-name"><?php echo $customer['full_name']; ?></div>
                                                <div class="user-email"><?php echo $customer['email']; ?></div>
                                            </div>
                                        </a>
                                    </td>
                                    <td data-label="Username">
                                        @<?php echo $customer['username']; ?>
                                    </td>
                                    <td data-label="Status">
                                        <?php if($customer['is_active'] == 1): ?>
                                            <span class="status-badge active">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                                </svg>
                                                Active
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge blocked">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                                </svg>
                                                Blocked
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Orders">
                                        <div class="stat-badge">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                                            </svg>
                                            <?php echo $customer['total_orders']; ?>
                                        </div>
                                    </td>
                                    <td data-label="Total Spent">
                                        <strong>Rp <?php echo number_format($customer['total_spent'], 0, ',', '.'); ?></strong>
                                    </td>
                                    <td data-label="Tanggal Bergabung">
                                        <?php echo date('d M Y', strtotime($customer['created_at'])); ?>
                                    </td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <a href="customer-detail.php?id=<?php echo $customer['user_id']; ?>" class="btn-action btn-view">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                                View
                                            </a>
                                            
                                            <a href="mailto:<?php echo $customer['email']; ?>" class="btn-email" title="Email Customer">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                                    <polyline points="22,6 12,13 2,6"></polyline>
                                                </svg>
                                                Email
                                            </a>
                                            
                                            <?php if($customer['is_active'] == 1): ?>
                                                <button class="btn-block" onclick="toggleCustomerStatus(<?php echo $customer['user_id']; ?>, 'block', '<?php echo htmlspecialchars($customer['full_name']); ?>')" title="Block Customer">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                                                    </svg>
                                                    Block
                                                </button>
                                            <?php else: ?>
                                                <button class="btn-unblock" onclick="toggleCustomerStatus(<?php echo $customer['user_id']; ?>, 'unblock', '<?php echo htmlspecialchars($customer['full_name']); ?>')" title="Unblock Customer">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                                    </svg>
                                                    Unblock
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            
                            <?php if(mysqli_num_rows($customers) == 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="empty-state">
                                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="9" cy="7" r="4"></circle>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                            </svg>
                                            <h3>No Customers Yet</h3>
                                            <p>Customer data will appear here when they register</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Mobile Card View for Customers -->
                <div class="mobile-card-list">
                    <?php 
                    mysqli_data_seek($customers, 0);
                    while($customer = mysqli_fetch_assoc($customers)): 
                    ?>
                        <div class="mobile-card-item" data-customer-id="<?php echo $customer['user_id']; ?>">
                            <div class="mobile-card-header">
                                <div class="avatar-circle success" style="width: 50px; height: 50px; font-size: 18px;">
                                    <?php if(!empty($customer['profile_picture']) && file_exists('../assets/images/profiles/' . $customer['profile_picture'])): ?>
                                        <img src="../assets/images/profiles/<?php echo $customer['profile_picture']; ?>" 
                                             alt="<?php echo $customer['full_name']; ?>"
                                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;"
                                             onerror="this.style.display='none'; this.parentElement.innerHTML='<?php echo strtoupper(substr($customer['full_name'], 0, 1)); ?>';">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($customer['full_name'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="mobile-card-info">
                                    <div class="mobile-card-title"><?php echo $customer['full_name']; ?></div>
                                    <div class="mobile-card-subtitle" style="color: #6B7280;">
                                        @<?php echo $customer['username']; ?>
                                    </div>
                                    <div class="mobile-card-subtitle" style="font-size: 12px; color: #9CA3AF;">
                                        <?php echo $customer['email']; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mobile-card-meta">
                                <div class="mobile-card-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                        <line x1="3" y1="6" x2="21" y2="6"></line>
                                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                                    </svg>
                                    <span><strong><?php echo $customer['total_orders']; ?></strong> Orders</span>
                                </div>
                                <div class="mobile-card-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                    <span><strong>Rp <?php echo number_format($customer['total_spent'], 0, ',', '.'); ?></strong></span>
                                </div>
                                <div class="mobile-card-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <span>Bergabung <?php echo date('d M Y', strtotime($customer['created_at'])); ?></span>
                                </div>
                            </div>
                            
                            <div class="mobile-card-meta-item">
                                <?php if($customer['is_active'] == 1): ?>
                                    <span class="status-badge active">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge blocked">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg>
                                        Blocked
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mobile-card-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="customer-detail.php?id=<?php echo $customer['user_id']; ?>" 
                                   class="btn-action"
                                   style="background: #3B82F6; color: white; border: 1px solid #2563EB; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 13px; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <span>View</span>
                                </a>
                                
                                <a href="mailto:<?php echo $customer['email']; ?>" class="btn-email" style="padding: 8px 16px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                    Email
                                </a>
                                
                                <?php if($customer['is_active'] == 1): ?>
                                    <button class="btn-block" onclick="toggleCustomerStatus(<?php echo $customer['user_id']; ?>, 'block', '<?php echo htmlspecialchars($customer['full_name']); ?>')" style="padding: 8px 16px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                                        </svg>
                                        Block
                                    </button>
                                <?php else: ?>
                                    <button class="btn-unblock" onclick="toggleCustomerStatus(<?php echo $customer['user_id']; ?>, 'unblock', '<?php echo htmlspecialchars($customer['full_name']); ?>')" style="padding: 8px 16px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                        Unblock
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <?php if(mysqli_num_rows($customers) == 0): ?>
                        <div style="text-align: center; padding: 60px 20px; color: #999;">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 24px; opacity: 0.3;">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <h3 style="font-size: 18px; font-weight: 700; color: #6B7280; margin-bottom: 8px;">No Customers Yet</h3>
                            <p style="color: #9CA3AF;">Customer data will appear here when they register</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>
    
    <script src="../assets/js/toast.js"></script>
    <script src="../assets/js/modal.js"></script>
    <script>
        /**
         * Toggle Customer Status (Block/Unblock)
         */
        function toggleCustomerStatus(userId, action, customerName) {
            const actionText = action === 'block' ? 'memblokir' : 'unblock';
            const actionTextCap = action === 'block' ? 'Blokir' : 'Unblock';
            
            // Konfirmasi sebelum action menggunakan confirmModal
            confirmModal(
                `Apakah Anda yakin ingin ${actionText} customer "${customerName}"?`,
                function() {
                    // Lanjutkan dengan action - kirim request AJAX
                    executeToggleCustomerStatus(userId, action);
                },
                null,
                {
                    confirmText: `Ya, ${actionTextCap}`,
                    iconType: 'warning'
                }
            );
        }
        
        /**
         * Execute Toggle Customer Status (dipanggil setelah konfirmasi)
         */
        function executeToggleCustomerStatus(userId, action) {
            // Show loading
            const buttons = document.querySelectorAll(`button[onclick*="${userId}"]`);
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.style.opacity = '0.6';
                btn.style.cursor = 'not-allowed';
            });
            
            // Send request
            fetch('process-customer-action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastSuccess(data.message);
                    // Reload page after 1 second to show updated status
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    toastError(data.message || 'Terjadi kesalahan. Silakan coba lagi.');
                    // Re-enable buttons
                    buttons.forEach(btn => {
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        btn.style.cursor = 'pointer';
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastError('Terjadi kesalahan koneksi. Silakan coba lagi.');
                // Re-enable buttons
                buttons.forEach(btn => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                });
            });
        }
    </script>
</body>
</html>
