<?php
/**
 * Halaman Keranjang Belanja (Shopping Cart)
 * Menampilkan daftar produk yang ditambahkan ke keranjang oleh customer
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

// PROSES HAPUS ITEM DARI CART
if(isset($_GET['remove'])) {
    $cart_id = escape($_GET['remove']);
    // Hapus item dari cart berdasarkan cart_id dan user_id
    query("DELETE FROM cart WHERE cart_id = '$cart_id' AND user_id = '$user_id'");
    header('Location: cart.php');
    exit();
}

// Ambil data cart dengan join ke products - cek juga status is_active dan is_sold
$cart_items = query("SELECT c.cart_id, c.product_id,
                            p.product_name, p.price, p.image_url, p.is_active, p.is_sold
                     FROM cart c 
                     JOIN products p ON c.product_id = p.product_id 
                     WHERE c.user_id = '$user_id'
                     ORDER BY c.cart_id DESC");

// Hitung total dan kategorikan produk
$total = 0;
$available_items = [];
$sold_out_items = [];

while($item = mysqli_fetch_assoc($cart_items)) {
    $price = floatval($item['price']);
    $item['price'] = $price;
    
    // CEK: Jika produk inactive, hapus otomatis
    if($item['is_active'] == 0) {
        query("DELETE FROM cart WHERE cart_id = '{$item['cart_id']}'");
        continue;
    }
    
    // CEK: Jika produk sudah terjual, pisahkan ke sold_out_items (JANGAN DIHAPUS)
    if($item['is_sold'] == 1) {
        $sold_out_items[] = $item;
    } else {
        // Produk masih tersedia, tambahkan ke total
        $total += $price;
        $available_items[] = $item;
    }
}

// Gabungkan data: available items dulu, sold out items di bawah
$cart_data = array_merge($available_items, $sold_out_items);

// Tampilkan info jika ada produk sold out
if(count($sold_out_items) > 0) {
    $sold_names = array_map(function($item) { return $item['product_name']; }, $sold_out_items);
    $message = 'Beberapa produk di cart Anda sudah terjual oleh customer lain: ' . implode(', ', $sold_names);
    set_message('warning', $message);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - RetroLoved</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/toast.css">
    <style>
        body {
            background: white !important;
        }
        
        .cart-section {
            min-height: calc(100vh - 80px);
            background: white;
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
                <a href="../shop.php" class="breadcrumb-item">Produk</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-item active">Keranjang</span>
            </nav>
        </div>
    </div>

    <!-- CART SECTION -->
    <section class="cart-section">
        <div class="container">
            <h2 class="section-title">Keranjang Belanja</h2>
            
            <?php display_message(); ?>
            
            <?php if(count($cart_data) > 0): ?>
                <div class="cart-layout">
                    <div class="cart-items">
                        <div class="cart-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($cart_data as $item): ?>
                                        <?php $is_sold_out = ($item['is_sold'] == 1); ?>
                                        <tr class="<?php echo $is_sold_out ? 'cart-item-sold-out' : ''; ?>">
                                            <td>
                                                <div class="cart-product-info">
                                                    <div style="position: relative;">
                                                        <img src="../assets/images/products/<?php echo $item['image_url']; ?>" 
                                                             class="cart-item-img <?php echo $is_sold_out ? 'img-sold-out' : ''; ?>"
                                                             alt="<?php echo $item['product_name']; ?>"
                                                             onerror="this.src='../assets/images/products/placeholder.jpg'">
                                                    </div>
                                                    <div>
                                                        <a href="product-detail.php?id=<?php echo $item['product_id']; ?>" 
                                                           class="cart-product-link <?php echo $is_sold_out ? 'sold-out' : ''; ?>">
                                                            <?php echo $item['product_name']; ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="<?php echo $is_sold_out ? 'price-sold-out' : ''; ?>">
                                                Rp <?php echo number_format($item['price'], 0, ',', '.'); ?>
                                            </td>
                                            <td>
                                                <a href="cart.php?remove=<?php echo $item['cart_id']; ?>" 
                                                   class="btn-delete">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                    Hapus
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="cart-summary">
                        <h3>Ringkasan Belanja</h3>
                        <div class="summary-item">
                            <span>Total Item:</span>
                            <strong><?php echo count($available_items); ?></strong>
                        </div>
                        <?php if(count($sold_out_items) > 0): ?>
                            <div class="summary-item" style="color: #DC2626;">
                                <span>Terjual:</span>
                                <strong><?php echo count($sold_out_items); ?></strong>
                            </div>
                        <?php endif; ?>
                        <div class="total-row">
                            <span>Total:</span>
                            <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                        </div>
                        <?php if(count($available_items) > 0): ?>
                            <a href="checkout.php" class="btn btn-primary btn-checkout">
                                Lanjut ke Checkout
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 6px;">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-primary btn-checkout" disabled style="opacity: 0.5; cursor: not-allowed;">
                                Tidak Ada Item Tersedia
                            </button>
                        <?php endif; ?>
                        <a href="../shop.php" class="btn btn-secondary btn-continue">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                            Lanjut Belanja
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <h3>Keranjang Anda Kosong</h3>
                    <p>Belum ada produk di keranjang. Yuk mulai belanja dan temukan produk vintage favorit Anda!</p>
                    <a href="../shop.php" class="btn btn-primary">Lihat Katalog Produk</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Custom Cart Scripts -->
    <script src="../assets/js/lazy-load.js"></script>
    <script src="../assets/js/toast.js"></script>
    <script src="../assets/js/modal.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/loading.js"></script>
    <script>
        // Add loading states to cart actions
        document.addEventListener('DOMContentLoaded', function() {
            // Remove from cart
            const removeButtons = document.querySelectorAll('a[href*="remove="]');
            removeButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    showLoadingOverlay();
                });
            });
            
            // Checkout button
            const checkoutButton = document.querySelector('a[href*="checkout.php"]');
            if (checkoutButton) {
                checkoutButton.addEventListener('click', function(e) {
                    showLoadingOverlay();
                });
            }
        });
    </script>
