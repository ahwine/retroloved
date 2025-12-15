<?php
/**
 * Halaman Edit Produk - Admin Panel
 * Form untuk mengedit produk, upload gambar tambahan, dan hapus gambar
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

// Inisialisasi variabel untuk pesan
$error = '';
$success = '';

// Ambil ID produk dari parameter URL
$product_id = isset($_GET['id']) ? escape($_GET['id']) : null;

// Jika tidak ada ID produk, redirect ke halaman products
if(!$product_id) {
    header('Location: products.php');
    exit();
}

if(isset($_POST['update_product']) || isset($_POST['remove_image_2']) || isset($_POST['remove_image_3']) || isset($_POST['remove_image_4']) || isset($_POST['remove_image_5']) || isset($_POST['remove_image_6']) || isset($_POST['remove_image_7']) || isset($_POST['remove_image_8']) || isset($_POST['remove_image_9']) || isset($_POST['remove_image_10'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $condition_item = mysqli_real_escape_string($conn, $_POST['condition_item']);
    $is_active = 1; // Always active
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Get current product data for images (support 10 images)
    $current_product = mysqli_fetch_assoc(query("SELECT image_url, image_url_2, image_url_3, image_url_4, image_url_5, image_url_6, image_url_7, image_url_8, image_url_9, image_url_10 FROM products WHERE product_id = '$product_id'"));
    $image_url = $current_product ? $current_product['image_url'] : '';
    $image_url_2 = $current_product ? $current_product['image_url_2'] : NULL;
    $image_url_3 = $current_product ? $current_product['image_url_3'] : NULL;
    $image_url_4 = $current_product ? $current_product['image_url_4'] : NULL;
    $image_url_5 = $current_product ? $current_product['image_url_5'] : NULL;
    $image_url_6 = $current_product ? $current_product['image_url_6'] : NULL;
    $image_url_7 = $current_product ? $current_product['image_url_7'] : NULL;
    $image_url_8 = $current_product ? $current_product['image_url_8'] : NULL;
    $image_url_9 = $current_product ? $current_product['image_url_9'] : NULL;
    $image_url_10 = $current_product ? $current_product['image_url_10'] : NULL;
    
    // Handle removal of images 2-10
    for($i = 2; $i <= 10; $i++) {
        $img_field = 'image_url_' . $i;
        if(isset($_POST['remove_image_' . $i])) {
            if($current_product && $current_product[$img_field] && file_exists('../assets/images/products/' . $current_product[$img_field])) {
                unlink('../assets/images/products/' . $current_product[$img_field]);
            }
            ${$img_field} = NULL;
        }
    }
    
    // Handle multiple image upload (new system like product-add)
    if(isset($_FILES['product_images']) && !empty($_FILES['product_images']['name'][0])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Count current existing images
        $existingImagesCount = 0;
        for($i = 1; $i <= 10; $i++) {
            $img_field = $i == 1 ? 'image_url' : 'image_url_' . $i;
            if(!empty(${$img_field})) {
                $existingImagesCount++;
            }
        }
        
        $uploaded_count = 0;
        $available_slots = 10 - $existingImagesCount;
        
        // Loop through all uploaded files
        foreach($_FILES['product_images']['tmp_name'] as $key => $tmp_name) {
            if($uploaded_count >= $available_slots) break; // Max 10 images total
            
            if($_FILES['product_images']['error'][$key] == 0) {
                $filename = $_FILES['product_images']['name'][$key];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if(!in_array(strtolower($filetype), $allowed)) {
                    $error = "Only JPG, JPEG, PNG, GIF & WEBP files are allowed!";
                    break;
                }
                
                $new_filename = 'product_' . time() . '_' . ($uploaded_count + 1) . '.' . $filetype;
                $upload_path = '../assets/images/products/' . $new_filename;
                
                if(move_uploaded_file($tmp_name, $upload_path)) {
                    // Find first available slot
                    for($i = 1; $i <= 10; $i++) {
                        $img_field = $i == 1 ? 'image_url' : 'image_url_' . $i;
                        if(empty(${$img_field})) {
                            ${$img_field} = $new_filename;
                            break;
                        }
                    }
                    $uploaded_count++;
                    usleep(100000); // 0.1 second delay for unique timestamps
                } else {
                    $error = "Failed to upload image " . ($uploaded_count + 1);
                    break;
                }
            }
        }
    }
    
    if(!$error) {
        $update = query("UPDATE products SET 
                        product_name = '$product_name',
                        category = '$category',
                        price = '$price',
                        description = '$description',
                        condition_item = '$condition_item',
                        image_url = '$image_url',
                        image_url_2 = " . ($image_url_2 ? "'$image_url_2'" : "NULL") . ",
                        image_url_3 = " . ($image_url_3 ? "'$image_url_3'" : "NULL") . ",
                        image_url_4 = " . ($image_url_4 ? "'$image_url_4'" : "NULL") . ",
                        image_url_5 = " . ($image_url_5 ? "'$image_url_5'" : "NULL") . ",
                        image_url_6 = " . ($image_url_6 ? "'$image_url_6'" : "NULL") . ",
                        image_url_7 = " . ($image_url_7 ? "'$image_url_7'" : "NULL") . ",
                        image_url_8 = " . ($image_url_8 ? "'$image_url_8'" : "NULL") . ",
                        image_url_9 = " . ($image_url_9 ? "'$image_url_9'" : "NULL") . ",
                        image_url_10 = " . ($image_url_10 ? "'$image_url_10'" : "NULL") . ",
                        is_active = '$is_active',
                        is_featured = '$is_featured'
                        WHERE product_id = '$product_id'");
        
        if($update) {
            $_SESSION['success'] = "Product updated successfully!";
            header("Location: products.php");
            exit();
        } else {
            $error = "Failed to update product!";
        }
    }
}

// Get product data for form display
if(!isset($product)) {
    $product = mysqli_fetch_assoc(query("SELECT * FROM products WHERE product_id = '$product_id'"));
    if(!$product) {
        header('Location: products.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - RetroLoved Admin
    <link rel="stylesheet" href="../assets/css/admin.css?v=5.4">
    <link rel="stylesheet" href="../assets/css/toast.css">
    <script src="../assets/js/toast.js"></script>
    <script src="../assets/js/modal.js"></script>
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        
        .form-group-full {
            grid-column: 1 / -1;
        }
        
        .current-image {
            margin-top: 12px;
            text-align: center;
        }
        
        .current-image img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .current-image p {
            margin-top: 8px;
            font-size: 13px;
            color: #666;
        }
        
        .image-preview-container {
            margin-top: 16px;
            text-align: center;
        }
        
        .image-preview-container img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .checkbox-wrapper input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .checkbox-wrapper label {
            margin: 0;
            cursor: pointer;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php $page_title = "Edit Produk"; include 'includes/navbar.php'; ?>

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
                        <li>
                            <a href="products.php" style="color: #6b7280; text-decoration: none; font-weight: 500; transition: color 0.2s;" onmouseover="this.style.color='#D97706'" onmouseout="this.style.color='#6b7280'">Produk</a>
                        </li>
                        <li style="color: #d1d5db;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </li>
                        <li style="color: #D97706; font-weight: 600;">
                            Edit Produk
                        </li>
                    </ol>
                </nav>
                
                <!-- Page Title -->
                <div style="margin-bottom: 24px;">
                    <h1 style="font-size: 32px; font-weight: 800; color: #1a1a1a; margin: 0 0 8px 0;">Edit Produk</h1>
                    <p style="font-size: 15px; color: #6b7280; margin: 0;">Update informasi dan gambar produk</p>
                </div>

            <div class="content-card" style="max-width: 1200px; margin: 0 auto;">
                <div class="card-header" style="background: #fff; border-bottom: 3px solid #3B82F6; padding: 24px 32px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <div>
                            <h3 style="margin: 0; font-size: 22px; font-weight: 700; color: #1f2937;">Edit Informasi Produk</h3>
                            <p style="margin: 4px 0 0 0; color: #6b7280; font-size: 14px;">Update detail dan gambar produk</p>
                        </div>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <div style="border-bottom: 2px solid #e5e7eb; background: #f9fafb;">
                    <div style="display: flex; padding: 0 32px; overflow-x: auto;">
                        <button type="button" class="tab-button active" onclick="switchTab(0)" data-tab="0" style="padding: 16px 24px; border: none; background: transparent; cursor: pointer; font-weight: 600; font-size: 14px; color: #6b7280; border-bottom: 3px solid transparent; white-space: nowrap; transition: all 0.2s;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                            Info Dasar
                        </button>
                        <button type="button" class="tab-button" onclick="switchTab(1)" data-tab="1" style="padding: 16px 24px; border: none; background: transparent; cursor: pointer; font-weight: 600; font-size: 14px; color: #6b7280; border-bottom: 3px solid transparent; white-space: nowrap; transition: all 0.2s;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle; margin-right: 6px;">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            Gambar & Galeri
                        </button>
                        <button type="button" class="tab-button" onclick="switchTab(2)" data-tab="2" style="padding: 16px 24px; border: none; background: transparent; cursor: pointer; font-weight: 600; font-size: 14px; color: #6b7280; border-bottom: 3px solid transparent; white-space: nowrap; transition: all 0.2s;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle; margin-right: 6px;">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Deskripsi & Detail
                        </button>
                        <button type="button" class="tab-button" onclick="switchTab(3)" data-tab="3" style="padding: 16px 24px; border: none; background: transparent; cursor: pointer; font-weight: 600; font-size: 14px; color: #6b7280; border-bottom: 3px solid transparent; white-space: nowrap; transition: all 0.2s;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle; margin-right: 6px;">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            Status & Pengaturan
                        </button>
                    </div>
                </div>

                <div style="padding: 40px;">
                    <form method="POST" enctype="multipart/form-data" id="productForm">
                        
                        <!-- TAB 1: BASIC INFO -->
                        <div class="tab-content" id="tab-0" style="display: block;">
                            <div style="margin-bottom: 32px;">
                                <h2 style="font-size: 20px; font-weight: 700; color: #1a1a1a; margin: 0 0 8px 0; display: flex; align-items: center; gap: 10px;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                    Basic Information
                                </h2>
                                <p style="margin: 0; color: #6b7280; font-size: 14px;">Update the essential details about your product</p>
                            </div>

                            <div class="form-grid" style="gap: 24px;">
                                <div class="form-group">
                                    <label>Product Name *</label>
                                    <input type="text" name="product_name" class="form-input" required 
                                           value="<?php echo htmlspecialchars($product['product_name']); ?>">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Clear and descriptive name for your product</small>
                                </div>

                                <div class="form-group">
                                    <label>Category *</label>
                                    <select name="category" class="form-input" required>
                                        <option value="">Select Category</option>
                                        <option value="Jacket" <?php echo $product['category'] == 'Jacket' ? 'selected' : ''; ?>>Jacket</option>
                                        <option value="Shirt" <?php echo $product['category'] == 'Shirt' ? 'selected' : ''; ?>>Shirt</option>
                                        <option value="T-Shirt" <?php echo $product['category'] == 'T-Shirt' ? 'selected' : ''; ?>>T-Shirt</option>
                                        <option value="Pants" <?php echo $product['category'] == 'Pants' ? 'selected' : ''; ?>>Pants</option>
                                        <option value="Jeans" <?php echo $product['category'] == 'Jeans' ? 'selected' : ''; ?>>Jeans</option>
                                        <option value="Dress" <?php echo $product['category'] == 'Dress' ? 'selected' : ''; ?>>Dress</option>
                                        <option value="Skirt" <?php echo $product['category'] == 'Skirt' ? 'selected' : ''; ?>>Skirt</option>
                                        <option value="Sweater" <?php echo $product['category'] == 'Sweater' ? 'selected' : ''; ?>>Sweater</option>
                                        <option value="Accessories" <?php echo $product['category'] == 'Accessories' ? 'selected' : ''; ?>>Accessories</option>
                                        <option value="Shoes" <?php echo $product['category'] == 'Shoes' ? 'selected' : ''; ?>>Shoes</option>
                                        <option value="Bag" <?php echo $product['category'] == 'Bag' ? 'selected' : ''; ?>>Bag</option>
                                        <option value="Other" <?php echo $product['category'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Brand</label>
                                    <input type="text" name="brand" class="form-input" placeholder="e.g. Levi's, Nike, Zara" 
                                           value="<?php echo htmlspecialchars($product['brand'] ?? ''); ?>">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Brand or manufacturer name (optional)</small>
                                </div>

                                <div class="form-group">
                                    <label>Harga Jual (Rp) *</label>
                                    <input type="number" name="price" class="form-input" required min="0" 
                                           value="<?php echo $product['price']; ?>">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Harga jual produk</small>
                                </div>
                            </div>

                            <!-- Navigation Buttons -->
                            <div style="margin-top: 32px; display: flex; justify-content: flex-end;">
                                <button type="button" onclick="switchTab(1)" style="background: #3B82F6; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
                                        onmouseover="this.style.background='#2563EB'"
                                        onmouseout="this.style.background='#3B82F6'">
                                    Selanjutnya: Gambar & Galeri
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- TAB 2: IMAGES & GALLERY -->
                        <div class="tab-content" id="tab-1" style="display: none;">
                            <div style="margin-bottom: 32px;">
                                <h2 style="font-size: 20px; font-weight: 700; color: #1a1a1a; margin: 0 0 8px 0; display: flex; align-items: center; gap: 10px;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>
                                    Images & Gallery
                                </h2>
                                <p style="margin: 0; color: #6b7280; font-size: 14px;">Manage your product images (up to 10 images)</p>
                            </div>

                            <!-- Current Images Display -->
                            <?php
                            $currentImages = [];
                            for ($i = 1; $i <= 10; $i++) {
                                $imageField = $i == 1 ? 'image_url' : 'image_url_' . $i;
                                if (!empty($product[$imageField])) {
                                    $currentImages[] = [
                                        'index' => $i,
                                        'url' => $product[$imageField],
                                        'field' => $imageField
                                    ];
                                }
                            }
                            ?>
                            
                            <?php if (!empty($currentImages)): ?>
                            <div style="background: #f0f9ff; padding: 24px; border-radius: 12px; margin-bottom: 24px; border: 2px solid #3B82F6;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>
                                    <h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #3B82F6;">Gambar Saat Ini (<?php echo count($currentImages); ?>)</h4>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 16px; margin-bottom: 20px;">
                                    <?php foreach ($currentImages as $img): ?>
                                    <div style="position: relative; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); background: white;">
                                        <img src="../assets/images/products/<?php echo htmlspecialchars($img['url']); ?>" 
                                             alt="Image <?php echo $img['index']; ?>"
                                             style="width: 100%; height: 150px; object-fit: cover; display: block;"
                                             onerror="this.src='../assets/images/products/placeholder.jpg'">
                                        <div style="position: absolute; top: 8px; left: 8px; background: #3B82F6; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                            <?php echo $img['index']; ?>
                                        </div>
                                        <?php if ($img['index'] > 1): // Allow deletion for images 2-10 ?>
                                        <button type="button" 
                                                onclick="showDeleteImageConfirm('Apakah Anda yakin ingin menghapus gambar <?php echo $img['index']; ?>?', function() { document.getElementById('removeImage<?php echo $img['index']; ?>Form').submit(); })"
                                                style="position: absolute; top: 8px; right: 8px; background: #EF4444; color: white; border: none; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); transition: all 0.2s;"
                                                onmouseover="this.style.background='#DC2626'"
                                                onmouseout="this.style.background='#EF4444'">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div style="padding: 12px; background: white; border-radius: 8px; border: 1px solid #DBEAFE;">
                                    <p style="margin: 0; color: #1E40AF; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="16" x2="12" y2="12"></line>
                                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                        </svg>
                                        <strong>Note:</strong> You can add more images or replace existing ones below. Image 1 is the main product image.
                                    </p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Add/Replace Images Section -->
                            <div style="background: #f8f9fa; padding: 24px; border-radius: 12px; border: 2px solid #e5e7eb;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>
                                    <h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #374151;">
                                        <?php echo empty($currentImages) ? 'Upload Gambar Produk *' : 'Tambah atau Ganti Gambar'; ?>
                                    </h4>
                                    <span style="background: #e5e7eb; color: #6b7280; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;" id="imageCountEdit">0 dipilih</span>
                                </div>
                                <p style="margin: 0 0 20px 0; color: #6b7280; font-size: 13px;">
                                    <?php if (empty($currentImages)): ?>
                                        Pilih 1 sampai 10 gambar untuk produk ini (Anda bisa pilih beberapa sekaligus)
                                    <?php else: ?>
                                        Pilih gambar baru untuk ditambahkan atau mengganti yang ada (maks <?php echo 10 - count($currentImages); ?> gambar lagi)
                                    <?php endif; ?>
                                </p>
                            
                                <div style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 40px; text-align: center; background: white;">
                                    <input type="file" name="product_images[]" id="imageInputEdit" class="form-input" accept="image/*" multiple
                                           onchange="previewMultipleImagesEdit(this)" style="display: none;">
                                    <div id="uploadPromptEdit">
                                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin: 0 auto 16px; display: block;">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="17 8 12 3 7 8"></polyline>
                                            <line x1="12" y1="3" x2="12" y2="15"></line>
                                        </svg>
                                        <p style="margin: 0 0 8px 0; font-weight: 600; font-size: 17px; color: #374151;">Upload Gambar Produk</p>
                                        <p style="margin: 0 0 20px 0; color: #64748b; font-size: 14px;">Drag & drop atau klik untuk pilih gambar</p>
                                        <button type="button" onclick="document.getElementById('imageInputEdit').click()" 
                                                style="background: #3B82F6; color: white; border: none; padding: 12px 28px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 15px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;"
                                                onmouseover="this.style.background='#2563EB'"
                                                onmouseout="this.style.background='#3B82F6'">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="17 8 12 3 7 8"></polyline>
                                                <line x1="12" y1="3" x2="12" y2="15"></line>
                                            </svg>
                                            Pilih Gambar
                                        </button>
                                        <p style="margin: 16px 0 0 0; color: #94a3b8; font-size: 13px;">JPG, PNG, GIF, WEBP (Maks 5MB per file)</p>
                                    </div>
                                    <div id="multipleImagePreviewEdit" style="display: none;">
                                        <div id="imageGridEdit" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; margin-bottom: 20px;"></div>
                                        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                                            <button type="button" onclick="clearAllImagesEdit()" style="background: #EF4444; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                                </svg>
                                                Hapus Semua
                                            </button>
                                            <button type="button" onclick="document.getElementById('imageInputEdit').click()" style="background: #3B82F6; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="23 4 23 10 17 10"></polyline>
                                                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                                                </svg>
                                                Ganti Gambar
                                            </button>
                                        </div>
                                        <div style="margin: 16px 0 0 0; color: #10B981; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                            <span id="readyMessageEdit">Gambar siap diupload</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden forms for image deletion -->
                            <?php for ($i = 2; $i <= 10; $i++): ?>
                            <form method="POST" id="removeImage<?php echo $i; ?>Form" style="display: none;">
                                <input type="hidden" name="remove_image_<?php echo $i; ?>" value="1">
                            </form>
                            <?php endfor; ?>
                            
                            
                            <!-- Navigation Buttons -->
                            <div style="margin-top: 32px; display: flex; justify-content: space-between; gap: 12px;">
                                <button type="button" onclick="switchTab(0)" style="background: #6b7280; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
                                        onmouseover="this.style.background='#4b5563'"
                                        onmouseout="this.style.background='#6b7280'">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                    Sebelumnya: Info Dasar
                                </button>
                                <button type="button" onclick="switchTab(2)" style="background: #3B82F6; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
                                        onmouseover="this.style.background='#2563EB'"
                                        onmouseout="this.style.background='#3B82F6'">
                                    Selanjutnya: Deskripsi & Detail
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- TAB 3: DESCRIPTION & DETAILS -->
                        <div class="tab-content" id="tab-2" style="display: none;">
                            <div style="margin-bottom: 32px;">
                                <h2 style="font-size: 20px; font-weight: 700; color: #1a1a1a; margin: 0 0 8px 0; display: flex; align-items: center; gap: 10px;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    Description & Details
                                </h2>
                                <p style="margin: 0; color: #6b7280; font-size: 14px;">Provide detailed information about the product</p>
                            </div>

                            <div class="form-grid" style="gap: 24px;">
                                <div class="form-group form-group-full">
                                    <label>Product Description *</label>
                                    <textarea name="description" class="form-input" rows="6" required placeholder="Describe the product in detail..."><?php echo htmlspecialchars($product['description']); ?></textarea>
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Provide a comprehensive description to help buyers</small>
                                </div>

                                <div class="form-group">
                                    <label>Size</label>
                                    <input type="text" name="size" class="form-input" placeholder="e.g. S, M, L, XL, 38, 40" 
                                           value="<?php echo htmlspecialchars($product['size'] ?? ''); ?>">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Product size (optional)</small>
                                </div>

                                <div class="form-group">
                                    <label>Color</label>
                                    <input type="text" name="color" class="form-input" placeholder="e.g. Blue, Red, Black" 
                                           value="<?php echo htmlspecialchars($product['color'] ?? ''); ?>">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Primary color (optional)</small>
                                </div>

                                <div class="form-group">
                                    <label>Material</label>
                                    <input type="text" name="material" class="form-input" placeholder="e.g. Cotton, Denim, Polyester" 
                                           value="<?php echo htmlspecialchars($product['material'] ?? ''); ?>">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Fabric or material type (optional)</small>
                                </div>

                                <div class="form-group">
                                    <label>Weight (grams)</label>
                                    <input type="number" name="weight" class="form-input" min="0" placeholder="e.g. 500" 
                                           value="<?php echo $product['weight'] ?? ''; ?>">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">For shipping calculation (optional)</small>
                                </div>

                                <div class="form-group form-group-full">
                                    <label>Additional Notes</label>
                                    <textarea name="notes" class="form-input" rows="3" placeholder="Any additional information for buyers..."><?php echo htmlspecialchars($product['notes'] ?? ''); ?></textarea>
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Extra details, care instructions, etc. (optional)</small>
                                </div>
                            </div>

                            <!-- Navigation Buttons -->
                            <div style="margin-top: 32px; display: flex; justify-content: space-between; gap: 12px;">
                                <button type="button" onclick="switchTab(1)" style="background: #6b7280; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
                                        onmouseover="this.style.background='#4b5563'"
                                        onmouseout="this.style.background='#6b7280'">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                    Sebelumnya: Gambar
                                </button>
                                <button type="button" onclick="switchTab(3)" style="background: #3B82F6; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
                                        onmouseover="this.style.background='#2563EB'"
                                        onmouseout="this.style.background='#3B82F6'">
                                    Selanjutnya: Status & Pengaturan
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- TAB 4: STATUS & SETTINGS -->
                        <div class="tab-content" id="tab-3" style="display: none;">
                            <div style="margin-bottom: 32px;">
                                <h2 style="font-size: 20px; font-weight: 700; color: #1a1a1a; margin: 0 0 8px 0; display: flex; align-items: center; gap: 10px;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    </svg>
                                    Status & Settings
                                </h2>
                                <p style="margin: 0; color: #6b7280; font-size: 14px;">Configure product status and other settings</p>
                            </div>

                            <div class="form-grid" style="gap: 24px;">
                                <div class="form-group">
                                    <label>Condition *</label>
                                    <select name="condition_item" class="form-input" required>
                                        <option value="">Select Condition</option>
                                        <option value="Excellent" <?php echo $product['condition_item'] == 'Excellent' ? 'selected' : ''; ?>>Excellent - Like new, no visible wear</option>
                                        <option value="Very Good" <?php echo $product['condition_item'] == 'Very Good' ? 'selected' : ''; ?>>Very Good - Minimal signs of use</option>
                                        <option value="Good" <?php echo $product['condition_item'] == 'Good' ? 'selected' : ''; ?>>Good - Some visible wear but functional</option>
                                        <option value="Fair" <?php echo $product['condition_item'] == 'Fair' ? 'selected' : ''; ?>>Fair - Noticeable wear, still usable</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>SKU / Product Code</label>
                                    <input type="text" name="sku" class="form-input" placeholder="e.g. VDJ-001" 
                                           value="<?php echo htmlspecialchars($product['sku'] ?? ''); ?>">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Stock Keeping Unit for inventory tracking (optional)</small>
                                </div>

                                <div class="form-group form-group-full">
                                    <div style="background: #FEF3C7; border: 2px solid #F59E0B; border-radius: 8px; padding: 16px;">
                                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                            <input type="checkbox" name="is_featured" id="is_featured" <?php echo $product['is_featured'] ? 'checked' : ''; ?> style="width: 20px; height: 20px; cursor: pointer;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#F59E0B" stroke="#F59E0B" stroke-width="2">
                                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                                </svg>
                                                <span style="font-weight: 600; color: #92400E;">Featured Product</span>
                                            </div>
                                        </label>
                                        <p style="margin: 8px 0 0 32px; color: #B45309; font-size: 13px;">Display this product prominently on the homepage</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Navigation Buttons -->
                            <div style="margin-top: 32px; display: flex; justify-content: space-between; gap: 12px;">
                                <button type="button" onclick="switchTab(2)" style="background: #6b7280; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
                                        onmouseover="this.style.background='#4b5563'"
                                        onmouseout="this.style.background='#6b7280'">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                    Sebelumnya: Deskripsi
                                </button>
                            </div>
                        </div>

                        <!-- SUBMIT BUTTON (shown on all tabs) -->
                        <div style="margin-top: 40px; padding-top: 24px; border-top: 2px solid #e5e7eb; display: flex; gap: 12px;">
                            <button type="submit" name="update_product" style="flex: 1; background: #3B82F6; color: white; border: none; padding: 14px 24px; border-radius: 6px; font-weight: 600; font-size: 15px; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 8px;"
                                    onmouseover="this.style.background='#2563EB'"
                                    onmouseout="this.style.background='#3B82F6'">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                Update Produk
                            </button>
                            <a href="products.php" style="flex: 1; background: #6b7280; color: white; border: none; padding: 14px 24px; border-radius: 6px; font-weight: 600; font-size: 15px; text-align: center; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;"
                               onmouseover="this.style.background='#4b5563'"
                               onmouseout="this.style.background='#6b7280'">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                                Batal
                            </a>
                        </div>                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="confirmation-modal" id="deleteImageConfirmModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 10000; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 16px; padding: 32px; max-width: 400px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3); text-align: center;">
            <div style="width: 64px; height: 64px; background: #FEF3C7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <h3 style="font-size: 18px; font-weight: 700; color: #1F2937; margin: 0 0 12px 0;">Konfirmasi</h3>
            <p id="deleteImageConfirmMessage" style="color: #6B7280; font-size: 15px; line-height: 1.5; margin: 0 0 24px 0;"></p>
            <div style="display: flex; gap: 12px;">
                <button onclick="closeDeleteImageConfirm()" style="flex: 1; padding: 12px 20px; background: white; color: #6B7280; border: 1.5px solid #E5E7EB; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s;">
                    Batal
                </button>
                <button id="deleteImageConfirmButton" onclick="executeDeleteImageAction()" style="flex: 1; padding: 12px 20px; background: #DC2626; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s;">
                    Ya, Hapus
                </button>
            </div>
            </div>
        </div>
    </div>

    <script>
        // Confirmation Modal for Delete Image
        let deleteImageCallback = null;
        
        function showDeleteImageConfirm(message, callback) {
            document.getElementById('deleteImageConfirmMessage').textContent = message;
            document.getElementById('deleteImageConfirmModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            deleteImageCallback = callback;
        }
        
        function closeDeleteImageConfirm() {
            document.getElementById('deleteImageConfirmModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            deleteImageCallback = null;
        }
        
        function executeDeleteImageAction() {
            if (deleteImageCallback && typeof deleteImageCallback === 'function') {
                deleteImageCallback();
            }
            closeDeleteImageConfirm();
        }
        
        function showImageUpload() {
            document.getElementById('currentImageSection').style.display = 'none';
            document.getElementById('uploadImageSection').style.display = 'block';
        }
        
        function cancelImageUpload() {
            document.getElementById('currentImageSection').style.display = 'block';
            document.getElementById('uploadImageSection').style.display = 'none';
            document.getElementById('imageInput').value = '';
            document.getElementById('uploadPrompt').style.display = 'block';
            document.getElementById('newImagePreview').style.display = 'none';
        }
        
        function previewNewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('uploadPrompt').style.display = 'none';
                    document.getElementById('newImagePreview').style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function removeNewImage() {
            document.getElementById('imageInput').value = '';
            document.getElementById('uploadPrompt').style.display = 'block';
            document.getElementById('newImagePreview').style.display = 'none';
        }
        
        // Second Image Functions
        function showSecondImageUpload() {
            document.getElementById('currentSecondImageSection').style.display = 'none';
            document.getElementById('uploadSecondImageSection').style.display = 'block';
        }
        
        function cancelSecondImageUpload() {
            document.getElementById('currentSecondImageSection').style.display = 'block';
            document.getElementById('uploadSecondImageSection').style.display = 'none';
            document.getElementById('imageInput2').value = '';
            document.getElementById('uploadPrompt2').style.display = 'block';
            document.getElementById('secondImagePreview').style.display = 'none';
        }
        
        function previewSecondImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('previewImg2').src = e.target.result;
                    document.getElementById('uploadPrompt2').style.display = 'none';
                    document.getElementById('secondImagePreview').style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function removeNewSecondImage() {
            document.getElementById('imageInput2').value = '';
            document.getElementById('uploadPrompt2').style.display = 'block';
            document.getElementById('secondImagePreview').style.display = 'none';
        }
        
        function removeCurrentSecondImage() {
            showDeleteImageConfirm('Apakah Anda yakin ingin menghapus gambar kedua? Gambar akan dihapus setelah Anda klik "Update Product".', function() {
                // Hide current image and show "no image" state
                document.getElementById('currentSecondImageSection').innerHTML = `
                    <div style="text-align: center; padding: 20px; background: white; border-radius: 8px; border: 2px dashed #e5e7eb;">
                        <p style="margin: 0 0 12px 0; color: #9ca3af; font-size: 14px;">No second image uploaded</p>
                        <button type="button" onclick="showSecondImageUpload()" style="background: #6b7280; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;"
                                onmouseover="this.style.background='#4b5563'" onmouseout="this.style.background='#6b7280'">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add Second Image
                        </button>
                    </div>
                `;
                
                // Add hidden input to mark for removal on form submit
                let input = document.querySelector('input[name="remove_image_2"]');
                if(!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'remove_image_2';
                    input.value = '1';
                    document.getElementById('productForm').appendChild(input);
                }
                
                toastSuccess('Gambar ke-2 akan dihapus setelah Anda klik "Update Product"', 'Ditandai untuk Dihapus');
            });
        }
        
        // ===== IMAGE 3 FUNCTIONS =====
        function showImage3Upload() {
            document.getElementById('currentImage3Section').style.display = 'none';
            document.getElementById('uploadImage3Section').style.display = 'block';
        }
        
        function cancelImage3Upload() {
            document.getElementById('currentImage3Section').style.display = 'block';
            document.getElementById('uploadImage3Section').style.display = 'none';
            document.getElementById('imageInput3').value = '';
            document.getElementById('uploadPrompt3').style.display = 'block';
            document.getElementById('image3Preview').style.display = 'none';
        }
        
        function removeCurrentImage3() {
            showDeleteImageConfirm('Apakah Anda yakin ingin menghapus gambar ke-3? Gambar akan dihapus setelah Anda klik "Update Product".', function() {
                // Hide current image and show "no image" state
                document.getElementById('currentImage3Section').innerHTML = `
                    <div style="text-align: center; padding: 20px; background: white; border-radius: 8px; border: 2px dashed #e5e7eb;">
                        <p style="margin: 0 0 12px 0; color: #9ca3af; font-size: 14px;">No third image uploaded</p>
                        <button type="button" onclick="showImage3Upload()" style="background: #6b7280; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;"
                                onmouseover="this.style.background='#4b5563'" onmouseout="this.style.background='#6b7280'">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add Third Image
                        </button>
                    </div>
                `;
                
                // Add hidden input to mark for removal on form submit
                let input = document.querySelector('input[name="remove_image_3"]');
                if(!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'remove_image_3';
                    input.value = '1';
                    document.getElementById('productForm').appendChild(input);
                }
                
                toastSuccess('Gambar ke-3 akan dihapus setelah Anda klik "Update Product"', 'Ditandai untuk Dihapus');
            });
        }
        
        // ===== IMAGE 4 FUNCTIONS =====
        function showImage4Upload() {
            document.getElementById('currentImage4Section').style.display = 'none';
            document.getElementById('uploadImage4Section').style.display = 'block';
        }
        
        function cancelImage4Upload() {
            document.getElementById('currentImage4Section').style.display = 'block';
            document.getElementById('uploadImage4Section').style.display = 'none';
            document.getElementById('imageInput4').value = '';
            document.getElementById('uploadPrompt4').style.display = 'block';
            document.getElementById('image4Preview').style.display = 'none';
        }
        
        function removeCurrentImage4() {
            showDeleteImageConfirm('Apakah Anda yakin ingin menghapus gambar ke-4? Gambar akan dihapus setelah Anda klik "Update Product".', function() {
                // Hide current image and show "no image" state
                document.getElementById('currentImage4Section').innerHTML = `
                    <div style="text-align: center; padding: 20px; background: white; border-radius: 8px; border: 2px dashed #e5e7eb;">
                        <p style="margin: 0 0 12px 0; color: #9ca3af; font-size: 14px;">No fourth image uploaded</p>
                        <button type="button" onclick="showImage4Upload()" style="background: #6b7280; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;"
                                onmouseover="this.style.background='#4b5563'" onmouseout="this.style.background='#6b7280'">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add Fourth Image
                        </button>
                    </div>
                `;
                
                // Add hidden input to mark for removal on form submit
                let input = document.querySelector('input[name="remove_image_4"]');
                if(!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'remove_image_4';
                    input.value = '1';
                    document.getElementById('productForm').appendChild(input);
                }
                
                toastSuccess('Gambar ke-4 akan dihapus setelah Anda klik "Update Product"', 'Ditandai untuk Dihapus');
            });
        }
        
        // ===== UNIFIED FUNCTIONS FOR NEW UPLOADS (Image 3 & 4) =====
        function previewImage(imageNumber, input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('previewImg' + imageNumber).src = e.target.result;
                    document.getElementById('uploadPrompt' + imageNumber).style.display = 'none';
                    document.getElementById('image' + imageNumber + 'Preview').style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function removeNewImage(imageNumber) {
            document.getElementById('imageInput' + imageNumber).value = '';
            document.getElementById('uploadPrompt' + imageNumber).style.display = 'block';
            document.getElementById('image' + imageNumber + 'Preview').style.display = 'none';
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
    </script>

    <!-- Tab Styling -->
    <style>
        .tab-button.active {
            color: #3B82F6 !important;
            border-bottom-color: #3B82F6 !important;
        }
        
        .tab-button:hover {
            color: #3B82F6 !important;
            background: rgba(59, 130, 246, 0.05);
        }
        
        .tab-content {
            animation: fadeInTab 0.3s ease-in;
        }
        
        @keyframes fadeInTab {
            from { 
                opacity: 0;
                transform: translateY(10px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <!-- Multi-Image Upload Script for Edit -->
    <script>
        function previewMultipleImagesEdit(input) {
            const files = input.files;
            if (!files || files.length === 0) return;
            
            // Max 10 images
            if (files.length > 10) {
                toastWarning('Maksimal 10 gambar saja!', 'Peringatan');
                input.value = '';
                return;
            }
            
            // Update counter
            document.getElementById('imageCountEdit').textContent = files.length + ' selected';
            
            // Hide upload prompt, show preview
            document.getElementById('uploadPromptEdit').style.display = 'none';
            document.getElementById('multipleImagePreviewEdit').style.display = 'block';
            
            // Clear previous previews
            const imageGrid = document.getElementById('imageGridEdit');
            imageGrid.innerHTML = '';
            
            // Create preview for each file
            Array.from(files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const imgContainer = document.createElement('div');
                        imgContainer.style.cssText = 'position: relative; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.cssText = 'width: 100%; height: 120px; object-fit: cover; display: block;';
                        
                        const badge = document.createElement('div');
                        badge.textContent = index + 1;
                        badge.style.cssText = 'position: absolute; top: 4px; left: 4px; background: #3B82F6; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600;';
                        
                        imgContainer.appendChild(img);
                        imgContainer.appendChild(badge);
                        imageGrid.appendChild(imgContainer);
                    }
                    
                    reader.readAsDataURL(file);
                }
            });
        }
        
        function clearAllImagesEdit() {
            document.getElementById('imageInputEdit').value = '';
            document.getElementById('uploadPromptEdit').style.display = 'block';
            document.getElementById('multipleImagePreviewEdit').style.display = 'none';
            document.getElementById('imageCountEdit').textContent = '0 selected';
        }
        
        // Tab switching function
        function switchTab(tabIndex) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.style.display = 'none';
            });
            
            // Show selected tab
            document.getElementById('tab-' + tabIndex).style.display = 'block';
            
            // Update tab button styles
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.style.color = '#6b7280';
                button.style.borderBottomColor = 'transparent';
            });
            
            const activeButton = document.querySelector('.tab-button[data-tab="' + tabIndex + '"]');
            if (activeButton) {
                activeButton.style.color = '#3B82F6';
                activeButton.style.borderBottomColor = '#3B82F6';
            }
            
            // Scroll to top of form
            document.getElementById('productForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
    </script>
</body>
</html>
