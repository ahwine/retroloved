<?php
/**
 * Halaman Shop (Semua Produk)
 * Menampilkan semua produk dengan fitur pagination
 * RetroLoved E-Commerce System
 */

// Mulai session untuk tracking user
session_start();

// Include koneksi database dan fungsi helper
require_once 'config/database.php';

// Base URL untuk link (root level)
$base_url = '';

// Setup pagination - hanya produk yang aktif dan belum terjual (12 produk per halaman)
$pagination = paginate('products', 'is_active = 1 AND is_sold = 0', 12);

// Ambil produk dari database dengan pagination
// Urutkan berdasarkan tanggal dibuat (terbaru dulu)
$products = query("SELECT * FROM products 
                   WHERE is_active = 1 AND is_sold = 0
                   ORDER BY created_at DESC 
                   LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop All Products - RetroLoved</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <style>
        /* ========================================
           STICKY FOOTER FIX
           Memastikan footer selalu di bawah
        ======================================== */
        html {
            height: 100%;
        }
        
        body {
            min-height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .products-section {
            flex: 1;
        }
        
        /* ========================================
           END STICKY FOOTER FIX
        ======================================== */
        
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
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
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

    <!-- SHOP SECTION -->
    <section class="products-section" style="padding-top: 120px; padding-bottom: 60px; min-height: 400px;">
        <div class="container" style="max-width: 1140px;">
            <div style="text-align: center; margin-bottom: 40px;">
                <h2 style="font-size: 28px; font-weight: 700; color: #1F2937; margin: 0 0 8px 0;">Semua Produk</h2>
                <p style="font-size: 15px; color: #6B7280; margin: 0;">Jelajahi koleksi lengkap fashion vintage kami</p>
            </div>
            
            <div class="products-grid-compact">
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
                            <h3 class="product-name"><?php echo strlen($product['product_name']) > 40 ? substr($product['product_name'], 0, 40) . '...' : $product['product_name']; ?></h3>
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
                        <h3>Belum ada produk tersedia</h3>
                        <p>Silakan cek kembali nanti</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- PAGINATION -->
            <?php if($pagination['total_pages'] > 1): ?>
            <div class="pagination" style="display: flex; justify-content: center; gap: 8px; margin: 48px 0; flex-wrap: wrap; align-items: center;">
                <?php if($pagination['page'] > 1): ?>
                    <a href="?page=<?php echo $pagination['page']-1; ?>" 
                       class="page-btn" 
                       style="padding: 10px 16px; background: #7C3AED; color: white; text-decoration: none; border-radius: 6px; transition: all 0.3s; display: inline-flex; align-items: center; gap: 4px;">
                        ← Sebelumnya
                    </a>
                <?php endif; ?>
                
                <?php 
                // Tampilkan halaman dengan smart pagination
                $start = max(1, $pagination['page'] - 2);
                $end = min($pagination['total_pages'], $pagination['page'] + 2);
                
                if($start > 1): ?>
                    <a href="?page=1" class="page-btn" style="padding: 10px 16px; background: #E5E7EB; color: #374151; text-decoration: none; border-radius: 6px; transition: all 0.3s;">1</a>
                    <?php if($start > 2): ?>
                        <span style="color: #9CA3AF; padding: 0 8px;">...</span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="page-btn <?php echo $i == $pagination['page'] ? 'active' : ''; ?>"
                       style="padding: 10px 16px; 
                              background: <?php echo $i == $pagination['page'] ? '#7C3AED' : '#E5E7EB'; ?>; 
                              color: <?php echo $i == $pagination['page'] ? 'white' : '#374151'; ?>; 
                              text-decoration: none; 
                              border-radius: 6px; 
                              transition: all 0.3s;
                              font-weight: <?php echo $i == $pagination['page'] ? '600' : '400'; ?>;">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if($end < $pagination['total_pages']): ?>
                    <?php if($end < $pagination['total_pages'] - 1): ?>
                        <span style="color: #9CA3AF; padding: 0 8px;">...</span>
                    <?php endif; ?>
                    <a href="?page=<?php echo $pagination['total_pages']; ?>" class="page-btn" style="padding: 10px 16px; background: #E5E7EB; color: #374151; text-decoration: none; border-radius: 6px; transition: all 0.3s;"><?php echo $pagination['total_pages']; ?></a>
                <?php endif; ?>
                
                <?php if($pagination['page'] < $pagination['total_pages']): ?>
                    <a href="?page=<?php echo $pagination['page']+1; ?>" 
                       class="page-btn"
                       style="padding: 10px 16px; background: #7C3AED; color: white; text-decoration: none; border-radius: 6px; transition: all 0.3s; display: inline-flex; align-items: center; gap: 4px;">
                        Selanjutnya →
                    </a>
                <?php endif; ?>
            </div>
            
            <p style="text-align: center; color: #6B7280; font-size: 14px;">
                Menampilkan <?php echo (($pagination['page']-1) * $pagination['per_page']) + 1; ?> 
                - <?php echo min($pagination['page'] * $pagination['per_page'], $pagination['total_items']); ?> 
                dari <?php echo $pagination['total_items']; ?> produk
            </p>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</html>
