<?php
/**
 * Halaman Tambah Produk - Admin Panel
 * Form untuk menambahkan produk baru dengan upload gambar (max 10 gambar)
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

// ===== PROSES TAMBAH PRODUK BARU =====
if(isset($_POST['add_product'])) {
    // Ambil dan bersihkan data dari form
    $product_name = escape($_POST['product_name']);
    $category = escape($_POST['category']);
    $price = escape($_POST['price']);
    $description = escape($_POST['description']);
    $condition_item = escape($_POST['condition_item']);
    $is_active = 1; // Produk baru selalu aktif
    $is_featured = isset($_POST['is_featured']) ? 1 : 0; // Cek apakah ditandai sebagai featured
    
    // ===== PROSES UPLOAD MULTIPLE GAMBAR (maksimal 10 gambar) =====
    if(isset($_FILES['product_images']) && !empty($_FILES['product_images']['name'][0])) {
        // Format file yang diizinkan
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Array untuk menyimpan nama file gambar (1-10)
        $image_filenames = array_fill(1, 10, NULL);
        $uploaded_count = 0;
        
        // Buat folder products jika belum ada
        if(!file_exists('../assets/images/products/')) {
            mkdir('../assets/images/products/', 0777, true);
        }
        
        // Loop untuk setiap file yang diupload
        foreach($_FILES['product_images']['tmp_name'] as $key => $tmp_name) {
            if($uploaded_count >= 10) break; // Batasi maksimal 10 gambar
            
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
                    $image_filenames[$uploaded_count + 1] = $new_filename;
                    $uploaded_count++;
                    usleep(100000); // 0.1 second delay for unique timestamps
                } else {
                    $error = "Failed to upload image " . ($uploaded_count + 1);
                    break;
                }
            }
        }
        
        if(!$error && $uploaded_count > 0) {
                
                $insert = query("INSERT INTO products (product_name, category, price, description, condition_item, image_url, image_url_2, image_url_3, image_url_4, image_url_5, image_url_6, image_url_7, image_url_8, image_url_9, image_url_10, is_active, is_featured, created_at) 
                                VALUES ('$product_name', '$category', '$price', '$description', '$condition_item', 
                                '" . $image_filenames[1] . "', 
                                " . ($image_filenames[2] ? "'" . $image_filenames[2] . "'" : "NULL") . ", 
                                " . ($image_filenames[3] ? "'" . $image_filenames[3] . "'" : "NULL") . ", 
                                " . ($image_filenames[4] ? "'" . $image_filenames[4] . "'" : "NULL") . ", 
                                " . ($image_filenames[5] ? "'" . $image_filenames[5] . "'" : "NULL") . ", 
                                " . ($image_filenames[6] ? "'" . $image_filenames[6] . "'" : "NULL") . ", 
                                " . ($image_filenames[7] ? "'" . $image_filenames[7] . "'" : "NULL") . ", 
                                " . ($image_filenames[8] ? "'" . $image_filenames[8] . "'" : "NULL") . ", 
                                " . ($image_filenames[9] ? "'" . $image_filenames[9] . "'" : "NULL") . ", 
                                " . ($image_filenames[10] ? "'" . $image_filenames[10] . "'" : "NULL") . ", 
                                '$is_active', '$is_featured', NOW())");
                
            if($insert) {
                $success = "Product added successfully with $uploaded_count image(s)!";
                header("refresh:2;url=products.php");
            } else {
                $error = "Failed to add product to database!";
            }
        }
    } else {
        $error = "Please upload at least 1 product image!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk Baru - RetroLoved Admin</title>
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
        
        .image-slot {
            transition: all 0.2s ease;
        }
        
        .image-slot.active-slot:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .image-slot.empty-slot {
            pointer-events: none;
            opacity: 0.5;
        }
        
        .upload-area:hover {
            background: rgba(59, 130, 246, 0.05);
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
        <?php $page_title = "Tambah Produk Baru"; include 'includes/navbar.php'; ?>

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
                            Tambah Produk
                        </li>
                    </ol>
                </nav>
                
                <!-- Page Title -->
                <div style="margin-bottom: 24px;">
                    <h1 style="font-size: 32px; font-weight: 800; color: #1a1a1a; margin: 0 0 8px 0;">Tambah Produk Baru</h1>
                    <p style="font-size: 15px; color: #6b7280; margin: 0;">Buat listing produk baru untuk toko Anda</p>
                </div>

            <div class="content-card" style="max-width: 1200px; margin: 0 auto; position: relative;">
                <!-- Close Button (X) - Pojok Kanan Atas -->
                <a href="products.php" style="position: absolute; top: 16px; right: 16px; z-index: 100; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; background: #f3f4f6; color: #6b7280; text-decoration: none; transition: all 0.2s; border: none; cursor: pointer;"
                   onmouseover="this.style.background='#EF4444'; this.style.color='white';"
                   onmouseout="this.style.background='#f3f4f6'; this.style.color='#6b7280';"
                   title="Tutup">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </a>
                
                <div class="card-header" style="background: #fff; border-bottom: 3px solid #10B981; padding: 24px 32px; position: relative;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <circle cx="12" cy="12" r="10"></circle>
                        </svg>
                        <div>
                            <h3 style="margin: 0; font-size: 22px; font-weight: 700; color: #1f2937;">Tambah Produk Baru</h3>
                            <p style="margin: 4px 0 0 0; color: #6b7280; font-size: 14px;">Isi detail produk dan upload gambar</p>
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
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                    Informasi Dasar
                                </h2>
                                <p style="margin: 0; color: #6b7280; font-size: 14px;">Masukkan detail penting tentang produk Anda</p>
                            </div>

                            <div class="form-grid" style="gap: 24px;">
                                <div class="form-group">
                                    <label>Nama Produk *</label>
                                    <input type="text" name="product_name" class="form-input" placeholder="contoh: Jaket Denim Vintage">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Nama yang jelas dan deskriptif untuk produk Anda</small>
                                </div>

                                <div class="form-group">
                                    <label>Kategori *</label>
                                    <select name="category" class="form-input">
                                        <option value="">Pilih Kategori</option>
                                        <option value="Jacket">Jacket</option>
                                        <option value="Shirt">Shirt</option>
                                        <option value="T-Shirt">T-Shirt</option>
                                        <option value="Pants">Pants</option>
                                        <option value="Jeans">Jeans</option>
                                        <option value="Dress">Dress</option>
                                        <option value="Skirt">Skirt</option>
                                        <option value="Sweater">Sweater</option>
                                        <option value="Accessories">Accessories</option>
                                        <option value="Shoes">Shoes</option>
                                        <option value="Bag">Bag</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Merek</label>
                                    <input type="text" name="brand" class="form-input" placeholder="contoh: Levi's, Nike, Zara">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Nama merek atau produsen (opsional)</small>
                                </div>

                                <div class="form-group">
                                    <label>Harga Jual (Rp) *</label>
                                    <input type="number" name="price" class="form-input" min="0" placeholder="contoh: 150000">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Harga jual produk</small>
                                </div>
                            </div>

                            <!-- Navigation Buttons -->
                            <div style="margin-top: 32px; display: flex; justify-content: flex-end;">
                                <button type="button" onclick="switchTab(1)" style="background: #D97706; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
                                        onmouseover="this.style.background='#B45309'"
                                        onmouseout="this.style.background='#D97706'">
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
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>
                                    Gambar & Galeri
                                </h2>
                                <p style="margin: 0; color: #6b7280; font-size: 14px;">Upload gambar produk satu per satu (maksimal 10 gambar)</p>
                            </div>

                            <!-- Image Upload Section -->
                            <div style="background: #f8f9fa; padding: 24px; border-radius: 12px; border: 2px solid #e5e7eb;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                                    <div>
                                        <h4 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 700; color: #1a1a1a;">Foto</h4>
                                        <p style="margin: 0; color: #6b7280; font-size: 13px;">Upload gambar produk satu per satu</p>
                                    </div>
                                </div>
                                
                                <!-- Image Slots Container (Grid 4 kolom) -->
                                <div id="imageSlots" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                                    <!-- Slot 1 - Active Upload -->
                                    <div class="image-slot active-slot" data-slot="1" style="aspect-ratio: 1; border: 2px solid #e5e7eb; border-radius: 8px; background: white; position: relative; overflow: hidden;">
                                        <input type="file" name="product_images[]" id="imageInput1" class="image-input" accept="image/*" style="display: none;" onchange="handleImageUpload(1, this)">
                                        <div class="upload-area" onclick="document.getElementById('imageInput1').click()" style="cursor: pointer; text-align: center; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px;">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin-bottom: 8px;">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                <polyline points="21 15 16 10 5 21"></polyline>
                                            </svg>
                                            <p style="margin: 0; color: #94a3b8; font-size: 11px;">Tambah foto</p>
                                        </div>
                                        <div class="preview-area" style="display: none; height: 100%;">
                                            <img class="preview-img" src="" style="width: 100%; height: 100%; object-fit: cover;">
                                            <button type="button" onclick="removeImage(1)" style="position: absolute; top: 4px; right: 4px; background: #EF4444; color: white; border: none; width: 24px; height: 24px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); z-index: 10;">×</button>
                                        </div>
                                    </div>
                                    
                                    <!-- Slots 2-8 - Empty Placeholders -->
                                    <div class="image-slot empty-slot" data-slot="2" style="aspect-ratio: 1; border: 2px solid #e5e7eb; border-radius: 8px; background: #f9fafb; position: relative;"></div>
                                    <div class="image-slot empty-slot" data-slot="3" style="aspect-ratio: 1; border: 2px solid #e5e7eb; border-radius: 8px; background: #f9fafb; position: relative;"></div>
                                    <div class="image-slot empty-slot" data-slot="4" style="aspect-ratio: 1; border: 2px solid #e5e7eb; border-radius: 8px; background: #f9fafb; position: relative;"></div>
                                    <div class="image-slot empty-slot" data-slot="5" style="aspect-ratio: 1; border: 2px solid #e5e7eb; border-radius: 8px; background: #f9fafb; position: relative;"></div>
                                    <div class="image-slot empty-slot" data-slot="6" style="aspect-ratio: 1; border: 2px solid #e5e7eb; border-radius: 8px; background: #f9fafb; position: relative;"></div>
                                    <div class="image-slot empty-slot" data-slot="7" style="aspect-ratio: 1; border: 2px solid #e5e7eb; border-radius: 8px; background: #f9fafb; position: relative;"></div>
                                    <div class="image-slot empty-slot" data-slot="8" style="aspect-ratio: 1; border: 2px solid #e5e7eb; border-radius: 8px; background: #f9fafb; position: relative;"></div>
                                </div>
                            </div>

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
                                <button type="button" onclick="switchTab(2)" style="background: #D97706; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
                                        onmouseover="this.style.background='#B45309'"
                                        onmouseout="this.style.background='#D97706'">
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
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    Deskripsi & Detail
                                </h2>
                                <p style="margin: 0; color: #6b7280; font-size: 14px;">Berikan informasi detail tentang produk</p>
                            </div>

                            <div class="form-grid" style="gap: 24px;">
                                <div class="form-group form-group-full">
                                    <label>Deskripsi Produk *</label>
                                    <textarea name="description" class="form-input" rows="6" placeholder="Deskripsikan produk secara detail...&#10;&#10;Contoh:&#10;- Material dan kualitas kain&#10;- Fitur unik&#10;- Detail kondisi&#10;- Informasi ukuran dan fit"></textarea>
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Berikan deskripsi lengkap untuk membantu pembeli</small>
                                </div>

                                <div class="form-group">
                                    <label>Ukuran</label>
                                    <input type="text" name="size" class="form-input" placeholder="contoh: S, M, L, XL, 38, 40">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Ukuran produk (opsional)</small>
                                </div>

                                <div class="form-group">
                                    <label>Warna</label>
                                    <input type="text" name="color" class="form-input" placeholder="contoh: Biru, Merah, Hitam">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Warna utama (opsional)</small>
                                </div>

                                <div class="form-group">
                                    <label>Material</label>
                                    <input type="text" name="material" class="form-input" placeholder="contoh: Katun, Denim, Polyester">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Jenis kain atau material (opsional)</small>
                                </div>

                                <div class="form-group">
                                    <label>Berat (gram)</label>
                                    <input type="number" name="weight" class="form-input" min="0" placeholder="contoh: 500">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Untuk kalkulasi ongkir (opsional)</small>
                                </div>

                                <div class="form-group form-group-full">
                                    <label>Catatan Tambahan</label>
                                    <textarea name="notes" class="form-input" rows="3" placeholder="Informasi tambahan untuk pembeli..."></textarea>
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Detail ekstra, instruksi perawatan, dll. (opsional)</small>
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
                                <button type="button" onclick="switchTab(3)" style="background: #D97706; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
                                        onmouseover="this.style.background='#B45309'"
                                        onmouseout="this.style.background='#D97706'">
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
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    </svg>
                                    Status & Pengaturan
                                </h2>
                                <p style="margin: 0; color: #6b7280; font-size: 14px;">Konfigurasi status dan ketersediaan produk</p>
                            </div>

                            <div class="form-grid" style="gap: 24px;">
                                <div class="form-group">
                                    <label>Kondisi *</label>
                                    <select name="condition_item" class="form-input">
                                        <option value="">Pilih Kondisi</option>
                                        <option value="Excellent">Excellent - Seperti baru, tidak ada tanda pakai</option>
                                        <option value="Very Good">Very Good - Tanda pakai minimal</option>
                                        <option value="Good">Good - Ada tanda pakai tapi masih bagus</option>
                                        <option value="Fair">Fair - Tanda pakai terlihat, masih bisa dipakai</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>SKU / Kode Produk</label>
                                    <input type="text" name="sku" class="form-input" placeholder="contoh: VDJ-001">
                                    <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Stock Keeping Unit untuk tracking inventori (opsional)</small>
                                </div>

                                <div class="form-group form-group-full">
                                    <label style="font-weight: 600; color: #1f2937; margin-bottom: 12px; display: block; font-size: 14px;">Visibilitas Produk</label>
                                    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                                        <div style="flex: 1; min-width: 200px; background: #F0FDF4; border: 2px solid #10B981; border-radius: 8px; padding: 16px;">
                                            <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer;">
                                                <input type="radio" name="status" value="active" checked style="margin-top: 4px; width: 18px; height: 18px; cursor: pointer;">
                                                <div>
                                                    <div style="font-weight: 600; color: #065F46; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                        Aktif
                                                    </div>
                                                    <div style="font-size: 13px; color: #047857;">Produk terlihat oleh customer</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div style="flex: 1; min-width: 200px; background: #FEF3C7; border: 2px solid #F59E0B; border-radius: 8px; padding: 16px;">
                                            <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer;">
                                                <input type="radio" name="status" value="draft" style="margin-top: 4px; width: 18px; height: 18px; cursor: pointer;">
                                                <div>
                                                    <div style="font-weight: 600; color: #92400E; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                            <line x1="1" y1="1" x2="23" y2="23"></line>
                                                        </svg>
                                                        Draft
                                                    </div>
                                                    <div style="font-size: 13px; color: #B45309;">Tersembunyi dari customer</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group form-group-full">
                                    <div style="background: #FEF3C7; border: 2px solid #F59E0B; border-radius: 8px; padding: 16px;">
                                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                            <input type="checkbox" name="is_featured" id="is_featured" style="width: 20px; height: 20px; cursor: pointer;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#F59E0B" stroke="#F59E0B" stroke-width="2">
                                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                                </svg>
                                                <span style="font-weight: 600; color: #92400E;">Produk Unggulan</span>
                                            </div>
                                        </label>
                                        <p style="margin: 8px 0 0 32px; color: #B45309; font-size: 13px;">Tampilkan produk ini secara menonjol di homepage</p>
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

                        <!-- SUBMIT BUTTON (only shown on tab 4 - Status & Pengaturan) -->
                        <div id="submitButtonContainer" style="margin-top: 40px; padding-top: 24px; border-top: 2px solid #e5e7eb; display: none;">
                            <button type="submit" name="add_product" style="width: 100%; background: #10B981; color: white; border: none; padding: 14px 24px; border-radius: 6px; font-weight: 600; font-size: 15px; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 8px;"
                                    onmouseover="this.style.background='#059669'"
                                    onmouseout="this.style.background='#10B981'">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                Tambah Produk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            </div>
        </div>
    </div>

    <script>
        // Track uploaded images count
        let uploadedImagesCount = 0;
        let nextSlotToActivate = 2; // Next slot yang akan diaktifkan setelah upload
        
        // Safe toast function with fallback
        function safeToast(type, message) {
            if (typeof window.Toast !== 'undefined' && window.Toast[type]) {
                window.Toast[type](message);
            } else if (typeof window['toast' + type.charAt(0).toUpperCase() + type.slice(1)] === 'function') {
                window['toast' + type.charAt(0).toUpperCase() + type.slice(1)](message);
            } else {
                console.log(`[${type.toUpperCase()}] ${message}`);
            }
        }
        
        // Handle image upload for each slot
        function handleImageUpload(slotNumber, input) {
            console.log('handleImageUpload called for slot:', slotNumber);
            
            const file = input.files[0];
            if (!file) {
                console.log('No file selected');
                return;
            }
            
            console.log('File selected:', file.name, file.size, file.type);
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                safeToast('error', 'File harus berupa gambar!');
                alert('File harus berupa gambar!');
                input.value = '';
                return;
            }
            
            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                safeToast('error', 'Ukuran file maksimal 5MB!');
                alert('Ukuran file maksimal 5MB!');
                input.value = '';
                return;
            }
            
            console.log('File validation passed, reading file...');
            
            // Read and preview image
            const reader = new FileReader();
            reader.onload = function(e) {
                console.log('File read successfully, showing preview...');
                
                const slot = document.querySelector(`.image-slot[data-slot="${slotNumber}"]`);
                if (!slot) {
                    console.error('Slot not found:', slotNumber);
                    return;
                }
                
                const uploadArea = slot.querySelector('.upload-area');
                const previewArea = slot.querySelector('.preview-area');
                const previewImg = slot.querySelector('.preview-img');
                
                if (!uploadArea || !previewArea || !previewImg) {
                    console.error('Required elements not found in slot');
                    return;
                }
                
                // Show preview
                previewImg.src = e.target.result;
                uploadArea.style.display = 'none';
                previewArea.style.display = 'block';
                
                // Update counter
                uploadedImagesCount++;
                console.log('Image uploaded successfully. Total:', uploadedImagesCount);
                
                // Show success toast
                safeToast('success', `Foto ${slotNumber} berhasil ditambahkan!`);
                
                // Activate next slot if available (max 8 slots visible)
                if (nextSlotToActivate <= 8) {
                    activateNextSlot();
                }
            };
            
            reader.onerror = function() {
                console.error('Failed to read file');
                safeToast('error', 'Gagal membaca file!');
            };
            
            reader.readAsDataURL(file);
        }
        
        // Activate next empty slot
        function activateNextSlot() {
            console.log('activateNextSlot called. Next slot:', nextSlotToActivate);
            
            if (nextSlotToActivate > 8) {
                console.log('Max slots reached (8)');
                return; // Max 8 slots
            }
            
            const nextSlot = document.querySelector(`.image-slot[data-slot="${nextSlotToActivate}"]`);
            if (nextSlot && nextSlot.classList.contains('empty-slot')) {
                console.log('Activating slot:', nextSlotToActivate);
                
                // Remove empty-slot class
                nextSlot.classList.remove('empty-slot');
                nextSlot.classList.add('active-slot');
                
                // Change background to white
                nextSlot.style.background = 'white';
                
                // Add upload functionality
                nextSlot.innerHTML = `
                    <input type="file" name="product_images[]" id="imageInput${nextSlotToActivate}" class="image-input" accept="image/*" style="display: none;" onchange="handleImageUpload(${nextSlotToActivate}, this)">
                    <div class="upload-area" onclick="document.getElementById('imageInput${nextSlotToActivate}').click()" style="cursor: pointer; text-align: center; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin-bottom: 8px;">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <p style="margin: 0; color: #94a3b8; font-size: 11px;">Tambah foto</p>
                    </div>
                    <div class="preview-area" style="display: none; height: 100%;">
                        <img class="preview-img" src="" style="width: 100%; height: 100%; object-fit: cover;">
                        <button type="button" onclick="removeImage(${nextSlotToActivate})" style="position: absolute; top: 4px; right: 4px; background: #EF4444; color: white; border: none; width: 24px; height: 24px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); z-index: 10;">×</button>
                    </div>
                `;
                
                nextSlotToActivate++;
                console.log('Slot activated. Next slot will be:', nextSlotToActivate);
            } else {
                console.log('Slot not found or not empty');
            }
        }
        
        // Remove image from slot
        function removeImage(slotNumber) {
            console.log('removeImage called for slot:', slotNumber);
            
            const slot = document.querySelector(`.image-slot[data-slot="${slotNumber}"]`);
            if (!slot) {
                console.error('Slot not found:', slotNumber);
                return;
            }
            
            const input = slot.querySelector('.image-input') || slot.querySelector('input[type="file"]');
            const uploadArea = slot.querySelector('.upload-area');
            const previewArea = slot.querySelector('.preview-area');
            
            if (!input || !uploadArea || !previewArea) {
                console.error('Required elements not found in slot');
                return;
            }
            
            // Clear input
            input.value = '';
            
            // Reset to upload state
            uploadArea.style.display = 'flex';
            previewArea.style.display = 'none';
            
            // Update counter
            uploadedImagesCount--;
            console.log('Image removed. Total:', uploadedImagesCount);
            
            safeToast('info', `Foto ${slotNumber} dihapus`);
        }
        
        // Validate form before submit
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('productForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('Form submit triggered. Uploaded images:', uploadedImagesCount);
                    
                    // Validasi manual untuk semua field required
                    const productName = form.querySelector('[name="product_name"]').value.trim();
                    const category = form.querySelector('[name="category"]').value;
                    const price = form.querySelector('[name="price"]').value;
                    const description = form.querySelector('[name="description"]').value.trim();
                    const condition = form.querySelector('[name="condition_item"]').value;
                    
                    // Cek field kosong
                    if (!productName) {
                        e.preventDefault();
                        safeToast('error', 'Nama produk harus diisi!');
                        alert('Nama produk harus diisi!');
                        switchTab(0); // Tab 1: Info Dasar
                        form.querySelector('[name="product_name"]').focus();
                        return false;
                    }
                    
                    if (!category) {
                        e.preventDefault();
                        safeToast('error', 'Kategori harus dipilih!');
                        alert('Kategori harus dipilih!');
                        switchTab(0); // Tab 1: Info Dasar
                        form.querySelector('[name="category"]').focus();
                        return false;
                    }
                    
                    if (!price || price <= 0) {
                        e.preventDefault();
                        safeToast('error', 'Harga harus diisi dengan benar!');
                        alert('Harga harus diisi dengan benar!');
                        switchTab(0); // Tab 1: Info Dasar
                        form.querySelector('[name="price"]').focus();
                        return false;
                    }
                    
                    // Cek gambar
                    if (uploadedImagesCount === 0) {
                        e.preventDefault();
                        safeToast('error', 'Minimal upload 1 foto produk!');
                        alert('Minimal upload 1 foto produk!');
                        switchTab(1); // Tab 2: Gambar
                        return false;
                    }
                    
                    // Cek deskripsi
                    if (!description) {
                        e.preventDefault();
                        safeToast('error', 'Deskripsi produk harus diisi!');
                        alert('Deskripsi produk harus diisi!');
                        switchTab(2); // Tab 3: Deskripsi
                        form.querySelector('[name="description"]').focus();
                        return false;
                    }
                    
                    // Cek kondisi
                    if (!condition) {
                        e.preventDefault();
                        safeToast('error', 'Kondisi produk harus dipilih!');
                        alert('Kondisi produk harus dipilih!');
                        switchTab(3); // Tab 4: Status
                        form.querySelector('[name="condition_item"]').focus();
                        return false;
                    }
                    
                    console.log('All validations passed. Submitting form...');
                    return true;
                });
            }
        });
        
        // Initialize on page load
        console.log('=== Product Add Page Script Loaded ===');
        console.log('Toast available:', typeof window.Toast !== 'undefined');
        console.log('toastSuccess available:', typeof window.toastSuccess === 'function');
        console.log('toastError available:', typeof window.toastError === 'function');
        
        // Show toast notifications for success/error messages
        <?php if($success): ?>
            window.addEventListener('DOMContentLoaded', function() {
                console.log('Showing success message');
                safeToast('success', '<?php echo addslashes($success); ?>');
            });
        <?php endif; ?>
        
        <?php if($error): ?>
            window.addEventListener('DOMContentLoaded', function() {
                console.log('Showing error message');
                safeToast('error', '<?php echo addslashes($error); ?>');
            });
        <?php endif; ?>
    </script>

    <!-- Tab Navigation & Auto-Save Script -->
    <script>
        // Tab switching functionality
        function switchTab(tabIndex) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach((content) => {
                content.style.display = 'none';
            });
            
            // Show selected tab content
            const selectedTab = document.getElementById('tab-' + tabIndex);
            if (selectedTab) {
                selectedTab.style.display = 'block';
            }
            
            // Update tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach((btn, index) => {
                if (index === tabIndex) {
                    btn.classList.add('active');
                    btn.style.color = '#D97706';
                    btn.style.borderBottomColor = '#D97706';
                } else {
                    btn.classList.remove('active');
                    btn.style.color = '#6b7280';
                    btn.style.borderBottomColor = 'transparent';
                }
            });
            
            // Show/hide submit button - only show on tab 3 (Status & Pengaturan)
            const submitButtonContainer = document.getElementById('submitButtonContainer');
            if (tabIndex === 3) {
                submitButtonContainer.style.display = 'block';
            } else {
                submitButtonContainer.style.display = 'none';
            }
            
            // Scroll to top of form smoothly
            document.querySelector('.content-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Auto-save draft to localStorage
        let autoSaveInterval;
        
        function saveDraft() {
            const form = document.getElementById('productForm');
            const formData = new FormData(form);
            const draftData = {};
            
            // Save form fields (except files)
            for (let [key, value] of formData.entries()) {
                if (key !== 'product_images[]') {
                    draftData[key] = value;
                }
            }
            
            localStorage.setItem('product_draft', JSON.stringify(draftData));
            
            // Show saved indicator
            const status = document.getElementById('autoSaveStatus');
            status.style.display = 'inline-flex';
            status.style.alignItems = 'center';
            status.style.gap = '4px';
            
            setTimeout(() => {
                status.style.display = 'none';
            }, 2000);
        }

        // Load draft from localStorage
        function loadDraft() {
            const draft = localStorage.getItem('product_draft');
            if (draft) {
                const draftData = JSON.parse(draft);
                
                // Fill form fields
                for (let [key, value] of Object.entries(draftData)) {
                    const input = document.querySelector(`[name="${key}"]`);
                    if (input) {
                        if (input.type === 'checkbox') {
                            input.checked = value === 'on' || value === '1';
                        } else {
                            input.value = value;
                        }
                    }
                }
                
                // Show notification
                toastInfo('Draft dimuat dari auto-save');
            }
        }

        // Clear draft
        function clearDraft() {
            localStorage.removeItem('product_draft');
        }

        // Initialize auto-save
        document.addEventListener('DOMContentLoaded', function() {
            // Load draft on page load
            loadDraft();
            
            // Start auto-save every 30 seconds
            autoSaveInterval = setInterval(saveDraft, 30000);
            
            // Save on form input changes
            const form = document.getElementById('productForm');
            form.addEventListener('input', function() {
                clearTimeout(window.autoSaveTimeout);
                window.autoSaveTimeout = setTimeout(saveDraft, 3000);
            });
            
            // Clear draft on successful submit
            form.addEventListener('submit', function() {
                clearDraft();
            });
        });

        // Clear draft button (optional - can be added to UI)
        function clearDraftManually() {
            confirmModal('Apakah Anda yakin ingin menghapus draft yang tersimpan?<br>Tindakan ini tidak dapat dibatalkan.', function() {
                clearDraft();
                location.reload();
            }, null, {
                confirmText: 'Ya, Hapus',
                iconType: 'danger'
            });
        }
    </script>

    <!-- Tab Styling -->
    <style>
        .tab-button.active {
            color: #D97706 !important;
            border-bottom-color: #D97706 !important;
        }
        
        .tab-button:hover {
            color: #D97706 !important;
            background: rgba(217, 119, 6, 0.05);
        }
        
        .tab-content {
            animation: fadeInTab 0.3s ease-in;
        }
        
        #autoSaveStatus {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
</body>
</html>
