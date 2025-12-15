<?php
/**
 * Halaman Beranda (Home Page)
 * Menampilkan hero banner, produk featured, dan produk yang baru dilihat
 * RetroLoved E-Commerce System
 */

// Mulai session untuk tracking user
session_start();

// Include koneksi database dan fungsi helper
require_once 'config/database.php';

// Base URL untuk link (root level)
$base_url = '';

// Ambil hanya featured products yang belum terjual (maksimal 6 produk)
$products = query("SELECT * FROM products WHERE is_active = 1 AND is_featured = 1 AND is_sold = 0 ORDER BY created_at DESC LIMIT 6");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RetroLoved - Vintage Fashion Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <style>
        /* ========================================
           MODERN PRODUCT CARD DESIGN
           Professional, Clean & Consistent
        ======================================== */
        
        /* Grid Layouts */
        .products-grid-compact {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        
        .products-grid-featured {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-bottom: 40px;
        }
        
        /* Product Card Modern */
        .product-card-modern {
            display: block;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #E5E7EB;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: inherit;
        }
        
        .product-card-modern:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
            border-color: #D97706;
        }
        
        /* Image Wrapper */
        .product-image-wrapper {
            position: relative;
            width: 100%;
            padding-top: 100%; /* 1:1 Aspect Ratio */
            overflow: hidden;
            background: #F9FAFB;
        }
        
        .product-img-primary,
        .product-img-secondary {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: all 0.4s ease;
        }
        
        .product-img-secondary {
            opacity: 0;
        }
        
        .product-card-modern:hover .product-img-primary {
            transform: scale(1.08);
        }
        
        .product-card-modern:hover .product-img-secondary {
            opacity: 1;
            transform: scale(1.08);
        }
        
        /* Product Badge */
        .product-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(0, 0, 0, 0.85);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
        }
        
        /* Product Details */
        .product-details {
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            background: white;
        }
        
        .product-name {
            font-size: 13px;
            font-weight: 400;
            color: #6B7280;
            margin: 0;
            line-height: 1.3;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-category {
            display: none;
        }
        
        .product-price-row {
            display: flex;
            align-items: baseline;
            gap: 8px;
            order: -1;
        }
        
        .product-price-main {
            font-size: 16px;
            font-weight: 700;
            color: #1F2937;
        }
        
        .product-price-old {
            font-size: 13px;
            color: #9CA3AF;
            text-decoration: line-through;
            font-weight: 400;
        }
        
        /* Button Hover */
        .btn-primary:hover {
            background: #B45309 !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(217, 119, 6, 0.3);
        }
        
        /* ========================================
           RESPONSIVE DESIGN
        ======================================== */
        
        @media (max-width: 1024px) {
            .products-grid-featured {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            .products-grid-compact {
                grid-template-columns: repeat(3, 1fr);
                gap: 18px;
            }
        }
        
        @media (max-width: 768px) {
            .products-grid-featured,
            .products-grid-compact {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }
            
            .featured-products-section,
            .recently-viewed-section {
                padding: 40px 0 !important;
            }
            
            .product-name {
                font-size: 12px;
            }
            
            .product-price-main {
                font-size: 15px;
            }
            
            .product-price-old {
                font-size: 12px;
            }
            
            .product-details {
                padding: 10px 12px;
            }
        }
        
        @media (max-width: 480px) {
            .product-card-modern:hover {
                transform: translateY(-3px);
            }
            
            .product-badge {
                font-size: 10px;
                padding: 4px 8px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- HERO SECTION - MINIMALIST PROFESSIONAL -->
    <section class="hero-minimal">
        <div class="hero-minimal-container">
            <div class="hero-minimal-overlay"></div>
            <img src="assets/images/hero-banner.jpg" alt="RetroLoved Vintage Collection" class="hero-minimal-bg">
            
            <div class="hero-minimal-content">
                <div class="container">
                    <div class="hero-minimal-inner">
                        <h1 class="hero-minimal-title">Penjualan Baju<br>Preloved & Vintage</h1>
                        <p class="hero-minimal-subtitle">Temukan fashion berkualitas dengan harga terjangkau</p>
                        <div class="hero-minimal-buttons">
                            <a href="shop.php" class="hero-btn-white">Lihat Koleksi</a>
                            <a href="#products" class="hero-btn-outline">Produk Pilihan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION PRODUK YANG BARU DILIHAT -->
    <?php
    /**
     * Ambil produk yang baru saja dilihat dari cookie
     * Fitur ini hanya aktif untuk pengguna yang sudah login (customer atau admin)
     */
    $recently_viewed_products = null;
    $is_logged_in = isset($_SESSION['user_id']);
    
    // Cek apakah user sudah login dan ada cookie recently_viewed
    if($is_logged_in && isset($_COOKIE['recently_viewed'])) {
        // Decode JSON dari cookie menjadi array
        $recently_viewed_ids = json_decode($_COOKIE['recently_viewed'], true);
        
        // Pastikan hasilnya adalah array dan memiliki item
        if(is_array($recently_viewed_ids) && count($recently_viewed_ids) > 0) {
            // Bersihkan dan validasi ID produk (harus integer positif)
            $recently_viewed_ids = array_filter(array_map('intval', $recently_viewed_ids), function($id) {
                return $id > 0;
            });
            
            // Jika masih ada ID yang valid setelah dibersihkan
            if(count($recently_viewed_ids) > 0) {
                // Gabungkan ID menjadi string untuk query SQL
                $ids_string = implode(',', $recently_viewed_ids);
                
                // Query produk berdasarkan ID yang ada di cookie
                $recently_viewed_query = "SELECT * FROM products 
                                          WHERE product_id IN ($ids_string) 
                                          AND is_active = 1 
                                          AND is_sold = 0 
                                          ORDER BY FIELD(product_id, $ids_string)
                                          LIMIT 4";
                $recently_viewed_products = query($recently_viewed_query);
            }
        }
    }
    ?>
    
    <?php 
    // Tampilkan section ini hanya jika ada produk yang pernah dilihat
    if($recently_viewed_products && mysqli_num_rows($recently_viewed_products) > 0): 
    ?>
    <section class="recently-viewed-section" style="padding: 50px 0 30px; background: #F9FAFB;">
        <div class="container" style="max-width: 1140px;">
            <div style="text-align: center; margin-bottom: 35px;">
                <h2 style="font-size: 26px; font-weight: 700; color: #1F2937; margin: 0 0 8px 0; display: inline-flex; align-items: center; gap: 10px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    Baru Dilihat
                </h2>
                <p style="font-size: 14px; color: #6B7280; margin: 0;">Produk yang baru saja Anda lihat</p>
            </div>
            
            <div class="products-grid-compact">
                <?php 
                // Loop setiap produk yang baru dilihat
                while($recent_product = mysqli_fetch_assoc($recently_viewed_products)): 
                ?>
                    <a href="customer/product-detail.php?id=<?php echo $recent_product['product_id']; ?>" class="product-card-modern">
                        <div class="product-image-wrapper">
                            <img class="product-img-primary" 
                                 src="assets/images/products/<?php echo $recent_product['image_url']; ?>" 
                                 alt="<?php echo $recent_product['product_name']; ?>"
                                 onerror="this.src='assets/images/products/placeholder.jpg'">
                            <?php if(!empty($recent_product['image_url_2'])): ?>
                            <img class="product-img-secondary" 
                                 src="assets/images/products/<?php echo $recent_product['image_url_2']; ?>" 
                                 alt="<?php echo $recent_product['product_name']; ?>"
                                 onerror="this.src='assets/images/products/<?php echo $recent_product['image_url']; ?>'">
                            <?php endif; ?>
                            <span class="product-badge"><?php echo $recent_product['condition_item']; ?></span>
                        </div>
                        <div class="product-details">
                            <h3 class="product-name"><?php echo strlen($recent_product['product_name']) > 40 ? substr($recent_product['product_name'], 0, 40) . '...' : $recent_product['product_name']; ?></h3>
                            <p class="product-category"><?php echo $recent_product['category']; ?></p>
                            <div class="product-price-row">
                                <span class="product-price-main">Rp <?php echo number_format($recent_product['price'], 0, ',', '.'); ?></span>
                                <?php if(!empty($recent_product['original_price']) && $recent_product['original_price'] > $recent_product['price']): ?>
                                    <span class="product-price-old">Rp <?php echo number_format($recent_product['original_price'], 0, ',', '.'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- FEATURED PRODUCTS SECTION -->
    <section class="featured-products-section" id="products" style="padding: 50px 0 70px; background: white;">
        <div class="container" style="max-width: 1140px;">
            <div style="text-align: center; margin-bottom: 40px;">
                <h2 style="font-size: 28px; font-weight: 700; color: #1F2937; margin: 0 0 8px 0;">Produk Pilihan</h2>
                <p style="font-size: 15px; color: #6B7280; margin: 0;">Koleksi pilihan terbaik kami yang siap melengkapi gaya vintage Anda</p>
            </div>
            
            <div class="products-grid-featured">
                <?php while($product = mysqli_fetch_assoc($products)): ?>
                    <a href="customer/product-detail.php?id=<?php echo $product['product_id']; ?>" class="product-card-modern">
                        <div class="product-image-wrapper">
                            <img class="product-img-primary" 
                                 src="assets/images/products/<?php echo $product['image_url']; ?>" 
                                 alt="<?php echo $product['product_name']; ?>"
                                 onerror="this.src='assets/images/products/placeholder.jpg'">
                            <?php if(!empty($product['image_url_2'])): ?>
                            <img class="product-img-secondary" 
                                 src="assets/images/products/<?php echo $product['image_url_2']; ?>" 
                                 alt="<?php echo $product['product_name']; ?>"
                                 onerror="this.src='assets/images/products/<?php echo $product['image_url']; ?>'">
                            <?php endif; ?>
                            <span class="product-badge"><?php echo $product['condition_item']; ?></span>
                        </div>
                        <div class="product-details">
                            <h3 class="product-name"><?php echo strlen($product['product_name']) > 50 ? substr($product['product_name'], 0, 50) . '...' : $product['product_name']; ?></h3>
                            <p class="product-category"><?php echo $product['category']; ?></p>
                            <div class="product-price-row">
                                <span class="product-price-main">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                <?php if(!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                    <span class="product-price-old">Rp <?php echo number_format($product['original_price'], 0, ',', '.'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
                
                <?php if(mysqli_num_rows($products) == 0): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: #999;">
                        <h3>Belum ada featured products</h3>
                        <p>Silakan cek kembali nanti</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- View All Button -->
            <div style="text-align: center; margin-top: 40px;">
                <a href="shop.php" class="btn btn-primary" style="display: inline-block; padding: 14px 40px; background: #D97706; color: white; text-decoration: none; border-radius: 10px; font-weight: 600; font-size: 15px; transition: all 0.3s ease; border: 2px solid #D97706;">
                    Lihat Semua Produk →
                </a>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="about-content">
                <h2>Tentang RetroLoved</h2>
                <p>RetroLoved adalah platform e-commerce yang menghadirkan koleksi fashion vintage dan preloved berkualitas tinggi. Kami percaya bahwa setiap pakaian memiliki cerita dan nilai yang layak untuk diteruskan. Dengan berbelanja di RetroLoved, Anda tidak hanya mendapatkan fashion unik, tetapi juga berkontribusi pada gaya hidup berkelanjutan.</p>
                <div class="about-features">
                    <div class="feature">
                        <span class="feature-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                            </svg>
                        </span>
                        <h3>Vintage Asli</h3>
                        <p>Produk original dari era 70s-90s yang telah diverifikasi kualitasnya</p>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </span>
                        <h3>Kualitas Terjamin</h3>
                        <p>Setiap item diinspeksi dengan teliti untuk memastikan kondisi terbaik</p>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="3" width="15" height="13"></rect>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                            </svg>
                        </span>
                        <h3>Pengiriman Cepat</h3>
                        <p>Pengiriman cepat dan aman ke seluruh Indonesia</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</html>
