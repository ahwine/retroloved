<!-- JavaScript Global Variables -->
<script>
    <?php if(isset($_SESSION['email'])): ?>
    // Save logged in user email to localStorage and global variable
    localStorage.setItem('lastUserEmail', '<?php echo $_SESSION['email']; ?>');
    window.userEmail = '<?php echo $_SESSION['email']; ?>';
    <?php endif; ?>
</script>

<!-- HEADER / NAVBAR -->
<header class="header">
    <div class="container">
        <div class="nav-wrapper">
            <!-- Mobile Menu Icon (Hamburger) - Left Side -->
            <button class="mobile-menu-icon" onclick="toggleMobileMenu()" aria-label="Toggle Menu">
                <svg width="24" height="20" viewBox="0 0 24 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <line x1="0" y1="1" x2="24" y2="1" stroke="#1F2937" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="0" y1="10" x2="24" y2="10" stroke="#1F2937" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="0" y1="19" x2="24" y2="19" stroke="#1F2937" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </button>
            
            <!-- Logo -->
            <div class="logo">
                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>index.php">
                    <h1>RetroLoved</h1>
                </a>
            </div>
            
            <!-- Center Menu (Desktop) -->
            <nav class="nav-menu desktop-nav" id="desktopNav">
                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>index.php" class="nav-link">Beranda</a>
                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>shop.php" class="nav-link">Belanja</a>
                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>index.php#about" class="nav-link">Tentang</a>
                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>how-it-works.php" class="nav-link">Cara Kerja</a>
                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>faq.php" class="nav-link">FAQ</a>
            </nav>
            
            <!-- Mobile Sidebar Menu -->
            <nav class="mobile-sidebar-menu" id="mobileSidebar">
                <div class="mobile-menu-header">
                    <button class="mobile-menu-close" onclick="toggleMobileMenu()" aria-label="Close Menu">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <line x1="18" y1="6" x2="6" y2="18" stroke="#1F2937" stroke-width="2" stroke-linecap="round"/>
                            <line x1="6" y1="6" x2="18" y2="18" stroke="#1F2937" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="mobile-menu-links">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>how-it-works.php" class="mobile-nav-link">
                        <span>Cara Kerja</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>faq.php" class="mobile-nav-link">
                        <span>FAQ</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>shipping-delivery.php" class="mobile-nav-link">
                        <span>Pengiriman & Delivery</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>size-guide.php" class="mobile-nav-link">
                        <span>Panduan Ukuran</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>terms-conditions.php" class="mobile-nav-link">
                        <span>Syarat & Ketentuan</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>privacy-policy.php" class="mobile-nav-link">
                        <span>Kebijakan Privasi</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </div>
            </nav>
            
            <!-- Right Actions -->
            <div class="nav-actions">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['role'] == 'customer'): ?>
                        <?php
                        // Get counts for customer
                        $user_id = $_SESSION['user_id'];
                        
                        // Ambil jumlah item di cart
                        $cart_result = query("SELECT COUNT(*) as count FROM cart WHERE user_id = '$user_id'");
                        $cart_count = $cart_result ? mysqli_fetch_assoc($cart_result)['count'] : 0;
                        
                        // Ambil jumlah notifikasi yang belum dibaca
                        $notif_count = get_unread_notifications_count($user_id);
                        
                        // Ambil foto profil user
                        $user_query = query("SELECT profile_picture FROM users WHERE user_id = '$user_id'");
                        $user_data = $user_query ? mysqli_fetch_assoc($user_query) : null;
                        $profile_picture = ($user_data && !empty($user_data['profile_picture'])) ? $user_data['profile_picture'] : null;
                        ?>
                        
                        <!-- Notification Icon -->
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>customer/notifications.php" class="nav-icon" title="Notifikasi">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <?php if($notif_count > 0): ?>
                                <span class="badge-count"><?php echo $notif_count; ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <!-- Cart Icon -->
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>customer/cart.php" class="nav-icon" title="Keranjang">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                            <?php if($cart_count > 0): ?>
                                <span class="badge-count"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <!-- User Profile with Dropdown -->
                        <div class="user-profile-wrapper">
                            <button class="user-profile" onclick="toggleProfileDropdown()">
                                <div class="user-avatar">
                                    <?php if(!empty($profile_picture) && file_exists((isset($base_url) ? $base_url : '') . 'assets/images/profiles/' . $profile_picture)): ?>
                                        <img src="<?php echo isset($base_url) ? $base_url : ''; ?>assets/images/profiles/<?php echo $profile_picture; ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                            <div class="profile-dropdown" id="profileDropdown">
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>customer/profile.php" class="dropdown-item">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    Pengaturan
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>customer/orders.php" class="dropdown-item">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                        <line x1="3" y1="6" x2="21" y2="6"></line>
                                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                                    </svg>
                                    Pesanan Saya
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>auth/logout.php" class="dropdown-item">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    Keluar
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Admin Button -->
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>admin/dashboard.php" class="btn btn-primary">Dashboard Admin</a>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Guest Buttons -->
                    <a href="javascript:void(0)" onclick="showLoginModal()" class="btn btn-secondary">Masuk</a>
                    <a href="javascript:void(0)" onclick="showRegisterModal()" class="btn btn-primary">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
