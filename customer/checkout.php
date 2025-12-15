<?php
/**
 * Halaman Checkout (Pembayaran)
 * Menangani proses checkout baik dari cart maupun direct buy (Beli Sekarang)
 * RetroLoved E-Commerce System
 */

// Mulai session
session_start();

// Validasi: Hanya customer yang bisa akses halaman ini
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header('Location: ../index.php');
    exit();
}

// Include koneksi database
require_once '../config/database.php';
$base_url = '../';

// Ambil ID user yang sedang login
$user_id = $_SESSION['user_id'];
$error = '';

// Cek apakah ini direct checkout dari tombol "Beli Sekarang"
// Direct checkout = langsung checkout tanpa masuk cart
$is_direct_checkout = isset($_GET['direct']) && isset($_SESSION['direct_checkout_product']);

// ===== PROSES DIRECT CHECKOUT (Beli Sekarang) =====
if($is_direct_checkout) {
    // Ambil ID produk dari session yang disimpan saat klik "Beli Sekarang"
    $product_id = $_SESSION['direct_checkout_product'];
    
    // Query data produk langsung dari database
    $product_query = query("SELECT product_id, product_name, price, image_url, is_active, is_sold 
                           FROM products 
                           WHERE product_id = '$product_id'");
    
    $cart_data = [];
    $total = 0;
    
    // Jika produk ditemukan
    if(mysqli_num_rows($product_query) > 0) {
        $product = mysqli_fetch_assoc($product_query);
        
        // Validasi: Pastikan produk masih tersedia (aktif dan belum terjual)
        if($product['is_active'] == 0 || $product['is_sold'] == 1) {
            unset($_SESSION['direct_checkout_product']);
            set_message('error', 'Maaf, produk ini sudah tidak tersedia!');
            header('Location: ../shop.php');
            exit();
        }
        
        // Hitung total harga
        $price = floatval($product['price']);
        $product['price'] = $price;
        $total += $price;
        $cart_data[] = $product;
    } else {
        // Produk tidak ditemukan di database
        unset($_SESSION['direct_checkout_product']);
        set_message('error', 'Produk tidak ditemukan!');
        header('Location: ../shop.php');
        exit();
    }
    
} else {
    // ===== PROSES NORMAL CHECKOUT (dari Cart) =====
    // Ambil semua item dari cart customer dengan data produk terbaru
    $cart_items = query("SELECT c.cart_id, c.product_id,
                                p.product_name, p.price, p.image_url, p.is_active, p.is_sold
                         FROM cart c 
                         JOIN products p ON c.product_id = p.product_id 
                         WHERE c.user_id = '$user_id'
                         ORDER BY c.cart_id DESC");

    // Inisialisasi variabel untuk menghitung total dan validasi
    $total = 0;
    $cart_data = [];
    $has_unavailable = false;

    // Loop setiap item di cart
    while($item = mysqli_fetch_assoc($cart_items)) {
        // Validasi: Skip produk yang tidak aktif atau sudah terjual
        if($item['is_active'] == 0 || $item['is_sold'] == 1) {
            $has_unavailable = true;
            // Hapus otomatis dari cart karena sudah tidak tersedia
            query("DELETE FROM cart WHERE cart_id = '{$item['cart_id']}'");
            continue;
        }
        
        // Konversi harga ke float untuk perhitungan
        $price = floatval($item['price']);
        
        // Untuk barang preloved, quantity selalu 1 (tidak ada stok)
        $item['price'] = $price;
        
        // Tambahkan ke total
        $total += $price;
        $cart_data[] = $item;
    }

    // Jika ada produk yang tidak tersedia, redirect ke cart dengan pesan
    if($has_unavailable) {
        set_message('warning', 'Beberapa produk di cart Anda sudah tidak tersedia dan telah dihapus. Silakan cek kembali cart Anda.');
        header('Location: cart.php');
        exit();
    }

    // Jika cart kosong setelah validasi, redirect ke cart
    if(count($cart_data) == 0) {
        set_message('warning', 'Cart Anda kosong! Silakan tambahkan produk terlebih dahulu.');
        header('Location: cart.php');
        exit();
    }
}

// Ambil data user
$user_query_result = query("SELECT * FROM users WHERE user_id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query_result);

// Check if user data exists
if(!$user_data) {
    set_message('error', 'Data user tidak ditemukan!');
    header('Location: ../index.php');
    exit();
}

// Ensure user has required fields
if(empty($user_data['full_name'])) {
    $user_data['full_name'] = $user_data['username'] ?? 'User';
}
if(empty($user_data['email'])) {
    $user_data['email'] = '';
}

// Ambil alamat pengiriman user
$addresses_query = "SELECT * FROM shipping_addresses WHERE user_id = '$user_id' ORDER BY is_default DESC, created_at DESC";
$addresses = query($addresses_query);
$addresses_list = [];
$default_address = null;

while($addr = mysqli_fetch_assoc($addresses)) {
    $addresses_list[] = $addr;
    if($addr['is_default'] == 1) {
        $default_address = $addr;
    }
}

// Jika tidak ada alamat default tapi ada alamat, ambil yang pertama
if(!$default_address && count($addresses_list) > 0) {
    $default_address = $addresses_list[0];
}

// HANDLE AJAX ADD ADDRESS
if(isset($_POST['add_address_ajax'])) {
    header('Content-Type: application/json');
    
    $recipient_name = escape($_POST['recipient_name']);
    $phone = escape($_POST['phone']);
    $full_address = escape($_POST['full_address']);
    $city = escape($_POST['city']);
    $province = escape($_POST['province']);
    $postal_code = escape($_POST['postal_code']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    $latitude = escape($_POST['latitude']);
    $longitude = escape($_POST['longitude']);
    
    // Validate required fields
    if(empty($recipient_name) || empty($phone) || empty($full_address) || empty($city) || empty($province) || empty($postal_code)) {
        echo json_encode([
            'success' => false,
            'message' => 'Semua field harus diisi!'
        ]);
        exit();
    }
    
    // If this is set as default, remove default from other addresses
    if($is_default) {
        query("UPDATE shipping_addresses SET is_default = 0 WHERE user_id = '$user_id'");
    }
    
    // Insert new address
    $insert_query = "INSERT INTO shipping_addresses (user_id, recipient_name, phone, full_address, city, province, postal_code, latitude, longitude, is_default) 
                    VALUES ('$user_id', '$recipient_name', '$phone', '$full_address', '$city', '$province', '$postal_code', '$latitude', '$longitude', '$is_default')";
    
    if(query($insert_query)) {
        echo json_encode([
            'success' => true,
            'message' => 'Alamat berhasil ditambahkan!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menambahkan alamat. Silakan coba lagi.'
        ]);
    }
    exit();
}

// PROSES CHECKOUT
if(isset($_POST['place_order'])) {
    // PERBAIKAN: Cek apakah ini direct checkout atau dari cart
    $is_direct = isset($_POST['is_direct_checkout']) && $_POST['is_direct_checkout'] == '1';
    
    if(!$is_direct) {
        // Normal checkout - RE-CHECK: Cart masih ada isinya (prevent race condition)
        $recheck_cart = query("SELECT COUNT(*) as count FROM cart c 
                              JOIN products p ON c.product_id = p.product_id 
                              WHERE c.user_id = '$user_id' 
                              AND p.is_active = 1 AND p.is_sold = 0");
        $cart_count_result = mysqli_fetch_assoc($recheck_cart);
        $cart_count = $cart_count_result ? $cart_count_result['count'] : 0;
        
        if($cart_count == 0) {
            set_message('error', 'Cart Anda kosong atau produk sudah tidak tersedia! Silakan tambahkan produk terlebih dahulu.');
            header('Location: cart.php');
            exit();
        }
    } else {
        // Direct checkout - validasi produk dari session
        if(!isset($_SESSION['direct_checkout_product'])) {
            set_message('error', 'Sesi checkout telah berakhir. Silakan coba lagi.');
            header('Location: ../shop.php');
            exit();
        }
    }
    
    // Get customer info
    $customer_name = isset($_POST['customer_name']) ? escape($_POST['customer_name']) : '';
    $customer_email = isset($_POST['customer_email']) ? escape($_POST['customer_email']) : '';
    
    // Get shipping info from selected address or manual input
    if(isset($_POST['address_id']) && !empty($_POST['address_id'])) {
        // Use saved address
        $address_id = escape($_POST['address_id']);
        $addr_query = query("SELECT * FROM shipping_addresses WHERE address_id = '$address_id' AND user_id = '$user_id'");
        $selected_addr = mysqli_fetch_assoc($addr_query);
        
        if($selected_addr) {
            $shipping_address = $selected_addr['full_address'] . ', ' . $selected_addr['city'] . ', ' . $selected_addr['province'] . ' ' . $selected_addr['postal_code'];
            $phone = $selected_addr['phone'];
        } else {
            $error = "Alamat tidak ditemukan!";
            $shipping_address = '';
            $phone = '';
        }
    } else {
        // Use manual input (jika ada)
        $shipping_address = isset($_POST['shipping_address']) ? escape($_POST['shipping_address']) : '';
        $phone = isset($_POST['phone']) ? escape($_POST['phone']) : '';
    }
    
    $payment_method = escape($_POST['payment_method']);
    
    // Get shipping data
    $shipping_service_id = isset($_POST['shipping_service_id']) ? escape($_POST['shipping_service_id']) : null;
    $shipping_cost = isset($_POST['shipping_cost']) ? floatval($_POST['shipping_cost']) : 0;
    $subtotal = isset($_POST['subtotal']) ? floatval($_POST['subtotal']) : $total;
    $total_amount = $subtotal + $shipping_cost;
    
    // Validasi input
    if(empty($customer_name) || empty($customer_email)) {
        $error = "Nama lengkap dan email harus diisi!";
    } elseif(empty($shipping_address) || empty($phone) || empty($payment_method)) {
        $error = "Semua field harus diisi!";
    } elseif(!$shipping_service_id) {
        $error = "Mohon pilih layanan pengiriman!";
    } elseif(strlen($shipping_address) < 10) {
        $error = "Alamat pengiriman terlalu pendek! Minimal 10 karakter.";
    } elseif($total <= 0) {
        $error = "Total amount tidak valid!";
    } else {
        // Insert order dengan customer info, shipping, dan payment deadline (5 menit dari sekarang)
        $order_query = "INSERT INTO orders (user_id, customer_name, customer_email, subtotal, shipping_cost, total_amount, shipping_service_id, shipping_address, phone, payment_method, status, current_status_detail, payment_deadline) 
                        VALUES ('$user_id', '$customer_name', '$customer_email', '$subtotal', '$shipping_cost', '$total_amount', '$shipping_service_id', '$shipping_address', '$phone', '$payment_method', 'Pending', 'order_placed', DATE_ADD(NOW(), INTERVAL 5 MINUTE))";
        
        if(query($order_query)) {
            $order_id = mysqli_insert_id($GLOBALS['conn']);
            
            // Insert order items dan tandai produk sebagai SOLD
            // RACE CONDITION FIX: Gunakan atomic UPDATE untuk memastikan hanya 1 customer berhasil
            $success_items = 0;
            foreach($cart_data as $item) {
                $product_id = $item['product_id'];
                $quantity = 1;
                $price = $item['price'];
                
                // ATOMIC OPERATION: Update is_sold dengan WHERE clause untuk lock produk
                // Hanya akan berhasil jika is_sold masih 0 (mencegah race condition)
                $update_result = query("UPDATE products SET is_sold = 1 
                                       WHERE product_id = '$product_id' 
                                       AND is_sold = 0 
                                       AND is_active = 1");
                
                // Cek berapa baris yang ter-update (mysqli_affected_rows)
                if(mysqli_affected_rows($GLOBALS['conn']) > 0) {
                    // Produk berhasil di-mark sebagai sold, lanjutkan insert order item
                    query("INSERT INTO order_items (order_id, product_id, quantity, price) 
                           VALUES ('$order_id', '$product_id', '$quantity', '$price')");
                    
                    $success_items++;
                }
                // Jika affected_rows = 0, berarti produk sudah terjual oleh customer lain (skip)
            }
            
            // Jika tidak ada item yang berhasil, rollback order
            if($success_items == 0) {
                query("DELETE FROM orders WHERE order_id = '$order_id'");
                
                // PERBAIKAN: Clear session jika direct checkout
                if($is_direct) {
                    unset($_SESSION['direct_checkout_product']);
                }
                
                set_message('error', 'Semua produk di cart Anda sudah tidak tersedia. Order dibatalkan.');
                header('Location: cart.php');
                exit();
            }
            
            // PERBAIKAN: Hanya hapus cart jika bukan direct checkout
            if(!$is_direct) {
                // Hapus cart untuk normal checkout
                query("DELETE FROM cart WHERE user_id = '$user_id'");
            } else {
                // Clear session untuk direct checkout
                unset($_SESSION['direct_checkout_product']);
            }
            
            // Create notification for customer
            create_notification(
                $user_id, 
                $order_id, 
                'order', 
                'Pesanan Berhasil Dibuat',
                'Pesanan #' . $order_id . ' berhasil dibuat. Total: Rp ' . number_format($total, 0, ',', '.') . '. Silakan upload bukti pembayaran.'
            );
            
            // Redirect ke halaman orders dengan success message
            set_message('success', 'Pesanan berhasil dibuat! Silakan upload bukti pembayaran.');
            header('Location: orders.php?success=order_placed');
            exit();
        } else {
            $error = "Gagal membuat pesanan. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - RetroLoved</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/toast.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/tracking.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- BREADCRUMB -->
    <div class="breadcrumb-container">
        <div class="container">
            <nav class="breadcrumb">
                <a href="../index.php" class="breadcrumb-item">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="cart.php" class="breadcrumb-item">Cart</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-item active">Checkout</span>
            </nav>
        </div>
    </div>

    <!-- CHECKOUT SECTION -->
    <section class="checkout-section">
        <div class="container">
            <h2 class="section-title">Checkout</h2>
            
            <?php display_message(); ?>
            
            <?php if($error): ?>
                <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span style="color: #DC2626; font-weight: 500;"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="checkout-form-wrapper" id="checkoutForm" novalidate>
                <!-- Hidden input untuk menandakan direct checkout -->
                <?php if($is_direct_checkout): ?>
                    <input type="hidden" name="is_direct_checkout" value="1">
                <?php endif; ?>
                
                <div class="checkout-grid">
                    <!-- Left: Form -->
                    <div class="checkout-form-section">
                        <div class="form-card">
                            <h3>Informasi Pengiriman</h3>
                            
                            <div class="form-group">
                                <label>Nama Lengkap <span class="required">*</span></label>
                                <input type="text" name="customer_name" id="customer_name" value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>" class="form-input" placeholder="Masukkan nama lengkap">
                            </div>
                            
                            <div class="form-group">
                                <label>Email <span class="required">*</span></label>
                                <input type="email" name="customer_email" id="customer_email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" class="form-input" placeholder="Masukkan email">
                            </div>
                            
                            <!-- Alamat Pengiriman Section -->
                            <div class="form-group">
                                <label>Alamat Pengiriman <span class="required">*</span></label>
                                
                                <?php if(count($addresses_list) > 0): ?>
                                    <!-- Daftar Alamat Tersimpan -->
                                    <div class="saved-addresses">
                                        <?php foreach($addresses_list as $addr): ?>
                                            <div class="address-card <?php echo ($default_address && $addr['address_id'] == $default_address['address_id']) ? 'selected' : ''; ?>" 
                                                 onclick="selectAddress(<?php echo $addr['address_id']; ?>, '<?php echo htmlspecialchars($addr['phone']); ?>')">
                                                <input type="radio" 
                                                       name="address_id" 
                                                       value="<?php echo $addr['address_id']; ?>" 
                                                       id="addr_<?php echo $addr['address_id']; ?>"
                                                       <?php echo ($default_address && $addr['address_id'] == $default_address['address_id']) ? 'checked' : ''; ?>>
                                                <label for="addr_<?php echo $addr['address_id']; ?>" class="address-content">
                                                    <div class="address-header">
                                                        <strong><?php echo htmlspecialchars($addr['recipient_name']); ?></strong>
                                                        <?php if($addr['is_default'] == 1): ?>
                                                            <span class="badge-default">Default</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="address-phone"><?php echo htmlspecialchars($addr['phone']); ?></div>
                                                    <div class="address-detail">
                                                        <?php echo htmlspecialchars($addr['full_address']); ?><br>
                                                        <?php echo htmlspecialchars($addr['city'] . ', ' . $addr['province'] . ' ' . $addr['postal_code']); ?>
                                                    </div>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Button Tambah Alamat Baru -->
                                    <button type="button" class="btn-add-address" onclick="openAddAddressModal()">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        Tambah Alamat Baru
                                    </button>
                                    
                                    <!-- Hidden phone field untuk manual input jika diperlukan -->
                                    <input type="hidden" name="phone" id="selected_phone" value="<?php echo $default_address ? htmlspecialchars($default_address['phone']) : ''; ?>">
                                    
                                <?php else: ?>
                                    <!-- Jika belum ada alamat tersimpan -->
                                    <div class="no-address-warning">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2">
                                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                            <line x1="12" y1="9" x2="12" y2="13"></line>
                                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                        </svg>
                                        <p>Anda belum memiliki alamat tersimpan.</p>
                                        <p class="small-text">Silakan tambahkan alamat di halaman profil terlebih dahulu untuk checkout lebih cepat.</p>
                                        <a href="profile.php?page=address" class="btn-go-to-profile">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                            </svg>
                                            Tambah Alamat di Profil
                                        </a>
                                    </div>
                                    
                                    <!-- Manual Input untuk user tanpa alamat tersimpan -->
                                    <div class="manual-address-input">
                                        <label>Nomor Telepon <span class="required">*</span></label>
                                        <input type="tel" name="phone" id="manual_phone" placeholder="08123456789" class="form-input">
                                        
                                        <label style="margin-top: 16px;">Alamat Lengkap <span class="required">*</span></label>
                                        <textarea name="shipping_address" id="manual_address" rows="4" placeholder="Masukkan alamat lengkap pengiriman..." class="form-input"></textarea>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Shipping Service Selection -->
                        <?php 
                        require_once '../config/shipping.php';
                        include '../includes/shipping-selection.php'; 
                        ?>
                        
                        <div class="form-card">
                            <h3>Metode Pembayaran <span class="required">*</span></h3>
                            <p class="payment-subtitle">Pilih salah satu metode pembayaran di bawah ini</p>
                            
                            <div class="payment-methods-simple">
                                <!-- Transfer Bank Section -->
                                <div class="payment-category">
                                    <h4 class="payment-category-title">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                            <line x1="1" y1="10" x2="23" y2="10"></line>
                                        </svg>
                                        Transfer Bank
                                    </h4>
                                    
                                    <div class="payment-options-grid">
                                        <label class="payment-option-card" data-payment="Transfer Bank - BCA">
                                            <input type="radio" name="payment_method" value="Transfer Bank - BCA">
                                            <div class="payment-card-content">
                                                <span class="payment-name">Bank BCA</span>
                                                <div class="payment-details">
                                                    <small>No. Rek: <strong>1234567890</strong></small>
                                                    <small>A/N: RetroLoved Official</small>
                                                </div>
                                            </div>
                                            <div class="payment-checkmark">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                        </label>
                                        
                                        <label class="payment-option-card" data-payment="Transfer Bank - BRI">
                                            <input type="radio" name="payment_method" value="Transfer Bank - BRI">
                                            <div class="payment-card-content">
                                                <span class="payment-name">Bank BRI</span>
                                                <div class="payment-details">
                                                    <small>No. Rek: <strong>0987654321</strong></small>
                                                    <small>A/N: RetroLoved Official</small>
                                                </div>
                                            </div>
                                            <div class="payment-checkmark">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                        </label>
                                        
                                        <label class="payment-option-card" data-payment="Transfer Bank - Mandiri">
                                            <input type="radio" name="payment_method" value="Transfer Bank - Mandiri">
                                            <div class="payment-card-content">
                                                <span class="payment-name">Bank Mandiri</span>
                                                <div class="payment-details">
                                                    <small>No. Rek: <strong>1122334455</strong></small>
                                                    <small>A/N: RetroLoved Official</small>
                                                </div>
                                            </div>
                                            <div class="payment-checkmark">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                        </label>
                                        
                                        <label class="payment-option-card" data-payment="Transfer Bank - Jatim">
                                            <input type="radio" name="payment_method" value="Transfer Bank - Jatim">
                                            <div class="payment-card-content">
                                                <span class="payment-name">Bank Jatim</span>
                                                <div class="payment-details">
                                                    <small>No. Rek: <strong>5544332211</strong></small>
                                                    <small>A/N: RetroLoved Official</small>
                                                </div>
                                            </div>
                                            <div class="payment-checkmark">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- E-Wallet Section -->
                                <div class="payment-category">
                                    <h4 class="payment-category-title">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                        E-Wallet
                                    </h4>
                                    
                                    <div class="payment-options-grid">
                                        <label class="payment-option-card" data-payment="E-Wallet - GoPay">
                                            <input type="radio" name="payment_method" value="E-Wallet - GoPay">
                                            <div class="payment-card-content">
                                                <span class="payment-name">GoPay</span>
                                                <div class="payment-details">
                                                    <small>Upload bukti pembayaran setelah order dibuat</small>
                                                </div>
                                            </div>
                                            <div class="payment-checkmark">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                        </label>
                                        
                                        <label class="payment-option-card" data-payment="E-Wallet - DANA">
                                            <input type="radio" name="payment_method" value="E-Wallet - DANA">
                                            <div class="payment-card-content">
                                                <span class="payment-name">DANA</span>
                                                <div class="payment-details">
                                                    <small>Upload bukti pembayaran setelah order dibuat</small>
                                                </div>
                                            </div>
                                            <div class="payment-checkmark">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                        </label>
                                        
                                        <label class="payment-option-card" data-payment="E-Wallet - OVO">
                                            <input type="radio" name="payment_method" value="E-Wallet - OVO">
                                            <div class="payment-card-content">
                                                <span class="payment-name">OVO</span>
                                                <div class="payment-details">
                                                    <small>Upload bukti pembayaran setelah order dibuat</small>
                                                </div>
                                            </div>
                                            <div class="payment-checkmark">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="payment-instruction">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                    </svg>
                                    <p>Setelah melakukan pembayaran, silakan upload bukti transfer di halaman <strong>My Orders</strong> untuk verifikasi pesanan Anda.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right: Order Summary -->
                    <div class="order-summary-section">
                        <div class="order-summary">
                            <h3>Ringkasan Pesanan</h3>
                            
                            <div class="order-items">
                                <?php foreach($cart_data as $item): ?>
                                    <div class="order-item">
                                        <img src="../assets/images/products/<?php echo $item['image_url']; ?>" 
                                             alt="<?php echo $item['product_name']; ?>"
                                             onerror="this.src='../assets/images/products/placeholder.jpg'">
                                        <div class="order-item-info">
                                            <strong><?php echo $item['product_name']; ?></strong>
                                            <p>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                                        </div>
                                        <div class="order-item-price">
                                            Rp <?php echo number_format($item['price'], 0, ',', '.'); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="order-total">
                                <div class="total-row">
                                    <span>Subtotal</span>
                                    <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                                </div>
                                <div class="total-row">
                                    <span>Ongkir</span>
                                    <span id="orderSummaryShippingCost" class="shipping-cost-value">
                                        <span class="text-muted" style="font-size: 13px; font-style: italic;">Pilih pengiriman</span>
                                    </span>
                                </div>
                                <div class="total-row total-final">
                                    <span>Total</span>
                                    <span id="orderSummaryTotal">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                                </div>
                            </div>
                            
                            <!-- Terms and Conditions Checkbox -->
                            <div class="terms-checkbox-wrapper">
                                <label class="terms-checkbox-label">
                                    <input type="checkbox" id="termsCheckbox" name="terms_accepted" class="terms-checkbox-input">
                                    <span class="terms-checkbox-text">
                                        Saya telah membaca dan menyetujui 
                                        <a href="../terms-conditions.php" target="_blank" class="terms-link">Syarat dan Ketentuan</a> 
                                        yang berlaku
                                    </span>
                                </label>
                            </div>
                            
                            <button type="button" class="btn btn-primary btn-place-order" id="placeOrderBtn" onclick="showConfirmationModal()" disabled>
                                Buat Pesanan
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 6px;">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </button>
                            
                            <div class="shipping-notice" style="margin-top: 16px; padding: 12px; background: #FFF7ED; border: 1px solid #FDBA74; border-radius: 8px; display: none;" id="shippingNotice">
                                <div style="display: flex; align-items: start; gap: 8px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                    </svg>
                                    <p style="margin: 0; font-size: 13px; color: #9A3412; line-height: 1.5;">
                                        <strong>Pilih layanan pengiriman</strong> terlebih dahulu untuk melanjutkan checkout
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="confirmation-modal">
        <div class="confirmation-modal-content">
            <div class="confirmation-modal-header">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <h3>Konfirmasi Pesanan</h3>
                <button type="button" class="modal-close" onclick="closeConfirmationModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="confirmation-modal-body">
                <div class="confirmation-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h4>Pastikan data pesanan Anda sudah benar</h4>
                <div class="confirmation-details">
                    <div class="detail-row">
                        <span class="detail-label">Nama:</span>
                        <span class="detail-value" id="confirm-name"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value" id="confirm-email"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Alamat Pengiriman:</span>
                        <span class="detail-value" id="confirm-address"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">No. Telepon:</span>
                        <span class="detail-value" id="confirm-phone"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Metode Pembayaran:</span>
                        <span class="detail-value" id="confirm-payment"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Layanan Pengiriman:</span>
                        <span class="detail-value" id="confirm-shipping"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Biaya Pengiriman:</span>
                        <span class="detail-value" id="confirm-shipping-cost"></span>
                    </div>
                    <div class="detail-row total-row">
                        <span class="detail-label">Total Pembayaran:</span>
                        <span class="detail-value total-value" id="orderSummaryTotalValue">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                    </div>
                </div>
                <div class="confirmation-warning">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <p>Setelah pesanan dibuat, Anda tidak dapat mengubah data pesanan. Pastikan semua informasi sudah benar!</p>
                </div>
            </div>
            <div class="confirmation-modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="closeConfirmationModal()">
                    Periksa Kembali
                </button>
                <button type="button" class="btn-modal-save" onclick="confirmOrder(event)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Ya, Buat Pesanan
                </button>
            </div>
        </div>
    </div>

    <!-- Custom Checkout Scripts - Load BEFORE footer to ensure order -->
    <script src="../assets/js/toast.js?v=<?php echo time(); ?>"></script>
    <script src="../assets/js/modal.js?v=<?php echo time(); ?>"></script>
    <script src="../assets/js/loading.js?v=<?php echo time(); ?>"></script>
    <script src="../assets/js/validation.js?v=<?php echo time(); ?>"></script>
    <script src="../assets/js/accessibility.js?v=<?php echo time(); ?>"></script>
    <script>
        // Force create toast container if not exists
        function ensureToastContainer() {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'toast-container';
                container.style.position = 'fixed';
                container.style.top = '20px';
                container.style.right = '20px';
                container.style.zIndex = '10001';
                container.style.display = 'flex';
                container.style.flexDirection = 'column';
                container.style.gap = '12px';
                container.style.maxWidth = '400px';
                container.style.pointerEvents = 'none';
                document.body.appendChild(container);
                console.log('✓ Toast container created manually with inline styles');
            } else {
                console.log('✓ Toast container already exists');
            }
            return container;
        }
        
        // Ensure container exists
        ensureToastContainer();
        
        // Debug: Check if toast functions are available
        console.log('=== CHECKOUT TOAST DEBUG ===');
        console.log('Toast container exists:', !!document.getElementById('toast-container'));
        console.log('window.Toast:', typeof window.Toast);
        console.log('toastWarning:', typeof toastWarning);
        console.log('toastError:', typeof toastError);
        console.log('toastSuccess:', typeof toastSuccess);
        console.log('toastInfo:', typeof toastInfo);
        console.log('confirmModal:', typeof confirmModal);
        console.log('============================');
        
        // Toast system is ready - no test notification needed
    </script>
    <script>
        // Utility function to format numbers with thousand separator
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        
        // Function to select address
        function selectAddress(addressId, phone) {
            // Remove selected class from all address cards
            document.querySelectorAll('.address-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            const selectedCard = event.target.closest('.address-card');
            if (selectedCard) {
                selectedCard.classList.add('selected');
            }
            
            // Update the radio button
            const radio = document.getElementById('addr_' + addressId);
            if (radio) {
                radio.checked = true;
            }
            
            // Update hidden phone field
            const phoneField = document.getElementById('selected_phone');
            if (phoneField) {
                phoneField.value = phone;
            }
        }
        
        // Function to open add address modal (placeholder for future implementation)
        function openAddAddressModal() {
            toastInfo('Untuk menambah alamat baru, silakan pergi ke halaman Profil > Alamat Pengiriman');
            window.location.href = 'profile.php?page=address';
        }
        

        
        // Terms and Conditions checkbox handler
        document.addEventListener('DOMContentLoaded', function() {
            const termsCheckbox = document.getElementById('termsCheckbox');
            const placeOrderBtn = document.getElementById('placeOrderBtn');
            
            if (termsCheckbox && placeOrderBtn) {
                // Enable/disable button based on checkbox
                termsCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        placeOrderBtn.disabled = false;
                        placeOrderBtn.style.opacity = '1';
                        placeOrderBtn.style.cursor = 'pointer';
                    } else {
                        placeOrderBtn.disabled = true;
                        placeOrderBtn.style.opacity = '0.5';
                        placeOrderBtn.style.cursor = 'not-allowed';
                    }
                });
                
                // Initial state - button is disabled
                placeOrderBtn.style.opacity = '0.5';
                placeOrderBtn.style.cursor = 'not-allowed';
            }
        });
        
        // Prevent multiple simultaneous calls
        let isValidating = false;
        
        function showConfirmationModal() {
            // Check if terms are accepted first
            const termsCheckbox = document.getElementById('termsCheckbox');
            if (termsCheckbox && !termsCheckbox.checked) {
                if (typeof toastWarning === 'function') {
                    toastWarning('Mohon centang persetujuan Syarat dan Ketentuan terlebih dahulu!');
                } else {
                    alert('Mohon centang persetujuan Syarat dan Ketentuan terlebih dahulu!');
                }
                // Highlight the checkbox
                const checkboxWrapper = document.querySelector('.terms-checkbox-wrapper');
                if (checkboxWrapper) {
                    checkboxWrapper.style.border = '2px solid #EF4444';
                    checkboxWrapper.style.borderRadius = '8px';
                    checkboxWrapper.style.padding = '12px';
                    checkboxWrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => {
                        checkboxWrapper.style.border = '';
                        checkboxWrapper.style.padding = '';
                    }, 3000);
                }
                return;
            }
            // Prevent multiple simultaneous validations
            if (isValidating) {
                console.log('Validation already in progress, skipping...');
                return;
            }
            
            isValidating = true;
            
            // Validate form first
            const form = document.querySelector('.checkout-form-wrapper');
            const customerName = document.querySelector('input[name="customer_name"]').value.trim();
            const customerEmail = document.querySelector('input[name="customer_email"]').value.trim();
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            
            // Check address
            const addressId = document.querySelector('input[name="address_id"]:checked');
            const manualAddress = document.querySelector('textarea[name="shipping_address"]');
            const phoneInputs = document.querySelectorAll('input[name="phone"]');
            
            let phone = '';
            let address = '';
            
            // Get phone from visible input or hidden input
            phoneInputs.forEach(input => {
                if (input.type === 'hidden' && input.value) {
                    phone = input.value;
                } else if (input.type !== 'hidden' && input.offsetParent !== null && input.value) {
                    phone = input.value;
                }
            });
            
            // Get address
            if (addressId) {
                const selectedAddressCard = addressId.closest('.address-card');
                if (selectedAddressCard) {
                    const addressDetail = selectedAddressCard.querySelector('.address-detail');
                    address = addressDetail ? addressDetail.textContent.trim() : '';
                }
            } else if (manualAddress && manualAddress.value.trim()) {
                address = manualAddress.value.trim();
            }
            
            // Validation with toast notifications
            if (!customerName) {
                console.log('Validation failed: Name is empty');
                if (typeof toastWarning === 'function') {
                    toastWarning('Mohon isi nama lengkap Anda!');
                } else {
                    console.error('toastWarning not available, using alert');
                    alert('Mohon isi nama lengkap Anda!');
                }
                const nameInput = document.querySelector('input[name="customer_name"]');
                if (nameInput) {
                    nameInput.focus();
                    nameInput.style.borderColor = '#EF4444';
                    setTimeout(() => { nameInput.style.borderColor = ''; }, 3000);
                }
                isValidating = false;
                return;
            }
            
            if (!customerEmail) {
                console.log('Validation failed: Email is empty');
                if (typeof toastWarning === 'function') {
                    toastWarning('Mohon isi email Anda!');
                } else {
                    console.error('toastWarning not available, using alert');
                    alert('Mohon isi email Anda!');
                }
                const emailInput = document.querySelector('input[name="customer_email"]');
                if (emailInput) {
                    emailInput.focus();
                    emailInput.style.borderColor = '#EF4444';
                    setTimeout(() => { emailInput.style.borderColor = ''; }, 3000);
                }
                isValidating = false;
                return;
            }
            
            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(customerEmail)) {
                console.log('Validation failed: Email format invalid');
                if (typeof toastWarning === 'function') {
                    toastWarning('Format email tidak valid!');
                } else {
                    alert('Format email tidak valid!');
                }
                const emailInput = document.querySelector('input[name="customer_email"]');
                if (emailInput) {
                    emailInput.focus();
                    emailInput.style.borderColor = '#EF4444';
                    setTimeout(() => { emailInput.style.borderColor = ''; }, 3000);
                }
                isValidating = false;
                return;
            }
            
            if (!address) {
                console.log('Validation failed: Address is empty');
                if (typeof toastWarning === 'function') {
                    toastWarning('Mohon pilih atau isi alamat pengiriman!');
                } else {
                    alert('Mohon pilih atau isi alamat pengiriman!');
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
                isValidating = false;
                return;
            }
            
            if (!phone) {
                console.log('Validation failed: Phone is empty');
                if (typeof toastWarning === 'function') {
                    toastWarning('Mohon isi nomor telepon!');
                } else {
                    alert('Mohon isi nomor telepon!');
                }
                const phoneInput = document.querySelector('input[name="phone"]:not([type="hidden"])');
                if (phoneInput && phoneInput.offsetParent !== null) {
                    phoneInput.focus();
                    phoneInput.style.borderColor = '#EF4444';
                    setTimeout(() => { phoneInput.style.borderColor = ''; }, 3000);
                }
                isValidating = false;
                return;
            }
            
            // Check shipping service selection
            const shippingService = document.querySelector('input[name="shipping_service_id"]:checked');
            if (!shippingService) {
                console.log('Validation failed: Shipping service not selected');
                if (typeof toastWarning === 'function') {
                    toastWarning('Mohon pilih layanan pengiriman!');
                } else {
                    alert('Mohon pilih layanan pengiriman!');
                }
                const shippingSection = document.querySelector('.shipping-services-grid');
                if (shippingSection) {
                    shippingSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    const formCard = shippingSection.closest('.form-card');
                    if (formCard) {
                        formCard.style.border = '2px solid #EF4444';
                        setTimeout(() => { formCard.style.border = ''; }, 3000);
                    }
                }
                // Show notice in order summary
                const shippingNotice = document.getElementById('shippingNotice');
                if (shippingNotice) {
                    shippingNotice.style.display = 'block';
                    setTimeout(() => { shippingNotice.style.display = 'none'; }, 5000);
                }
                isValidating = false;
                return;
            }
            
            if (!paymentMethod) {
                console.log('Validation failed: Payment method not selected');
                if (typeof toastWarning === 'function') {
                    toastWarning('Mohon pilih metode pembayaran!');
                } else {
                    alert('Mohon pilih metode pembayaran!');
                }
                const paymentSection = document.querySelector('.payment-methods-simple');
                if (paymentSection) {
                    paymentSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    paymentSection.style.border = '2px solid #EF4444';
                    setTimeout(() => { paymentSection.style.border = ''; }, 3000);
                }
                isValidating = false;
                return;
            }
            
            // Get shipping info (reuse shippingService variable from validation above)
            const shippingServiceCard = shippingService.closest('.shipping-service-card');
            const shippingServiceName = shippingServiceCard ? shippingServiceCard.querySelector('.courier-name').textContent.trim() : 'Tidak dipilih';
            const shippingCost = shippingService ? parseFloat(shippingService.dataset.cost) : 0;
            
            // Populate confirmation modal
            document.getElementById('confirm-name').textContent = customerName;
            document.getElementById('confirm-email').textContent = customerEmail;
            document.getElementById('confirm-address').textContent = address;
            document.getElementById('confirm-phone').textContent = phone;
            document.getElementById('confirm-payment').textContent = paymentMethod.value;
            document.getElementById('confirm-shipping').textContent = shippingServiceName;
            document.getElementById('confirm-shipping-cost').textContent = shippingCost === 0 ? 'Gratis' : 'Rp ' + formatNumber(shippingCost);
            
            // Show modal
            const modal = document.getElementById('confirmationModal');
            modal.style.display = 'flex';
            
            // Prevent background scroll
            document.body.classList.add('modal-open');
            
            // Reset validation flag after modal is shown
            isValidating = false;
        }
        
        function closeConfirmationModal() {
            const modal = document.getElementById('confirmationModal');
            modal.style.display = 'none';
            
            // Re-enable background scroll
            document.body.classList.remove('modal-open');
        }
        
        function confirmOrder(event) {
            console.log('confirmOrder called');
            
            try {
                // Validate form before submission
                const form = document.querySelector('.checkout-form-wrapper');
                
                if (!form) {
                    toastError('Form tidak ditemukan!');
                    return false;
                }
                
                // Check if address is selected
                const addressIdInput = document.querySelector('input[name="address_id"]:checked');
                if (!addressIdInput) {
                    toastWarning('Silakan pilih alamat pengiriman terlebih dahulu');
                    closeConfirmationModal();
                    return false;
                }
                
                // Check if shipping service is selected
                const shippingServiceInput = document.querySelector('input[name="shipping_service_id"]:checked');
                if (!shippingServiceInput) {
                    toastWarning('Silakan pilih layanan pengiriman terlebih dahulu');
                    closeConfirmationModal();
                    return false;
                }
                
                // Check if payment method is selected
                const paymentMethodInput = document.querySelector('input[name="payment_method"]:checked');
                if (!paymentMethodInput) {
                    toastWarning('Silakan pilih metode pembayaran terlebih dahulu');
                    closeConfirmationModal();
                    return false;
                }
                
                // Create a hidden input for place_order
                const submitInput = document.createElement('input');
                submitInput.type = 'hidden';
                submitInput.name = 'place_order';
                submitInput.value = '1';
                form.appendChild(submitInput);
                
                // Show loading overlay
                if (typeof showLoadingOverlay === 'function') {
                    showLoadingOverlay();
                }
                
                // Disable button to prevent double submission
                const confirmBtn = event ? event.target : document.querySelector('.btn-modal-save');
                if (confirmBtn) {
                    confirmBtn.disabled = true;
                    confirmBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10"></circle></svg> Memproses...';
                    
                    if (typeof setButtonLoading === 'function') {
                        setButtonLoading(confirmBtn);
                    }
                }
                
                // Close modal
                closeConfirmationModal();
                
                // Submit form
                console.log('Submitting form...');
                form.submit();
                
            } catch (error) {
                console.error('Error in confirmOrder:', error);
                toastError('Terjadi kesalahan: ' + error.message);
            }
        }
        
        // Prevent form default submission and use custom validation
        document.addEventListener('DOMContentLoaded', function() {
            const checkoutForm = document.getElementById('checkoutForm');
            
            // Prevent default form submission
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('Form submission prevented - use button click instead');
                    return false;
                });
            }
            
            // Validate address selection
            const addressCards = document.querySelectorAll('.address-card');
            addressCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove error state from address section if exists
                    const addressSection = document.querySelector('.addresses-list');
                    if (addressSection) {
                        addressSection.style.border = 'none';
                    }
                });
            });
            
            // Validate payment method selection
            const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
            paymentMethods.forEach(method => {
                method.addEventListener('change', function() {
                    // Remove error state from payment section if exists
                    const paymentSection = document.querySelector('.payment-methods-simple');
                    if (paymentSection) {
                        paymentSection.style.border = 'none';
                    }
                });
            });
            
            // Setup payment method selection with proper event handling
            console.log('=== PAYMENT METHOD SETUP ===');
            
            // Method 1: Listen to radio button changes (native behavior)
            const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
            console.log('Found payment radios:', paymentRadios.length);
            
            paymentRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    console.log('Radio changed:', this.value);
                    
                    // Remove selected class from all cards
                    document.querySelectorAll('.payment-option-card').forEach(card => {
                        card.classList.remove('selected');
                    });
                    
                    // Add selected class to parent card
                    const parentCard = this.closest('.payment-option-card');
                    if (parentCard) {
                        parentCard.classList.add('selected');
                        console.log('✓ Card selected:', this.value);
                    }
                    
                    // Remove validation error
                    const paymentSection = document.querySelector('.payment-methods-simple');
                    if (paymentSection) {
                        paymentSection.style.border = '';
                    }
                });
            });
            
            // Method 2: Direct click on label (backup)
            const paymentCards = document.querySelectorAll('.payment-option-card');
            console.log('Found payment cards:', paymentCards.length);
            
            paymentCards.forEach((card, index) => {
                card.style.cursor = 'pointer';
                card.style.pointerEvents = 'auto';
                
                card.addEventListener('click', function(e) {
                    console.log('Card clicked:', index, e.target.tagName);
                    
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio && !radio.checked) {
                        console.log('Checking radio:', radio.value);
                        radio.checked = true;
                        radio.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            });
            
            console.log('=== PAYMENT METHOD SETUP COMPLETE ===');
            
            // Validate phone number if editable
            const phoneInputs = document.querySelectorAll('input[type="tel"], input[name*="phone"]');
            phoneInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value && this.value.length < 10) {
                        this.style.borderColor = '#EF4444';
                        if (typeof toastWarning === 'function') {
                            toastWarning('Nomor telepon minimal 10 digit');
                        }
                    } else {
                        this.style.borderColor = '';
                    }
                });
                
                input.addEventListener('input', function() {
                    // Auto-format phone number - only allow digits
                    let value = this.value.replace(/[^\d]/g, '');
                    this.value = value;
                    // Remove error styling when user types
                    if (this.style.borderColor === 'rgb(239, 68, 68)') {
                        this.style.borderColor = '';
                    }
                });
            });
            
            // Clear error styling on input
            const allInputs = document.querySelectorAll('.form-input');
            allInputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.style.borderColor === 'rgb(239, 68, 68)') {
                        this.style.borderColor = '';
                    }
                });
            });
        });
        
        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('confirmationModal');
            if (event.target === modal) {
                closeConfirmationModal();
            }
        });
    </script>

    <script>
        // Show error toast if exists
        <?php if($error): ?>
            window.addEventListener('DOMContentLoaded', function() {
                if (typeof toastError === 'function') {
                    toastError('<?php echo addslashes($error); ?>');
                }
            });
        <?php endif; ?>
        
        // REMOVE DEFAULT FORM VALIDATION - We handle it in showConfirmationModal()
        // This prevents the ugly default browser validation popups
        const checkoutForm = document.querySelector('form[method="POST"]');
        if (checkoutForm) {
            // Prevent any form submission
            checkoutForm.addEventListener('submit', function(event) {
                event.preventDefault();
                event.stopPropagation();
                console.log('Form submit prevented - validation handled by button');
                return false;
            });
            
            // OLD CODE BELOW - DISABLED - We use showConfirmationModal() for validation
            /*
            // This old validation code is disabled to prevent default browser popups
            checkoutForm.addEventListener('submit', function(event) {
                // Remove all previous error states
                clearValidationErrors();
                
                let hasError = false;
                let firstErrorElement = null;
                
                try {
                    // Get all sections
                    const formSection = document.querySelector('.checkout-form-section');
                    const addressSection = formSection ? formSection.querySelector('.form-card:first-child') : null;
                    const paymentMethodsEl = document.querySelector('.payment-methods');
                    const paymentSection = paymentMethodsEl ? paymentMethodsEl.closest('.form-card') : null;
                    
                    // Validate name and email
                    const customerName = document.querySelector('input[name="customer_name"]');
                    const customerEmail = document.querySelector('input[name="customer_email"]');
                    
                    if (!customerName || !customerName.value.trim()) {
                        event.preventDefault();
                        hasError = true;
                        if (customerName) customerName.classList.add('validation-error');
                        if (addressSection && !firstErrorElement) {
                            showSectionError(addressSection, 'Mohon lengkapi nama lengkap dan email Anda!');
                            firstErrorElement = addressSection;
                        }
                    }
                    
                    if (!customerEmail || !customerEmail.value.trim()) {
                        event.preventDefault();
                        hasError = true;
                        if (customerEmail) customerEmail.classList.add('validation-error');
                        if (addressSection && !firstErrorElement) {
                            showSectionError(addressSection, 'Mohon lengkapi nama lengkap dan email Anda!');
                            firstErrorElement = addressSection;
                        }
                    }
                    
                    // Check if address is selected or manual address is filled
                    const addressId = document.querySelector('input[name="address_id"]:checked');
                    const shippingAddress = document.querySelector('input[name="shipping_address"]');
                    const phoneInputs = document.querySelectorAll('input[name="phone"]');
                    let phone = null;
                    
                    // Get the visible phone input
                    phoneInputs.forEach(input => {
                        if (input.offsetParent !== null) { // Check if visible
                            phone = input;
                        }
                    });
                    
                    // If no saved address selected, check manual input
                    if (!addressId) {
                        if (shippingAddress && (!shippingAddress.value || !shippingAddress.value.trim())) {
                            event.preventDefault();
                            hasError = true;
                            shippingAddress.classList.add('validation-error');
                            if (addressSection && !firstErrorElement) {
                                showSectionError(addressSection, 'Silakan isi alamat pengiriman lengkap!');
                                firstErrorElement = addressSection;
                            }
                        }
                        if (phone && (!phone.value || !phone.value.trim())) {
                            event.preventDefault();
                            hasError = true;
                            phone.classList.add('validation-error');
                            if (addressSection && !firstErrorElement) {
                                showSectionError(addressSection, 'Nomor telepon harus diisi!');
                                firstErrorElement = addressSection;
                            }
                        }
                    }
                    
                    // Check if payment method is selected
                    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
                    
                    if (!paymentMethod) {
                        event.preventDefault();
                        hasError = true;
                        if (paymentSection) {
                            showSectionError(paymentSection, 'Silakan pilih metode pembayaran terlebih dahulu!');
                            addValidationError('.payment-methods');
                            if (!firstErrorElement) firstErrorElement = paymentSection;
                        }
                    }
                    
                    // If there are errors, scroll to first error (no toast to avoid conflicts)
                    if (hasError) {
                        if (firstErrorElement) {
                            setTimeout(() => {
                                firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }, 100);
                        }
                        return false;
                    }
                    
                } catch (error) {
                    console.error('Validation error:', error);
                    return true; // Allow form submit if validation script fails
                }
                
                // All validation passed
                return true;
            });
            END OF DISABLED OLD VALIDATION CODE */
        }
        
        // Function to show section error (still used for reference, but not called)
        function showSectionError(sectionElement, message) {
            if (!sectionElement) return;
            
            // Check if warning already exists
            if (sectionElement.querySelector('.section-warning')) return;
            
            const warning = document.createElement('div');
            warning.className = 'section-warning';
            warning.innerHTML = `
                <div class="section-warning-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div class="section-warning-text">${message}</div>
                <button type="button" class="section-warning-close" onclick="this.parentElement.remove()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            `;
            
            sectionElement.insertBefore(warning, sectionElement.firstChild);
        }
        
        // Function to add validation error class
        function addValidationError(selector) {
            const element = document.querySelector(selector);
            if (element) {
                element.classList.add('validation-error');
                setTimeout(() => {
                    element.classList.remove('validation-error');
                }, 3000);
            }
        }
        
        // Function to clear all validation errors
        function clearValidationErrors() {
            document.querySelectorAll('.section-warning').forEach(el => el.remove());
            document.querySelectorAll('.validation-error').forEach(el => el.classList.remove('validation-error'));
        }
        
        // Handle address selection
        function selectAddress(addressId, phone) {
            // Remove selected class from all address cards
            document.querySelectorAll('.address-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            event.currentTarget.classList.add('selected');
            
            // Update hidden phone field
            document.getElementById('selected_phone').value = phone;
            
            // Check the radio button
            document.getElementById('addr_' + addressId).checked = true;
        }
        
        // Open Add Address Modal
        function openAddAddressModal() {
            document.getElementById('addAddressModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        // Close Add Address Modal
        function closeAddAddressModal() {
            document.getElementById('addAddressModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('addAddressForm').reset();
        }
        
        // Submit Add Address via AJAX
        function submitAddAddress() {
            const form = document.getElementById('addAddressForm');
            
            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Get form data
            const formData = new FormData(form);
            formData.append('add_address_ajax', '1');
            
            // Show loading
            document.getElementById('btnAddText').style.display = 'none';
            document.getElementById('btnAddLoading').style.display = 'inline-flex';
            
            // Send AJAX request
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading
                document.getElementById('btnAddText').style.display = 'inline';
                document.getElementById('btnAddLoading').style.display = 'none';
                
                if (data.success) {
                    // Show success message
                    toastSuccess(data.message);
                    
                    // Close modal
                    closeAddAddressModal();
                    
                    // Reload page to show new address
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Show error message
                    toastError(data.message);
                }
            })
            .catch(error => {
                // Hide loading
                document.getElementById('btnAddText').style.display = 'inline';
                document.getElementById('btnAddLoading').style.display = 'none';
                
                console.error('Error:', error);
                toastError('Terjadi kesalahan. Silakan coba lagi.');
            });
        }
        
        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('addAddressModal');
            if (event.target === modal) {
                closeAddAddressModal();
            }
        });
    </script>
    
    <style>
        /* Saved Addresses Styling */
        .saved-addresses {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .address-card {
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            background: #FFFFFF;
        }
        
        .address-card:hover {
            border-color: #F97316;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.1);
        }
        
        .address-card.selected {
            border-color: #F97316;
            background: #FFF7ED;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.15);
        }
        
        .address-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        
        .address-content {
            display: block;
            cursor: pointer;
        }
        
        .address-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        
        .address-header strong {
            font-size: 16px;
            color: #1F2937;
        }
        
        .badge-default {
            background: #F97316;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .address-phone {
            color: #6B7280;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .address-detail {
            color: #374151;
            font-size: 14px;
            line-height: 1.6;
        }
        
        /* Button Tambah Alamat */
        .btn-add-address {
            width: 100%;
            padding: 12px 16px;
            border: 2px dashed #D1D5DB;
            border-radius: 12px;
            background: #F9FAFB;
            color: #6B7280;
            font-weight: 500;
            font-size: 15px;
            line-height: 1.5;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-add-address:hover {
            border-color: #F97316;
            background: #FFF7ED;
            color: #F97316;
        }
        
        .btn-add-address svg {
            flex-shrink: 0;
            display: block;
            margin: 0;
            transition: transform 0.3s ease;
        }
        
        .btn-add-address:hover svg {
            transform: rotate(90deg);
        }
        
        /* No Address Warning */
        .no-address-warning {
            text-align: center;
            padding: 32px;
            background: #FFFBEB;
            border: 2px solid #FDE68A;
            border-radius: 12px;
            margin-bottom: 16px;
        }
        
        .no-address-warning svg {
            margin: 0 auto 16px;
        }
        
        .no-address-warning p {
            color: #92400E;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .no-address-warning .small-text {
            color: #B45309;
            font-size: 14px;
            font-weight: 400;
        }
        
        .btn-go-to-profile {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            background: #F97316;
            color: white;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            line-height: 1.5;
            margin-top: 16px;
            transition: all 0.3s ease;
            text-decoration: none;
            border: none;
        }
        
        .btn-go-to-profile:hover {
            background: #EA580C;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }
        
        .btn-go-to-profile svg {
            flex-shrink: 0;
            display: block;
            margin: 0;
        }
        
        /* Manual Address Input */
        .manual-address-input {
            margin-top: 16px;
        }
        
        .manual-address-input label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .manual-address-input .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .manual-address-input .form-input:focus {
            outline: none;
            border-color: #F97316;
        }
        
        /* Required asterisk */
        .required {
            color: #EF4444;
            margin-left: 2px;
        }
        
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }
        
        .confirmation-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            padding: 20px;
        }
        
        .confirmation-modal-overlay.active {
            display: flex;
        }
        
        /* ===== PREVENT BODY SCROLL WHEN MODAL IS OPEN ===== */
        body.modal-open {
            overflow: hidden !important;
            position: fixed;
            width: 100%;
            height: 100%;
        }
        
        /* ===== CONFIRMATION MODAL - CENTERED ===== */
        .confirmation-modal {
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
        
        .confirmation-modal[style*="display: flex"] {
            display: flex !important;
        }
        
        .confirmation-modal-content {
            background: white;
            border-radius: 16px;
            max-width: 580px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .confirmation-modal-header {
            padding: 24px;
            border-bottom: 2px solid #E5E7EB;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .confirmation-modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #1F2937;
            flex: 1;
        }
        
        .confirmation-modal-body {
            padding: 24px;
        }
        
        .confirmation-modal-body h4 {
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            color: #1F2937;
            margin: 0 0 20px 0;
        }
        
        .confirmation-icon {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .confirmation-details {
            background: #F9FAFB;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            gap: 16px;
        }
        
        .detail-row:last-child {
            margin-bottom: 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: #6B7280;
            font-size: 14px;
        }
        
        .detail-value {
            font-weight: 600;
            color: #1F2937;
            font-size: 14px;
            text-align: right;
        }
        
        .detail-row.total-row {
            border-top: 2px solid #E5E7EB;
            padding-top: 12px;
            margin-top: 12px;
        }
        
        .total-value {
            color: #F97316;
            font-size: 18px;
            font-weight: 700;
        }
        
        .confirmation-warning {
            background: #FEF3C7;
            border: 2px solid #FCD34D;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }
        
        .confirmation-warning svg {
            flex-shrink: 0;
            color: #D97706;
            margin-top: 2px;
        }
        
        .confirmation-warning p {
            margin: 0;
            color: #92400E;
            font-size: 13px;
            line-height: 1.6;
        }
        
        .confirmation-modal-actions {
            padding: 20px 24px;
            border-top: 2px solid #E5E7EB;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        /* ===== MODAL GENERAL STYLES ===== */
        .modal-container,
        .modal-content {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            padding: 24px;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #1F2937;
        }
        
        .modal-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            color: #6B7280;
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        
        .modal-close:hover {
            background: #F3F4F6;
            color: #1F2937;
        }
        
        .modal-body {
            padding: 24px;
        }
        
        .modal-body h4 {
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            color: #1F2937;
            margin: 0 0 20px 0;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .modal-body .form-group {
            margin-bottom: 20px;
        }
        
        .modal-body label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .modal-body .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            font-family: inherit;
        }
        
        .modal-body .form-input:focus {
            outline: none;
            border-color: #F97316;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }
        
        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .checkbox-label span {
            font-size: 14px;
            color: #374151;
        }
        
        .modal-footer {
            padding: 20px 24px;
            border-top: 1px solid #E5E7EB;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-secondary {
            background: #F3F4F6;
            color: #6B7280;
        }
        
        .btn-secondary:hover {
            background: #E5E7EB;
            color: #374151;
        }
        
        .btn-primary {
            background: #F97316;
            color: white;
        }
        
        .btn-primary:hover {
            background: #EA580C;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }
        
        .spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        /* Validation Error Styles */
        .validation-error {
            border: 2px solid #EF4444 !important;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .section-warning {
            background: #FEF2F2;
            border: 2px solid #FCA5A5;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .section-warning-icon {
            flex-shrink: 0;
            color: #DC2626;
        }
        
        .section-warning-text {
            flex: 1;
            color: #991B1B;
            font-size: 14px;
            font-weight: 600;
        }
        
        .section-warning-close {
            background: none;
            border: none;
            color: #DC2626;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: background 0.2s;
        }
        
        .section-warning-close:hover {
            background: #FEE2E2;
        }
        
        .section-title-with-badge {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .required-badge {
            background: #FEF2F2;
            color: #DC2626;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.6;
            }
        }

        /* Terms and Conditions Checkbox Styling */
        .terms-checkbox-wrapper {
            margin: 20px 0;
            padding: 16px;
            background: #F9FAFB;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .terms-checkbox-wrapper:hover {
            background: #F3F4F6;
            border-color: #D1D5DB;
        }
        
        .terms-checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            cursor: pointer;
            user-select: none;
        }
        
        .terms-checkbox-input {
            width: 20px;
            height: 20px;
            min-width: 20px;
            min-height: 20px;
            cursor: pointer;
            margin-top: 2px;
            accent-color: #F97316;
            outline: none !important;
            box-shadow: none !important;
        }
        
        .terms-checkbox-input:focus {
            outline: none !important;
            box-shadow: none !important;
        }
        
        .terms-checkbox-input:focus-visible {
            outline: none !important;
            box-shadow: none !important;
        }
        
        .terms-checkbox-text {
            color: #374151;
            font-size: 14px;
            line-height: 1.6;
            flex: 1;
        }
        
        .terms-link {
            color: #F97316;
            font-weight: 600;
            text-decoration: none;
            border-bottom: 1px solid transparent;
            transition: all 0.3s ease;
            outline: none !important;
            box-shadow: none !important;
        }
        
        .terms-link:hover {
            color: #EA580C;
            border-bottom-color: #EA580C;
        }
        
        .terms-link:focus {
            outline: none !important;
            box-shadow: none !important;
        }
        
        .terms-link:focus-visible {
            outline: none !important;
            box-shadow: none !important;
        }
        
        /* Button disabled state when terms not accepted */
        .btn-place-order:disabled {
            background: #D1D5DB !important;
            color: #9CA3AF !important;
            cursor: not-allowed !important;
            opacity: 0.5;
            transform: none !important;
            box-shadow: none !important;
        }
        
        .btn-place-order:disabled:hover {
            background: #D1D5DB !important;
            transform: none !important;
            box-shadow: none !important;
        }
        
        /* Free Shipping Badge in Order Summary */
        .free-shipping-badge {
            display: inline-block;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0%, 100% {
                box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
            }
            50% {
                box-shadow: 0 4px 12px rgba(16, 185, 129, 0.5);
            }
        }
        
        /* Shipping Cost Value Styling */
        .shipping-cost-value {
            font-weight: 600;
            color: #1F2937;
        }
        
        .shipping-cost-value .text-muted {
            color: #9CA3AF;
            font-weight: 500;
            font-style: italic;
        }
        
        /* Shipping Notice in Order Summary */
        .shipping-notice {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            /* Prevent horizontal scroll */
            body {
                overflow-x: hidden;
            }
            
            .checkout-wrapper,
            .checkout-container {
                padding: 16px 12px;
                max-width: 100%;
                overflow-x: hidden;
            }
            
            /* Fix form row stacking */
            .form-row {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            /* Wider input fields on mobile */
            .form-input,
            input[type="text"],
            input[type="email"],
            input[type="tel"],
            textarea,
            select {
                width: 100% !important;
                min-width: 100%;
                max-width: 100%;
                padding: 14px 16px;
                font-size: 16px; /* Prevent iOS zoom */
                box-sizing: border-box;
            }
            
            /* Address cards responsive */
            .address-card {
                padding: 16px;
                margin-bottom: 12px;
            }
            
            .address-card-content {
                font-size: 14px;
            }
            
            /* Payment methods responsive */
            .payment-method-item {
                padding: 16px;
            }
            
            .payment-method-label {
                font-size: 15px;
            }
            
            /* Order summary responsive */
            .order-summary {
                padding: 16px;
            }
            
            .summary-item {
                font-size: 14px;
            }
            
            /* Modal improvements */
            .modal-container {
                max-width: 100%;
                margin: 0 10px;
                width: calc(100% - 20px);
            }
            
            .confirmation-modal-content {
                max-width: 100%;
                width: calc(100% - 20px);
                margin: 10px;
            }
            
            .modal-header {
                padding: 16px;
            }
            
            .modal-body {
                padding: 16px;
            }
            
            .modal-footer {
                padding: 16px;
                flex-direction: column;
                gap: 12px;
            }
            
            /* Full width buttons on mobile */
            .btn,
            .btn-primary,
            .btn-secondary,
            .btn-modal-save,
            .btn-modal-cancel {
                width: 100%;
                justify-content: center;
                min-height: 48px;
                padding: 14px 20px;
            }
            
            /* Better spacing for touch */
            .address-card,
            .payment-method-item {
                min-height: 60px;
            }
            
            /* Confirmation modal details */
            .confirmation-details {
                font-size: 14px;
            }
            
            .detail-row {
                flex-direction: column;
                gap: 4px;
                padding: 12px 0;
            }
            
            .detail-label {
                font-weight: 600;
                font-size: 13px;
            }
            
            .detail-value {
                font-size: 14px;
            }
            
            /* Better textarea on mobile */
            textarea {
                min-height: 100px;
                resize: vertical;
            }
            
            /* Prevent layout shift */
            * {
                max-width: 100%;
            }
            
            img {
                max-width: 100%;
                height: auto;
            }
            
            /* Fix grid layouts */
            .checkout-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            /* Stack order summary at bottom on mobile */
            .order-summary-sticky {
                position: static;
                margin-top: 20px;
            }
            
            /* Terms checkbox responsive */
            .terms-checkbox-wrapper {
                padding: 14px;
                margin: 16px 0;
            }
            
            .terms-checkbox-text {
                font-size: 13px;
                line-height: 1.5;
            }
            
            .terms-checkbox-input {
                width: 18px;
                height: 18px;
                min-width: 18px;
                min-height: 18px;
            }
        }
        
        /* Extra small devices */
        @media (max-width: 480px) {
            .checkout-container {
                padding: 12px 8px;
            }
            
            .form-input,
            input,
            textarea,
            select {
                font-size: 16px; /* Consistent to prevent zoom */
            }
            
            .modal-container,
            .confirmation-modal-content {
                margin: 8px;
                width: calc(100% - 16px);
            }
            
            .btn {
                font-size: 15px;
            }
        }
    </style>

    <!-- Modal Tambah Alamat -->
    <div id="addAddressModal" class="modal-overlay" style="display: none;">
        <div class="modal-container modal-lg">
            <div class="modal-header">
                <h3>Tambah Alamat Baru</h3>
                <button type="button" class="modal-close" onclick="closeAddAddressModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <form id="addAddressForm" class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Penerima <span class="required">*</span></label>
                        <input type="text" name="recipient_name" id="modal_recipient_name" placeholder="Nama lengkap penerima" required class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label>Nomor Telepon <span class="required">*</span></label>
                        <input type="tel" name="phone" id="modal_phone" placeholder="08123456789" required class="form-input">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Alamat Lengkap <span class="required">*</span></label>
                    <textarea name="full_address" id="modal_full_address" rows="3" placeholder="Jalan, nomor rumah, RT/RW, kelurahan/desa" required class="form-input"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Kota/Kabupaten <span class="required">*</span></label>
                        <input type="text" name="city" id="modal_city" placeholder="Contoh: Surabaya" required class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label>Provinsi <span class="required">*</span></label>
                        <input type="text" name="province" id="modal_province" placeholder="Contoh: Jawa Timur" required class="form-input">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Kode Pos <span class="required">*</span></label>
                    <input type="text" name="postal_code" id="modal_postal_code" placeholder="Contoh: 60123" required class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_default" id="modal_is_default">
                        <span>Jadikan sebagai alamat default</span>
                    </label>
                </div>
                
                <input type="hidden" name="latitude" id="modal_latitude" value="0">
                <input type="hidden" name="longitude" id="modal_longitude" value="0">
            </form>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddAddressModal()">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitAddAddress()">
                    <span id="btnAddText">Simpan Alamat</span>
                    <span id="btnAddLoading" style="display: none;">
                        <svg class="spinner" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
