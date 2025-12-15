<?php
/**
 * Halaman Manajemen Produk - Admin Panel
 * Mengelola semua produk: tambah, edit, hapus, dan bulk actions
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

// Inisialisasi variabel untuk UI
$page_title = "Products";
$error = '';
$success = '';

// Cek apakah ada pesan sukses dari session
if(isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ===== PROSES BULK ACTIONS (aksi massal untuk banyak produk sekaligus) =====
if(isset($_POST['bulk_action']) && isset($_POST['selected_products'])) {
    $action = $_POST['bulk_action'];
    $selected_ids = $_POST['selected_products'];
    
    // Pastikan ada produk yang dipilih dan berupa array
    if(!empty($selected_ids) && is_array($selected_ids)) {
        // Konversi array ID menjadi string untuk query SQL
        $ids_string = implode(',', array_map('intval', $selected_ids));
        $count = count($selected_ids);
        
        // Proses action berdasarkan pilihan
        switch($action) {
            case 'delete':
                // Hapus permanen dari database (termasuk yang sudah pernah dipesan)
                // Delete related data first
                query("DELETE FROM cart WHERE product_id IN ($ids_string)");
                query("DELETE FROM order_items WHERE product_id IN ($ids_string)");
                
                // Delete products permanently
                query("DELETE FROM products WHERE product_id IN ($ids_string)");
                $_SESSION['toast_message'] = "{$count} produk berhasil dihapus permanen dari database";
                $_SESSION['toast_type'] = "success";
                break;
                
            case 'mark_sold':
                // Tandai produk sebagai terjual
                query("UPDATE products SET is_sold = 1 WHERE product_id IN ($ids_string)");
                $_SESSION['toast_message'] = "{$count} produk ditandai sebagai terjual";
                $_SESSION['toast_type'] = "success";
                break;
                
            case 'mark_available':
                // Tandai produk sebagai tersedia (batal terjual)
                query("UPDATE products SET is_sold = 0 WHERE product_id IN ($ids_string)");
                $_SESSION['toast_message'] = "{$count} produk ditandai sebagai tersedia";
                $_SESSION['toast_type'] = "success";
                break;
        }
        
        // Redirect untuk refresh halaman
        header('Location: products.php');
        exit();
    }
}

// QUICK TOGGLE SOLD STATUS - AJAX Handler
if(isset($_POST['ajax_toggle_sold'])) {
    header('Content-Type: application/json');
    $product_id = escape($_POST['product_id']);
    $current = mysqli_fetch_assoc(query("SELECT is_sold FROM products WHERE product_id = '$product_id'"));
    
    if($current) {
        $new_sold_status = $current['is_sold'] ? 0 : 1;
        query("UPDATE products SET is_sold = $new_sold_status WHERE product_id = '$product_id'");
        $new_status_text = $new_sold_status ? 'Terjual' : 'Tersedia';
        
        echo json_encode([
            'success' => true,
            'message' => "Status produk berhasil diubah menjadi {$new_status_text}!",
            'new_status' => $new_sold_status
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Produk tidak ditemukan!'
        ]);
    }
    exit();
}

// DELETE PRODUCT - Hapus permanen dari database
if(isset($_GET['delete'])) {
    $product_id = escape($_GET['delete']);
    
    // Get all image filenames first (support 10 images)
    $product = mysqli_fetch_assoc(query("SELECT image_url, image_url_2, image_url_3, image_url_4, image_url_5, image_url_6, image_url_7, image_url_8, image_url_9, image_url_10 FROM products WHERE product_id = '$product_id'"));
    
    // Delete related data first (foreign key constraints)
    query("DELETE FROM cart WHERE product_id = '$product_id'");
    query("DELETE FROM order_items WHERE product_id = '$product_id'");
    
    // Now delete the product permanently
    if(query("DELETE FROM products WHERE product_id = '$product_id'")) {
        // Delete all image files
        if($product) {
            $images = [
                $product['image_url'], 
                $product['image_url_2'], 
                $product['image_url_3'], 
                $product['image_url_4'],
                $product['image_url_5'],
                $product['image_url_6'],
                $product['image_url_7'],
                $product['image_url_8'],
                $product['image_url_9'],
                $product['image_url_10']
            ];
            foreach($images as $img) {
                if(!empty($img) && file_exists('../assets/images/products/' . $img)) {
                    unlink('../assets/images/products/' . $img);
                }
            }
        }
        $_SESSION['toast_message'] = "Produk berhasil dihapus permanen dari database!";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_message'] = "Gagal menghapus produk!";
        $_SESSION['toast_type'] = "error";
    }
    header('Location: products.php');
    exit();
}

// BUILD FILTERS QUERY
$where_conditions = ["1=1"];
$filter_category = '';
$filter_condition = '';
$filter_status = '';
$search_query = '';

// Category Filter
if(isset($_GET['category']) && !empty($_GET['category'])) {
    $filter_category = escape($_GET['category']);
    $where_conditions[] = "category = '$filter_category'";
}

// Condition Filter
if(isset($_GET['condition']) && !empty($_GET['condition'])) {
    $filter_condition = escape($_GET['condition']);
    $where_conditions[] = "condition_item = '$filter_condition'";
}

// Status Filter (Available/Sold)
if(isset($_GET['status']) && !empty($_GET['status'])) {
    $filter_status = escape($_GET['status']);
    if($filter_status == 'available') {
        $where_conditions[] = "is_sold = 0 AND is_active = 1";
    } elseif($filter_status == 'sold') {
        $where_conditions[] = "is_sold = 1";
    }
}

// Search (Product Name)
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = escape($_GET['search']);
    $where_conditions[] = "product_name LIKE '%$search_query%'";
}

$where_clause = implode(" AND ", $where_conditions);

// ===== SORTING =====
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

$allowed_sorts = ['product_name', 'price', 'created_at', 'category'];
if(!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'created_at';
}

$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';
$order_by = "$sort_by $sort_order";

// ===== PAGINATION =====
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 25;
$allowed_per_page = [10, 25, 50, 100];
if(!in_array($per_page, $allowed_per_page)) {
    $per_page = 25;
}

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);

// Count total filtered products
$count_result = mysqli_fetch_assoc(query("SELECT COUNT(*) as total FROM products WHERE $where_clause"));
$total_filtered = $count_result['total'];
$filtered_products = $total_filtered; // Alias untuk display di filter summary
$total_pages = ceil($total_filtered / $per_page);
$current_page = min($current_page, max(1, $total_pages));

$offset = ($current_page - 1) * $per_page;

// Get products with filters, sorting, and pagination
$products = query("SELECT * FROM products WHERE $where_clause ORDER BY $order_by LIMIT $per_page OFFSET $offset");

// Statistik produk
$total_products_result = query("SELECT COUNT(*) as total FROM products");
$total_products = $total_products_result ? mysqli_fetch_assoc($total_products_result)['total'] : 0;

$active_products_result = query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
$active_products = $active_products_result ? mysqli_fetch_assoc($active_products_result)['total'] : 0;

$sold_products_result = query("SELECT COUNT(*) as total FROM products WHERE is_sold = 1");
$sold_products = $sold_products_result ? mysqli_fetch_assoc($sold_products_result)['total'] : 0;

$available_products_result = query("SELECT COUNT(*) as total FROM products WHERE is_sold = 0 AND is_active = 1");
$available_products = $available_products_result ? mysqli_fetch_assoc($available_products_result)['total'] : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - RetroLoved Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=5.4">
    <link rel="stylesheet" href="../assets/css/toast.css">
</head>
<body class="admin-body">
    <script src="../assets/js/modal.js"></script>
    <script src="../assets/js/toast.js"></script>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php $page_title = "Products"; include 'includes/navbar.php'; ?>

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
                            Products
                        </li>
                    </ol>
                </nav>
                
                <!-- Page Title -->
                <div style="margin-bottom: 24px;">
                    <h1 style="font-size: 32px; font-weight: 800; color: #1a1a1a; margin: 0 0 8px 0;">Products Management</h1>
                    <p style="font-size: 15px; color: #6b7280; margin: 0;">Manage your product inventory and listings</p>
                </div>
                
                <!-- Stats Grid -->
                <div class="stats-grid stats-grid-3">
                    <div class="stat-card">
                <div class="stat-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                </div>
                <div class="stat-number"><?php echo $total_products; ?></div>
                <div class="stat-label">Total Products</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="stat-number"><?php echo $available_products; ?></div>
                <div class="stat-label">Produk Tersedia</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </div>
                <div class="stat-number"><?php echo $sold_products; ?></div>
                <div class="stat-label">Produk Terjual</div>
                    </div>
                </div>
        
                <!-- FILTERS & SEARCH TOOLBAR -->
        <div class="content-card" style="margin-bottom: 24px;">
            <div style="padding: 20px 24px;">
                <form method="GET" action="products.php" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                    <!-- Category Filter -->
                    <div style="flex: 1; min-width: 150px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #6b7280; margin-bottom: 6px;">Kategori</label>
                        <select name="category" class="filter-select" style="width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #374151;">
                            <option value="" disabled selected hidden>Semua Kategori</option>
                            <option value="Jacket" <?php echo ($filter_category == 'Jacket') ? 'selected' : ''; ?>>Jacket</option>
                            <option value="Shirt" <?php echo ($filter_category == 'Shirt') ? 'selected' : ''; ?>>Shirt</option>
                            <option value="T-Shirt" <?php echo ($filter_category == 'T-Shirt') ? 'selected' : ''; ?>>T-Shirt</option>
                            <option value="Pants" <?php echo ($filter_category == 'Pants') ? 'selected' : ''; ?>>Pants</option>
                            <option value="Jeans" <?php echo ($filter_category == 'Jeans') ? 'selected' : ''; ?>>Jeans</option>
                            <option value="Dress" <?php echo ($filter_category == 'Dress') ? 'selected' : ''; ?>>Dress</option>
                            <option value="Skirt" <?php echo ($filter_category == 'Skirt') ? 'selected' : ''; ?>>Skirt</option>
                            <option value="Sweater" <?php echo ($filter_category == 'Sweater') ? 'selected' : ''; ?>>Sweater</option>
                            <option value="Accessories" <?php echo ($filter_category == 'Accessories') ? 'selected' : ''; ?>>Accessories</option>
                            <option value="Shoes" <?php echo ($filter_category == 'Shoes') ? 'selected' : ''; ?>>Shoes</option>
                            <option value="Bag" <?php echo ($filter_category == 'Bag') ? 'selected' : ''; ?>>Bag</option>
                            <option value="Other" <?php echo ($filter_category == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <!-- Condition Filter -->
                    <div style="flex: 1; min-width: 150px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #6b7280; margin-bottom: 6px;">Kondisi</label>
                        <select name="condition" class="filter-select" style="width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #374151;">
                            <option value="" disabled selected hidden>Semua Kondisi</option>
                            <option value="Excellent" <?php echo ($filter_condition == 'Excellent') ? 'selected' : ''; ?>>Excellent</option>
                            <option value="Very Good" <?php echo ($filter_condition == 'Very Good') ? 'selected' : ''; ?>>Very Good</option>
                            <option value="Good" <?php echo ($filter_condition == 'Good') ? 'selected' : ''; ?>>Good</option>
                            <option value="Fair" <?php echo ($filter_condition == 'Fair') ? 'selected' : ''; ?>>Fair</option>
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div style="flex: 1; min-width: 150px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #6b7280; margin-bottom: 6px;">Status</label>
                        <select name="status" class="filter-select" style="width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #374151;">
                            <option value="" disabled selected hidden>Semua Status</option>
                            <option value="available" <?php echo ($filter_status == 'available') ? 'selected' : ''; ?>>Hanya Tersedia</option>
                            <option value="sold" <?php echo ($filter_status == 'sold') ? 'selected' : ''; ?>>Hanya Terjual</option>
                        </select>
                    </div>
                    
                    <!-- Search -->
                    <div style="flex: 2; min-width: 200px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #6b7280; margin-bottom: 6px;">Cari</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Nama produk..." style="width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #374151;">
                    </div>
                    
                    <!-- Actions -->
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" style="padding: 10px 20px; background: #D97706; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s;">
                            Terapkan Filter
                        </button>
                        <a href="products.php" style="padding: 10px 20px; background: #F9FAFB; color: #6B7280; border: 1px solid #E5E7EB; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block; font-size: 14px; transition: all 0.2s;">
                            Bersihkan
                        </a>
                    </div>
                </form>
                
                <!-- Filter Summary -->
                <?php if($filter_category || $filter_condition || $filter_status || $search_query): ?>
                    <div style="margin-top: 16px; padding: 12px; background: #f9fafb; border-radius: 8px; font-size: 14px; color: #6b7280;">
                        <strong>Menampilkan <?php echo $filtered_products; ?></strong> dari <?php echo $total_products; ?> produk
                        <?php if($filter_category): ?>
                            <span style="display: inline-block; margin-left: 8px; padding: 4px 10px; background: #fff; border-radius: 6px; font-size: 13px;">Kategori: <strong><?php echo $filter_category; ?></strong></span>
                        <?php endif; ?>
                        <?php if($filter_condition): ?>
                            <span style="display: inline-block; margin-left: 8px; padding: 4px 10px; background: #fff; border-radius: 6px; font-size: 13px;">Kondisi: <strong><?php echo $filter_condition; ?></strong></span>
                        <?php endif; ?>
                        <?php if($filter_status): ?>
                            <span style="display: inline-block; margin-left: 8px; padding: 4px 10px; background: #fff; border-radius: 6px; font-size: 13px;">Status: <strong><?php echo ucfirst($filter_status); ?></strong></span>
                        <?php endif; ?>
                        <?php if($search_query): ?>
                            <span style="display: inline-block; margin-left: 8px; padding: 4px 10px; background: #fff; border-radius: 6px; font-size: 13px;">Pencarian: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Toolbar: Bulk Actions, Sorting, Pagination, View Toggle -->
        <div class="content-card" style="margin-bottom: 16px;">
            <div style="padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <!-- Left: Bulk Actions & Selected Counter -->
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <span id="selectedCounter" style="font-size: 14px; color: #6B7280; display: none;">
                        <strong id="selectedCount">0</strong> selected
                    </span>
                    <div id="bulkActionsBar" style="display: none; gap: 8px;">
                        <select id="bulkActionSelect" style="padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 14px;">
                            <option value="" disabled selected hidden>Select Action</option>
                            <option value="mark_sold">Mark as Sold</option>
                            <option value="mark_available">Mark as Available</option>
                            <option value="delete">Hapus yang Dipilih</option>
                        </select>
                        <button onclick="applyBulkAction()" style="padding: 8px 16px; background: #D97706; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
                            Apply
                        </button>
                    </div>
                </div>

                <!-- Right: Sorting & Per Page -->
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <!-- Urutkan -->
                    <select onchange="applySort(this.value)" style="padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 14px;">
                        <option value="created_at_DESC" <?php echo ($sort_by == 'created_at' && $sort_order == 'DESC') ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="created_at_ASC" <?php echo ($sort_by == 'created_at' && $sort_order == 'ASC') ? 'selected' : ''; ?>>Terlama</option>
                        <option value="product_name_ASC" <?php echo ($sort_by == 'product_name' && $sort_order == 'ASC') ? 'selected' : ''; ?>>Nama A-Z</option>
                        <option value="product_name_DESC" <?php echo ($sort_by == 'product_name' && $sort_order == 'DESC') ? 'selected' : ''; ?>>Nama Z-A</option>
                        <option value="price_ASC" <?php echo ($sort_by == 'price' && $sort_order == 'ASC') ? 'selected' : ''; ?>>Harga Terendah</option>
                        <option value="price_DESC" <?php echo ($sort_by == 'price' && $sort_order == 'DESC') ? 'selected' : ''; ?>>Harga Tertinggi</option>
                    </select>

                    <!-- Per Halaman -->
                    <select onchange="changePerPage(this.value)" style="padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 14px;">
                        <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10 per halaman</option>
                        <option value="25" <?php echo $per_page == 25 ? 'selected' : ''; ?>>25 per halaman</option>
                        <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50 per halaman</option>
                        <option value="100" <?php echo $per_page == 100 ? 'selected' : ''; ?>>100 per halaman</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- STATISTICS CARDS -->
        <div class="stats-grid" style="display: none;">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                </div>
                <div class="stat-number"><?php echo $total_products; ?></div>
                <div class="stat-label">Total Products</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2">
                        <polyline points="9 11 12 14 22 4"></polyline>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                </div>
                <div class="stat-number"><?php echo $active_products; ?></div>
                <div class="stat-label">Active Products</div>
            </div>

        </div>


            <!-- PRODUCTS TABLE -->
        <div class="content-card" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 24px 28px; background: #f8f9fa;">
                <div>
                    <h3 style="font-size: 20px; font-weight: 700; margin: 0; color: #1a1a1a;">All Products</h3>
                    <p style="font-size: 13px; color: #6c757d; margin: 4px 0 0 0;">Manage your product inventory</p>
                </div>
                <a href="product-add.php" class="btn-action btn-add" style="background: #3B82F6; color: white; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Tambah Produk
                </a>
            </div>

                <form method="POST" id="bulkActionForm">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes(this)" style="width: 18px; height: 18px; cursor: pointer;">
                                </th>
                                <th style="width: 100px;">Image</th>
                                <th>Product Name</th>
                                <th style="width: 150px;">Category</th>
                                <th style="width: 150px;">Price</th>
                                <th style="width: 120px;">Condition</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            mysqli_data_seek($products, 0);
                            while($product = mysqli_fetch_assoc($products)): 
                            ?>
                                <tr>
                                    <td data-label="Select">
                                        <input type="checkbox" name="selected_products[]" value="<?php echo $product['product_id']; ?>" class="product-checkbox" onchange="updateSelectedCounter()" style="width: 18px; height: 18px; cursor: pointer;">
                                    </td>
                                    <td data-label="Image">
                                        <div style="position: relative; display: inline-block;">
                                            <img src="../assets/images/products/<?php echo $product['image_url']; ?>" 
                                                 alt="<?php echo $product['product_name']; ?>"
                                                 class="product-thumb"
                                                 onerror="this.src='../assets/images/products/placeholder.jpg'">
                                            <?php if($product['is_sold'] == 1): ?>
                                                <span style="position: absolute; top: 4px; right: 4px; background: #DC2626; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 600;">SOLD</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td data-label="Product">
                                        <div class="product-name-cell">
                                            <strong><?php echo $product['product_name']; ?></strong>
                                            <?php if($product['is_sold'] == 1): ?>
                                                <span style="display: block; color: #DC2626; font-size: 12px; margin-top: 4px; font-weight: 600;">● Terjual</span>
                                            <?php else: ?>
                                                <span style="display: block; color: #10B981; font-size: 12px; margin-top: 4px;">● Available</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td data-label="Category"><?php echo $product['category'] ? $product['category'] : '<span style="color: #999;">No Category</span>'; ?></td>
                                    <td data-label="Price"><strong>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></strong></td>
                                    <td data-label="Condition">
                                        <span class="condition-badge"><?php echo $product['condition_item']; ?></span>
                                    </td>
                                    <td data-label="Actions">
                                        <div class="action-buttons" style="display: flex; gap: 6px; flex-wrap: wrap;">
                                            <!-- Quick Toggle Sold -->
                                            <button 
                                               class="btn-action toggle-sold-btn" 
                                               data-product-id="<?php echo $product['product_id']; ?>"
                                               data-is-sold="<?php echo $product['is_sold']; ?>"
                                               style="<?php echo $product['is_sold'] ? 'background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5;' : 'background: #D1FAE5; color: #10B981; border: 1px solid #6EE7B7;'; ?> padding: 6px 12px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; gap: 4px; font-size: 13px; font-weight: 600; transition: all 0.2s; cursor: pointer; min-width: 95px;"
                                               title="Toggle Sold Status"
                                               onclick="toggleSoldStatus(<?php echo $product['product_id']; ?>, this)"
                                               >
                                                <?php if($product['is_sold']): ?>
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <line x1="15" y1="9" x2="9" y2="15"></line>
                                                        <line x1="9" y1="9" x2="15" y2="15"></line>
                                                    </svg>
                                                    <span style="font-size: 11px; font-weight: 600;">Sold</span>
                                                <?php else: ?>
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="20 6 9 17 4 12"></polyline>
                                                    </svg>
                                                    <span style="font-size: 11px; font-weight: 600;">Mark Sold</span>
                                                <?php endif; ?>
                                            </button>
                                            
                                            <!-- Edit -->
                                            <a href="product-edit.php?id=<?php echo $product['product_id']; ?>" 
                                               style="background: #D97706; color: white; border: 1px solid #B45309; padding: 6px 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-size: 13px; font-weight: 600; transition: all 0.2s;"
                                               title="Edit">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </a>
                                            
                                            <!-- Delete -->
                                            <a href="javascript:void(0)" 
                                               style="background: #DC2626; color: white; border: 1px solid #B91C1C; padding: 6px 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-size: 13px; font-weight: 600; transition: all 0.2s; cursor: pointer;"
                                               title="Delete"
                                               onclick="showConfirmModal('Apakah Anda yakin ingin menghapus produk ini? Data tidak dapat dikembalikan.', '?delete=<?php echo $product['product_id']; ?>', 'Ya, Hapus')">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            
                            <?php if($total_products == 0): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 50px; color: #999;">
                                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 16px;">
                                            <rect x="1" y="3" width="15" height="13"></rect>
                                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                        </svg>
                                        <h3>Belum Ada Produk</h3>
                                        <p>Mulai dengan menambahkan produk pertama Anda</p>
                                        <a href="product-add.php" class="btn-action btn-add" style="margin-top: 16px;">Tambah Produk</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Mobile Card View -->
                <div class="mobile-card-list">
                    <?php 
                    mysqli_data_seek($products, 0);
                    while($product = mysqli_fetch_assoc($products)): 
                    ?>
                        <div class="mobile-card-item" data-product-id="<?php echo $product['product_id']; ?>">
                            <div class="mobile-card-header">
                                <img src="../assets/images/products/<?php echo $product['image_url']; ?>" 
                                     alt="<?php echo $product['product_name']; ?>"
                                     class="mobile-card-image"
                                     onerror="this.src='../assets/images/products/placeholder.jpg'">
                                <div class="mobile-card-info">
                                    <div class="mobile-card-title"><?php echo $product['product_name']; ?></div>
                                    <div class="mobile-card-subtitle" style="font-weight: 700; color: #D97706; font-size: 15px;">
                                        Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                    </div>
                                    <div style="margin-top: 8px;">
                                        <?php if($product['is_sold'] == 1): ?>
                                            <span style="display: inline-block; background: #FEE2E2; color: #DC2626; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; border: 1px solid #FCA5A5;">
                                                ● SOLD
                                            </span>
                                        <?php else: ?>
                                            <span style="display: inline-block; background: #D1FAE5; color: #10B981; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; border: 1px solid #6EE7B7;">
                                                ● AVAILABLE
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mobile-card-meta">
                                <div class="mobile-card-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 7h-9"></path>
                                        <path d="M14 17H5"></path>
                                        <circle cx="17" cy="17" r="3"></circle>
                                        <circle cx="7" cy="7" r="3"></circle>
                                    </svg>
                                    <span><?php echo $product['category'] ? $product['category'] : 'No Category'; ?></span>
                                </div>
                                <div class="mobile-card-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    <span><?php echo $product['condition_item']; ?></span>
                                </div>
                            </div>
                            
                            <div class="mobile-card-actions">
                                <button 
                                   class="btn-action toggle-sold-btn" 
                                   data-product-id="<?php echo $product['product_id']; ?>"
                                   data-is-sold="<?php echo $product['is_sold']; ?>"
                                   style="<?php echo $product['is_sold'] ? 'background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5;' : 'background: #D1FAE5; color: #10B981; border: 1px solid #6EE7B7;'; ?> display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 13px; font-weight: 600; border-radius: 8px; cursor: pointer; min-width: 110px;"
                                   onclick="toggleSoldStatus(<?php echo $product['product_id']; ?>, this)">
                                    <?php if($product['is_sold']): ?>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg>
                                        <span>Sold</span>
                                    <?php else: ?>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        <span>Mark Sold</span>
                                    <?php endif; ?>
                                </button>
                                
                                <a href="product-edit.php?id=<?php echo $product['product_id']; ?>" 
                                   class="btn-action"
                                   style="background: #D97706; color: white; border: 1px solid #B45309; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 13px; font-weight: 600; border-radius: 8px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    <span>Edit</span>
                                </a>
                                
                                <a href="javascript:void(0)" 
                                   class="btn-action"
                                   style="background: #DC2626; color: white; border: 1px solid #B91C1C; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 13px; font-weight: 600; border-radius: 8px;"
                                   onclick="showConfirmModal('Apakah Anda yakin ingin menghapus produk ini?', '?delete=<?php echo $product['product_id']; ?>', 'Ya, Hapus')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                    <span>Hapus</span>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <?php if($total_products == 0): ?>
                        <div style="text-align: center; padding: 60px 20px; color: #999;">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 24px; opacity: 0.3;">
                                <rect x="1" y="3" width="15" height="13"></rect>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                            </svg>
                            <h3 style="font-size: 18px; font-weight: 700; color: #6B7280; margin-bottom: 8px;">No Products Yet</h3>
                            <p style="color: #9CA3AF; margin-bottom: 24px;">Start by adding your first product</p>
                            <a href="product-add.php" class="btn-action btn-add" style="display: inline-flex; align-items: center; gap: 8px; background: #3B82F6; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                Tambah Produk
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                </form>

                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <div style="padding: 16px 24px; border-top: 1px solid #E5E7EB; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <!-- Pagination Info -->
                    <div style="font-size: 14px; color: #6B7280;">
                        Menampilkan <strong><?php echo min($offset + 1, $total_filtered); ?></strong> sampai <strong><?php echo min($offset + $per_page, $total_filtered); ?></strong> dari <strong><?php echo $total_filtered; ?></strong> produk
                    </div>

                    <!-- Pagination Buttons -->
                    <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                        <?php if($current_page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" style="padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; text-decoration: none; color: #6B7280; font-size: 14px;">«</a>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>" style="padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; text-decoration: none; color: #6B7280; font-size: 14px;">‹</a>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        for($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" style="padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; text-decoration: none; font-size: 14px; <?php echo $i == $current_page ? 'background: #D97706; color: white; border-color: #D97706; font-weight: 600;' : 'color: #6B7280;'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if($current_page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>" style="padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; text-decoration: none; color: #6B7280; font-size: 14px;">›</a>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>" style="padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; text-decoration: none; color: #6B7280; font-size: 14px;">»</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
            </div>
        </div>
    </div>

    <script>
        function showConfirmModal(message, actionUrl, buttonText = 'Ya, Hapus') {
            confirmModal(message, function() {
                window.location.href = actionUrl;
            }, null, {
                title: 'Konfirmasi',
                confirmText: buttonText,
                cancelText: 'Batal',
                iconType: 'warning'
            });
        }
        
        // Show toast notifications for success/error messages
        <?php if($success): ?>
            window.addEventListener('DOMContentLoaded', function() {
                toastSuccess('<?php echo addslashes($success); ?>');
            });
        <?php endif; ?>
        
        <?php if($error): ?>
            window.addEventListener('DOMContentLoaded', function() {
                toastError('<?php echo addslashes($error); ?>');
            });
        <?php endif; ?>
        
        // Show toast for toggle actions
        <?php if(isset($_SESSION['toast_message'])): ?>
            window.addEventListener('DOMContentLoaded', function() {
                <?php 
                $toast_type = isset($_SESSION['toast_type']) ? $_SESSION['toast_type'] : 'success';
                ?>
                <?php if($toast_type === 'success'): ?>
                    toastSuccess('<?php echo addslashes($_SESSION['toast_message']); ?>');
                <?php elseif($toast_type === 'error'): ?>
                    toastError('<?php echo addslashes($_SESSION['toast_message']); ?>');
                <?php elseif($toast_type === 'warning'): ?>
                    toastWarning('<?php echo addslashes($_SESSION['toast_message']); ?>');
                <?php else: ?>
                    toastInfo('<?php echo addslashes($_SESSION['toast_message']); ?>');
                <?php endif; ?>
            });
            <?php 
            unset($_SESSION['toast_message']); 
            unset($_SESSION['toast_type']); 
            ?>
        <?php endif; ?>
    </script>
    <!-- JavaScript for Bulk Actions, Sorting, Pagination -->
    <script>
    // Toggle all checkboxes
    function toggleAllCheckboxes(source) {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = source.checked;
        });
        updateSelectedCounter();
    }

    // Update selected counter
    function updateSelectedCounter() {
        const checkboxes = document.querySelectorAll('.product-checkbox:checked');
        const count = checkboxes.length;
        const counter = document.getElementById('selectedCounter');
        const countDisplay = document.getElementById('selectedCount');
        const bulkBar = document.getElementById('bulkActionsBar');
        
        countDisplay.textContent = count;
        
        if(count > 0) {
            counter.style.display = 'block';
            bulkBar.style.display = 'flex';
        } else {
            counter.style.display = 'none';
            bulkBar.style.display = 'none';
        }
        
        // Update select all checkbox state
        const selectAll = document.getElementById('selectAll');
        const allCheckboxes = document.querySelectorAll('.product-checkbox');
        selectAll.checked = allCheckboxes.length > 0 && count === allCheckboxes.length;
    }

    // Apply bulk action
    function applyBulkAction() {
        const action = document.getElementById('bulkActionSelect').value;
        const checkboxes = document.querySelectorAll('.product-checkbox:checked');
        
        if(!action) {
            toastWarning('Please select an action');
            return;
        }
        
        if(checkboxes.length === 0) {
            toastWarning('Please select at least one product');
            return;
        }
        
        let confirmMsg = '';
        switch(action) {
            case 'delete':
                confirmMsg = 'Apakah Anda yakin ingin menghapus ' + checkboxes.length + ' produk?<br>Tindakan ini tidak dapat dibatalkan.';
                break;
            case 'mark_sold':
                confirmMsg = 'Mark ' + checkboxes.length + ' product(s) as sold?';
                break;
            case 'mark_available':
                confirmMsg = 'Mark ' + checkboxes.length + ' product(s) as available?';
                break;
        }
        
        confirmModal(confirmMsg, function() {
            const form = document.getElementById('bulkActionForm');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'bulk_action';
            input.value = action;
            form.appendChild(input);
            form.submit();
        });
    }

    // Apply sorting
    function applySort(value) {
        const [sort, order] = value.split('_');
        const url = new URL(window.location.href);
        url.searchParams.set('sort', sort);
        url.searchParams.set('order', order);
        url.searchParams.delete('page'); // Reset to page 1
        window.location.href = url.toString();
    }

    // Change per page
    function changePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.delete('page'); // Reset to page 1
        window.location.href = url.toString();
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateSelectedCounter();
        makeRowsClickable();
    });

    // Make table rows clickable to toggle checkbox
    function makeRowsClickable() {
        const rows = document.querySelectorAll('.data-table tbody tr');
        
        rows.forEach(row => {
            // Add hover effect
            row.style.cursor = 'pointer';
            
            row.addEventListener('click', function(e) {
                // Jangan toggle jika klik pada link/button action
                if(e.target.closest('a') || e.target.closest('button') || e.target.tagName === 'INPUT') {
                    return;
                }
                
                // Find checkbox in this row
                const checkbox = this.querySelector('.product-checkbox');
                if(checkbox) {
                    checkbox.checked = !checkbox.checked;
                    updateSelectedCounter();
                    
                    // Add visual feedback
                    if(checkbox.checked) {
                        this.style.backgroundColor = '#FEF3C7';
                    } else {
                        this.style.backgroundColor = '';
                    }
                }
            });
            
            // Initial visual state for checked rows
            const checkbox = row.querySelector('.product-checkbox');
            if(checkbox && checkbox.checked) {
                row.style.backgroundColor = '#FEF3C7';
            }
        });
    }

    // Update the updateSelectedCounter function to also update row colors
    const originalUpdateSelectedCounter = updateSelectedCounter;
    updateSelectedCounter = function() {
        originalUpdateSelectedCounter();
        
        // Update row colors based on checkbox state
        const rows = document.querySelectorAll('.data-table tbody tr');
        rows.forEach(row => {
            const checkbox = row.querySelector('.product-checkbox');
            if(checkbox) {
                if(checkbox.checked) {
                    row.style.backgroundColor = '#FEF3C7';
                } else {
                    row.style.backgroundColor = '';
                }
            }
        });
    };
    
    // Toggle Sold Status dengan AJAX (tanpa reload halaman)
    function toggleSoldStatus(productId, button) {
        // Disable button sementara
        button.disabled = true;
        button.style.opacity = '0.6';
        button.style.cursor = 'not-allowed';
        
        // Kirim request AJAX
        fetch('products.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'ajax_toggle_sold=1&product_id=' + productId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastSuccess(data.message);
                
                // Update button appearance tanpa reload
                const isSold = data.new_status == 1;
                
                if (isSold) {
                    button.style.background = '#FEE2E2';
                    button.style.color = '#DC2626';
                    button.style.borderColor = '#FCA5A5';
                    button.innerHTML = `
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        <span style="font-size: 11px; font-weight: 600;">Sold</span>
                    `;
                } else {
                    button.style.background = '#D1FAE5';
                    button.style.color = '#10B981';
                    button.style.borderColor = '#6EE7B7';
                    button.innerHTML = `
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span style="font-size: 11px; font-weight: 600;">Mark Sold</span>
                    `;
                }
                
                // Update data attribute
                button.setAttribute('data-is-sold', data.new_status);
                
                // Update status badge di product name cell
                const row = button.closest('tr');
                if (row) {
                    const statusBadge = row.querySelector('.product-name-cell span');
                    if (statusBadge) {
                        if (isSold) {
                            statusBadge.style.color = '#DC2626';
                            statusBadge.textContent = '● Terjual';
                        } else {
                            statusBadge.style.color = '#10B981';
                            statusBadge.textContent = '● Available';
                        }
                    }
                }
                
                // Update mobile card badge jika ada
                const mobileCard = button.closest('.mobile-card-item');
                if (mobileCard) {
                    const mobileBadge = mobileCard.querySelector('.mobile-card-info span');
                    if (mobileBadge) {
                        if (isSold) {
                            mobileBadge.style.background = '#FEE2E2';
                            mobileBadge.style.color = '#DC2626';
                            mobileBadge.style.borderColor = '#FCA5A5';
                            mobileBadge.textContent = '● SOLD';
                        } else {
                            mobileBadge.style.background = '#D1FAE5';
                            mobileBadge.style.color = '#10B981';
                            mobileBadge.style.borderColor = '#6EE7B7';
                            mobileBadge.textContent = '● AVAILABLE';
                        }
                    }
                }
                
                // Re-enable button
                button.disabled = false;
                button.style.opacity = '1';
                button.style.cursor = 'pointer';
            } else {
                toastError(data.message || 'Gagal mengubah status produk');
                button.disabled = false;
                button.style.opacity = '1';
                button.style.cursor = 'pointer';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastError('Terjadi kesalahan. Silakan coba lagi.');
            button.disabled = false;
            button.style.opacity = '1';
            button.style.cursor = 'pointer';
        });
    }
    </script>

</body>
</html>
