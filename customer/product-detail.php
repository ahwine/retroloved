<?php
/**
 * Halaman Detail Produk
 * Menampilkan informasi lengkap produk, gambar gallery, dan opsi pembelian
 * RetroLoved E-Commerce System
 */

// Mulai session
session_start();

// Include koneksi database
require_once '../config/database.php';
$base_url = '../';

// Cek status login pengguna
// Guest (tamu) bisa melihat produk, tapi tidak bisa membeli
$is_logged_in = isset($_SESSION['user_id']) && $_SESSION['role'] == 'customer';
$is_admin = isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin';
$is_guest = !isset($_SESSION['user_id']);

// Ambil ID produk dari parameter URL
$product_id = isset($_GET['id']) ? escape($_GET['id']) : 0;

// Ambil detail produk - CEK juga apakah sudah terjual
$query = "SELECT * FROM products WHERE product_id = '$product_id' AND is_active = 1";
$result = query($query);
$product = mysqli_fetch_assoc($result);

// ===== SISTEM TRACKING PRODUK YANG BARU DILIHAT (menggunakan cookies) =====
if($product) {
    // Ambil data recently viewed yang sudah ada dari cookie
    $recently_viewed = isset($_COOKIE['recently_viewed']) ? json_decode($_COOKIE['recently_viewed'], true) : [];
    
    // Pastikan hasilnya adalah array
    if(!is_array($recently_viewed)) {
        $recently_viewed = [];
    }
    
    // Hapus produk saat ini jika sudah ada di list (untuk menghindari duplikat)
    $recently_viewed = array_filter($recently_viewed, function($id) use ($product_id) {
        return $id != $product_id;
    });
    
    // Re-index array setelah filter (reset index dari 0)
    $recently_viewed = array_values($recently_viewed);
    
    // Tambahkan produk saat ini ke awal array (paling baru)
    array_unshift($recently_viewed, intval($product_id));
    
    // Simpan maksimal 6 produk terakhir saja
    $recently_viewed = array_slice($recently_viewed, 0, 6);
    
    // Simpan ke cookie dengan masa berlaku 30 hari
    setcookie('recently_viewed', json_encode($recently_viewed), time() + (30 * 24 * 60 * 60), '/', '', false, false);
    
    // PENTING: Update $_COOKIE supaya bisa langsung digunakan di request ini
    $_COOKIE['recently_viewed'] = json_encode($recently_viewed);
}

// Jika produk tidak ditemukan
if(!$product) {
    set_message('error', 'Produk tidak ditemukan!');
    header('Location: ../shop.php');
    exit();
}

// Jika produk sudah terjual, redirect dengan message
if($product['is_sold'] == 1) {
    set_message('error', 'Maaf, produk ini sudah terjual!');
    header('Location: ../shop.php');
    exit();
}

// Ambil produk lainnya (selain produk ini, max 5 produk)
$other_products_query = "SELECT * FROM products 
                         WHERE product_id != '$product_id' 
                         AND is_active = 1 
                         AND is_sold = 0 
                         ORDER BY RAND() 
                         LIMIT 5";
$other_products = query($other_products_query);

// Get Recently Viewed Products (ONLY for logged in users)
$recently_viewed_products = [];
if($is_logged_in && isset($_COOKIE['recently_viewed'])) {
    $recently_viewed_ids = json_decode($_COOKIE['recently_viewed'], true);
    
    // Ensure it's an array
    if(is_array($recently_viewed_ids) && count($recently_viewed_ids) > 0) {
        // Remove current product from recently viewed display
        $recently_viewed_ids = array_filter($recently_viewed_ids, function($id) use ($product_id) {
            return intval($id) != intval($product_id);
        });
        
        if(count($recently_viewed_ids) > 0) {
            $ids_string = implode(',', array_map('intval', $recently_viewed_ids));
            $recently_viewed_query = "SELECT * FROM products 
                                      WHERE product_id IN ($ids_string) 
                                      AND is_active = 1 
                                      AND is_sold = 0 
                                      LIMIT 4";
            $recently_viewed_products = query($recently_viewed_query);
        }
    }
}

// Cek apakah produk sudah ada di cart (hanya jika logged in)
$in_cart = false;
if($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    $in_cart = mysqli_num_rows(query("SELECT * FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'")) > 0;
}

// BELI SEKARANG - Direct to checkout (TANPA menambahkan ke cart)
if(isset($_POST['buy_now'])) {
    // Paksa login dulu
    if(!$is_logged_in) {
        set_message('error', 'Silakan login terlebih dahulu!');
        header('Location: ../index.php');
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // CEK: Apakah produk masih available (not sold)
    $product_check = mysqli_fetch_assoc(query("SELECT is_sold, is_active FROM products WHERE product_id = '$product_id'"));
    
    if($product_check['is_sold'] == 1) {
        set_message('error', 'Maaf, produk ini sudah terjual!');
        header("Location: ../shop.php");
        exit();
    }
    
    if($product_check['is_active'] == 0) {
        set_message('error', 'Maaf, produk ini sudah tidak tersedia!');
        header("Location: ../shop.php");
        exit();
    }
    
    // PERBAIKAN: Simpan product_id di session untuk direct checkout
    // TIDAK menambahkan ke cart database
    $_SESSION['direct_checkout_product'] = $product_id;
    
    // Langsung redirect ke checkout
    header("Location: checkout.php?direct=1");
    exit();
}

// TAMBAH KE CART
if(isset($_POST['add_to_cart'])) {
    // Paksa login dulu
    if(!$is_logged_in) {
        set_message('error', 'Silakan login terlebih dahulu untuk menambahkan ke cart!');
        header('Location: ../index.php');
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // CEK: Apakah produk masih available (not sold)
    $product_check = mysqli_fetch_assoc(query("SELECT is_sold, is_active FROM products WHERE product_id = '$product_id'"));
    
    if($product_check['is_sold'] == 1) {
        set_message('error', 'Maaf, produk ini sudah terjual!');
        header("Location: ../shop.php");
        exit();
    }
    
    if($product_check['is_active'] == 0) {
        set_message('error', 'Maaf, produk ini sudah tidak tersedia!');
        header("Location: ../shop.php");
        exit();
    }
    
    // Cek apakah produk sudah ada di cart
    if(mysqli_num_rows(query("SELECT * FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'")) > 0) {
        $_SESSION['toast_message'] = 'Produk sudah ada di cart!';
        $_SESSION['toast_type'] = 'info';
    } 
    else {
        // Insert baru jika belum ada
        query("INSERT INTO cart (user_id, product_id) VALUES ('$user_id', '$product_id')");
        $_SESSION['toast_message'] = 'Produk berhasil ditambahkan ke cart!';
        $_SESSION['toast_type'] = 'success';
    }
    header("Location: product-detail.php?id=$product_id");
    exit();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['product_name']; ?> - RetroLoved</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/toast.css">
    <link rel="stylesheet" href="../assets/css/performance.css">
    <style>
        /* Image Gallery Styles */
        .product-gallery {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .main-image-container {
            position: relative;
            width: 100%;
            background: #f8f9fa;
            border-radius: 16px;
            overflow: hidden;
            aspect-ratio: 1/1;
            cursor: zoom-in;
        }
        
        .main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .main-image-container:hover .main-image {
            transform: scale(1.05);
        }
        
        /* Zoom Icon */
        .zoom-icon {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(0, 0, 0, 0.75);
            color: white;
            padding: 10px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            pointer-events: none;
        }
        
        /* Lightbox Modal */
        .lightbox-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .lightbox-modal.active {
            display: flex;
            opacity: 1;
        }
        
        .lightbox-content {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .lightbox-image {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 8px;
            animation: zoomIn 0.3s ease;
        }
        
        @keyframes zoomIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .lightbox-close {
            position: absolute;
            top: -50px;
            right: 0;
            background: white;
            color: #1F2937;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .lightbox-close:hover {
            background: #F3F4F6;
            transform: rotate(90deg);
        }
        
        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.9);
            color: #1F2937;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            opacity: 0.8;
        }
        
        .lightbox-nav:hover {
            background: white;
            transform: translateY(-50%) scale(1.1);
            opacity: 1;
        }
        
        .lightbox-nav:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        
        .lightbox-nav.prev {
            left: 20px;
        }
        
        .lightbox-nav.next {
            right: 20px;
        }
        
        .lightbox-counter {
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.9);
            color: #1F2937;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .thumbnail-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        
        .thumbnail {
            position: relative;
            aspect-ratio: 1/1;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .thumbnail:hover {
            border-color: #D97706;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
        }
        
        .thumbnail.active {
            border-color: #D97706;
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.2);
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .thumbnail-badge {
            position: absolute;
            bottom: 8px;
            right: 8px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }
        
        /* Modern Product Info Section */
        .product-price-section {
            margin: 24px 0;
            padding: 20px;
            background: #F9FAFB;
            border-radius: 12px;
            border: 1px solid #E5E7EB;
        }
        
        .price-label {
            display: block;
            font-size: 13px;
            color: #6B7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .product-price-large {
            font-size: 32px;
            font-weight: 700;
            color: #D97706;
        }
        
        /* Modern Action Buttons */
        .product-actions-modern {
            margin: 24px 0;
        }
        
        .action-buttons-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .btn-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 24px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .btn-buy-now {
            background: #D97706;
            color: white;
            border: 2px solid #B45309;
        }
        
        .btn-buy-now:hover:not(:disabled) {
            background: #B45309;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(217, 119, 6, 0.3);
        }
        
        .btn-add-cart {
            background: white;
            color: #D97706;
            border: 2px solid #D97706;
        }
        
        .btn-add-cart:hover:not(:disabled) {
            background: #FEF3C7;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(217, 119, 6, 0.2);
        }
        
        .btn-action:active:not(:disabled) {
            transform: translateY(0);
        }
        
        /* Info Box */
        .info-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 16px;
        }
        
        .info-box svg {
            flex-shrink: 0;
        }
        
        .info-warning {
            background: #FEF3C7;
            border: 1px solid #FCD34D;
            color: #92400E;
        }
        
        .info-success {
            background: #D1FAE5;
            border: 1px solid #6EE7B7;
            color: #065F46;
        }
        
        /* Product Description */
        .product-description-full {
            margin: 32px 0;
            padding: 24px;
            background: white;
            border-radius: 12px;
            border: 1px solid #E5E7EB;
        }
        
        .product-description-full h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1F2937;
            margin: 0 0 16px 0;
        }
        
        .product-description-full p {
            font-size: 15px;
            line-height: 1.7;
            color: #4B5563;
            margin: 0;
        }
        
        /* Back Link */
        .back-link-modern {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            color: #6B7280;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .back-link-modern:hover {
            background: #E5E7EB;
            color: #374151;
            transform: translateX(-4px);
        }
        
        @media (max-width: 768px) {
            .action-buttons-row {
                grid-template-columns: 1fr;
            }
            
            .product-price-large {
                font-size: 28px;
            }
            
            .btn-action {
                padding: 14px 20px;
                font-size: 15px;
            }
        }
        
        .image-counter {
            position: absolute;
            bottom: 16px;
            left: 16px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .thumbnail-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        
        /* Size Guide Link */
        .size-guide-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: #FEF3C7;
            border: 2px solid #F59E0B;
            border-radius: 10px;
            color: #92400E;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .size-guide-link:hover {
            background: #FCD34D;
            border-color: #D97706;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.2);
        }
        
        .size-guide-link svg {
            flex-shrink: 0;
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
                <span class="breadcrumb-item active"><?php echo htmlspecialchars($product['product_name']); ?></span>
            </nav>
        </div>
    </div>

    <!-- PRODUCT DETAIL SECTION -->
    <section class="product-detail-section">
        <div class="container">
            <?php display_message(); ?>
            
            <div class="product-detail">
                <!-- Image Gallery -->
                <div class="product-gallery">
                    <!-- Main Image Display -->
                    <div class="main-image-container" onclick="openLightbox()">
                        <img id="mainImage" 
                             src="../assets/images/products/<?php echo $product['image_url']; ?>" 
                             class="main-image critical"
                             alt="<?php echo $product['product_name']; ?>"
                             data-fallback="../assets/images/products/placeholder.jpg">
                        <div class="zoom-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                <line x1="11" y1="8" x2="11" y2="14"></line>
                                <line x1="8" y1="11" x2="14" y2="11"></line>
                            </svg>
                            Klik untuk Zoom
                        </div>
                        <div class="image-counter">
                            <span id="currentImageNumber">1</span> / <span id="totalImages">1</span>
                        </div>
                    </div>
                    
                    <!-- Thumbnail Grid -->
                    <div class="thumbnail-grid">
                        <?php 
                        // Collect all available images
                        $images = [];
                        if(!empty($product['image_url'])) {
                            $images[] = ['url' => $product['image_url'], 'label' => 'Utama'];
                        }
                        if(!empty($product['image_url_2'])) {
                            $images[] = ['url' => $product['image_url_2'], 'label' => 'Tampilan 2'];
                        }
                        if(!empty($product['image_url_3'])) {
                            $images[] = ['url' => $product['image_url_3'], 'label' => 'Tampilan 3'];
                        }
                        if(!empty($product['image_url_4'])) {
                            $images[] = ['url' => $product['image_url_4'], 'label' => 'Tampilan 4'];
                        }
                        
                        // Display thumbnails
                        foreach($images as $index => $image):
                        ?>
                            <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 onclick="changeImage('<?php echo $image['url']; ?>', <?php echo $index; ?>)">
                                <img data-src="../assets/images/products/<?php echo $image['url']; ?>" 
                                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect fill='%23f0f0f0' width='100' height='100'/%3E%3C/svg%3E"
                                     alt="<?php echo $product['product_name']; ?> - <?php echo $image['label']; ?>"
                                     loading="lazy"
                                     data-fallback="../assets/images/products/placeholder.jpg">
                                <span class="thumbnail-badge"><?php echo $image['label']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="product-detail-info">
                    <h1><?php echo $product['product_name']; ?></h1>
                    
                    <div class="product-meta">
                        <span class="meta-category"><?php echo $product['category']; ?></span>
                        <span class="meta-condition"><?php echo $product['condition_item']; ?></span>
                    </div>
                    
                    <div class="product-price-section">
                        <span class="price-label">Harga</span>
                        <div class="product-price-large">
                            Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                        </div>
                    </div>
                    
                    <!-- Size Guide Link -->
                    <a href="../size-guide.php" target="_blank" class="size-guide-link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Lihat Size Guide</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                    </a>
                    
                    <div class="product-description-full">
                        <h3>Deskripsi Produk</h3>
                        <p><?php echo nl2br($product['description']); ?></p>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="product-actions-modern">
                            <?php if($is_admin): ?>
                                <!-- Admin view - Hanya bisa lihat, tidak bisa beli -->
                                <div class="action-buttons-row">
                                    <button type="button" class="btn-action btn-buy-now" onclick="showAdminWarning()">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        Beli Sekarang
                                    </button>
                                    <button type="button" class="btn-action btn-add-cart" onclick="showAdminWarning()">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="9" cy="21" r="1"></circle>
                                            <circle cx="20" cy="21" r="1"></circle>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                        </svg>
                                        Tambah ke Cart
                                    </button>
                                </div>
                                <div class="info-box info-warning">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                    </svg>
                                    <span><strong>Admin</strong> tidak dapat melakukan pembelian produk</span>
                                </div>
                            <?php elseif($is_guest): ?>
                                <!-- Guest view -->
                                <div class="action-buttons-row">
                                    <button type="button" class="btn-action btn-buy-now" onclick="showLoginModal()">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        Beli Sekarang
                                    </button>
                                    <button type="button" class="btn-action btn-add-cart" onclick="showLoginModal()">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="9" cy="21" r="1"></circle>
                                            <circle cx="20" cy="21" r="1"></circle>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                        </svg>
                                        Tambah ke Cart
                                    </button>
                                </div>
                                <div class="info-box info-warning">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                    </svg>
                                    <span>Silakan <strong>Login</strong> terlebih dahulu untuk membeli</span>
                                </div>
                            <?php else: ?>
                                <!-- Logged in view -->
                                <div class="action-buttons-row">
                                    <?php if($in_cart): ?>
                                        <button type="submit" name="buy_now" class="btn-action btn-buy-now">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            Beli Sekarang
                                        </button>
                                        <button type="button" class="btn-action btn-add-cart product-detail-btn-disabled" disabled>
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                            Sudah di Cart
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="buy_now" class="btn-action btn-buy-now">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            Beli Sekarang
                                        </button>
                                        <button type="submit" name="add_to_cart" class="btn-action btn-add-cart">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="9" cy="21" r="1"></circle>
                                                <circle cx="20" cy="21" r="1"></circle>
                                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                            </svg>
                                            Tambah ke Cart
                                        </button>
                                    <?php endif; ?>
                                </div>
                            
                                <?php if($in_cart): ?>
                                    <div class="info-box info-success">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        <span>Produk ini sudah ada di <strong>Keranjang</strong> Anda</span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </form>
                    
                    <a href="../shop.php" class="back-link-modern">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Kembali ke Katalog
                    </a>
                </div>
            </div>
        </div>
    </section>


    <!-- OTHER PRODUCTS SECTION -->
    <?php if(mysqli_num_rows($other_products) > 0): ?>
    <section class="other-products-section">
        <div class="container">
            <div class="other-products-header">
                <h2 class="other-products-title">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    Produk Lainnya
                </h2>
                <a href="../shop.php" class="view-all-link">
                    Lihat Semua
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>
            
            <div class="other-products-grid">
                <?php 
                mysqli_data_seek($other_products, 0); // Reset pointer
                while($other_product = mysqli_fetch_assoc($other_products)): 
                ?>
                    <a href="product-detail.php?id=<?php echo $other_product['product_id']; ?>" class="product-card">
                        <div class="product-image">
                            <div class="product-image-link">
                                <img class="primary-image" 
                                     data-src="../assets/images/products/<?php echo $other_product['image_url']; ?>" 
                                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 400'%3E%3Crect fill='%23f0f0f0' width='400' height='400'/%3E%3C/svg%3E"
                                     alt="<?php echo $other_product['product_name']; ?>"
                                     loading="lazy"
                                     data-fallback="../assets/images/products/placeholder.jpg">
                                
                                <?php if(!empty($other_product['image_url_2'])): ?>
                                <img class="secondary-image" 
                                     data-src="../assets/images/products/<?php echo $other_product['image_url_2']; ?>" 
                                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 400'%3E%3Crect fill='%23f0f0f0' width='400' height='400'/%3E%3C/svg%3E"
                                     alt="<?php echo $other_product['product_name']; ?>"
                                     loading="lazy"
                                     data-fallback="../assets/images/products/<?php echo $other_product['image_url']; ?>">
                                <?php endif; ?>
                                
                                <span class="product-condition"><?php echo $other_product['condition_item']; ?></span>
                            </div>
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?php echo $other_product['category']; ?></span>
                            <h3 class="product-name"><?php echo $other_product['product_name']; ?></h3>
                            <div class="product-footer">
                                <span class="product-price">Rp <?php echo number_format($other_product['price'], 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Lightbox Modal -->
    <div class="lightbox-modal" id="lightboxModal">
        <div class="lightbox-content">
            <button class="lightbox-close" onclick="closeLightbox()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <button class="lightbox-nav prev" onclick="lightboxPrev()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <img id="lightboxImage" class="lightbox-image" src="" alt="">
            <button class="lightbox-nav next" onclick="lightboxNext()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
            <div class="lightbox-counter">
                <span id="lightboxCounter">1 / 1</span>
            </div>
        </div>
    </div>

    <!-- Toast Notification Script -->
    <script src="../assets/js/lazy-load.js"></script>
    <script src="../assets/js/toast.js"></script>
    
    <!-- Product Lightbox Script -->
    <script src="../assets/js/product-lightbox.js"></script>
    
    <!-- Initialize Gallery -->
    <script>
        // Initialize gallery with images from PHP
        const galleryImages = [
            <?php 
            $image_urls = [];
            foreach($images as $img) {
                $image_urls[] = "'" . $img['url'] . "'";
            }
            echo implode(', ', $image_urls);
            ?>
        ];
        
        const totalImagesCount = <?php echo count($images); ?>;
        
        // Initialize gallery when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initializeGallery(galleryImages, totalImagesCount);
            });
        } else {
            initializeGallery(galleryImages, totalImagesCount);
        }
        
        // Show toast notification if exists
        <?php if(isset($_SESSION['toast_message'])): ?>
            <?php if($_SESSION['toast_type'] == 'success'): ?>
                toastSuccess('<?php echo addslashes($_SESSION['toast_message']); ?>');
            <?php elseif($_SESSION['toast_type'] == 'error'): ?>
                toastError('<?php echo addslashes($_SESSION['toast_message']); ?>');
            <?php elseif($_SESSION['toast_type'] == 'info'): ?>
                toastInfo('<?php echo addslashes($_SESSION['toast_message']); ?>');
            <?php endif; ?>
            <?php 
            unset($_SESSION['toast_message']);
            unset($_SESSION['toast_type']);
            ?>
        <?php endif; ?>
    </script>
    
    <!-- Admin Warning Script -->
    <script>
        function showAdminWarning() {
            // Create overlay
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.65);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.3s;
            `;
            
            // Create warning panel
            const panel = document.createElement('div');
            panel.style.cssText = `
                background: white;
                border-radius: 16px;
                padding: 32px;
                max-width: 450px;
                width: 90%;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
                transform: scale(0.9);
                opacity: 0;
                transition: all 0.3s;
                text-align: center;
            `;
            
            panel.innerHTML = `
                <div style="width: 80px; height: 80px; background: #FEF3C7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <h2 style="font-size: 24px; font-weight: 800; color: #1F2937; margin-bottom: 12px; font-family: 'Playfair Display', serif;">Akses Ditolak</h2>
                <p style="color: #6B7280; font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                    Anda login sebagai <strong>Admin</strong>. Admin tidak dapat melakukan pembelian atau menambahkan produk ke keranjang.
                    <br><br>
                    Anda hanya dapat melihat detail produk untuk keperluan pengelolaan toko.
                </p>
                <button onclick="closeAdminWarning()" style="width: 100%; padding: 14px; background: #D97706; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s;">
                    Saya Mengerti
                </button>
            `;
            
            overlay.appendChild(panel);
            document.body.appendChild(overlay);
            
            // Animate in
            setTimeout(() => {
                overlay.style.opacity = '1';
                panel.style.transform = 'scale(1)';
                panel.style.opacity = '1';
            }, 10);
            
            // Close on overlay click
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    closeAdminWarning();
                }
            });
            
            // Store reference
            window.adminWarningOverlay = overlay;
        }
        
        function closeAdminWarning() {
            const overlay = window.adminWarningOverlay;
            if (overlay) {
                const panel = overlay.querySelector('div');
                overlay.style.opacity = '0';
                panel.style.transform = 'scale(0.9)';
                panel.style.opacity = '0';
                setTimeout(() => {
                    if (overlay.parentElement) {
                        document.body.removeChild(overlay);
                    }
                }, 300);
            }
        }
    </script>
    
    <!-- Main Script -->
    <script src="../assets/js/script.js"></script>
    
    <?php include '../includes/footer.php'; ?>