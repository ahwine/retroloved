<!-- NAVBAR ATAS ADMIN -->
<header class="top-navbar">
    <div class="navbar-left">
        <!-- Menu Hamburger untuk Mobile -->
        <button class="hamburger-menu" onclick="toggleSidebar()" aria-label="Toggle Menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        <h1 class="page-title"><?php echo $page_title ?? 'Admin Panel'; ?></h1>
    </div>
    
    <div class="navbar-right">
        <div class="admin-profile">
            <button class="profile-btn" onclick="toggleAdminDropdown()">
                <div class="admin-avatar">
                    <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                </div>
                <span class="profile-name"><?php echo $_SESSION['full_name']; ?></span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            
            <div class="admin-dropdown" id="adminDropdown">
                <a href="../auth/logout.php" class="dropdown-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Overlay Sidebar untuk Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Bottom Navigation for Mobile -->
<nav class="mobile-bottom-nav">
    <div class="mobile-bottom-nav-container">
        <a href="dashboard.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>Dashboard</span>
        </a>
        
        <a href="orders.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' || basename($_SERVER['PHP_SELF']) == 'order-detail.php' ? 'active' : ''; ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <span>Pesanan</span>
            <?php
            // Hitung pesanan pending untuk badge
            $pending_count_result = query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'");
            $pending_count = $pending_count_result ? mysqli_fetch_assoc($pending_count_result)['count'] : 0;
            if($pending_count > 0):
            ?>
                <span class="nav-badge"><?php echo $pending_count; ?></span>
            <?php endif; ?>
        </a>
        
        <a href="products.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'product-add.php' || basename($_SERVER['PHP_SELF']) == 'product-edit.php' ? 'active' : ''; ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
            <span>Produk</span>
        </a>
        
        <a href="customers.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' || basename($_SERVER['PHP_SELF']) == 'customer-detail.php' ? 'active' : ''; ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span>Customer</span>
        </a>
        
        <a href="../index.php" class="mobile-nav-item" target="_blank">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                <polyline points="15 3 21 3 21 9"></polyline>
                <line x1="10" y1="14" x2="21" y2="3"></line>
            </svg>
            <span>Website</span>
        </a>
        
    </div>
</nav>

<script>
// Toggle dropdown profil admin
function toggleAdminDropdown() {
    const dropdown = document.getElementById('adminDropdown');
    dropdown.classList.toggle('show');
}

// Toggle sidebar untuk mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
    document.body.classList.toggle('sidebar-open');
}

// Tutup sidebar saat klik item navigasi di mobile
document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                toggleSidebar();
            }
        });
    });
});

// Tutup dropdown saat klik di luar area dropdown
window.onclick = function(event) {
    if (!event.target.matches('.profile-btn') && !event.target.closest('.profile-btn')) {
        const dropdown = document.getElementById('adminDropdown');
        if (dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
}
</script>
