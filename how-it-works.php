<?php
/**
 * Halaman How It Works
 * Menjelaskan cara kerja sistem penjualan di RetroLoved
 * RetroLoved E-Commerce System
 */

session_start();
require_once 'config/database.php';
$base_url = '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>How It Works - RetroLoved</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/toast.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- HOW IT WORKS HERO -->
    <section class="page-hero">
        <div class="container">
            <h1 class="page-title">Cara Kerja</h1>
            <p class="page-subtitle">Belanja fashion vintage & preloved dengan mudah, aman, dan terpercaya di RetroLoved</p>
        </div>
    </section>

    <!-- STEPS SECTION -->
    <section class="steps-section">
        <div class="container">
            <div class="section-intro">
                <h2 class="section-title">Belanja dalam 6 Langkah Mudah</h2>
                <p class="section-description">Dari browsing hingga produk sampai di tangan Anda, kami pastikan prosesnya simpel dan menyenangkan!</p>
            </div>

            <div class="steps-grid">
                <!-- Step 1: Register & Browse -->
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <h3 class="step-title">Daftar & Jelajahi</h3>
                    <p class="step-description">Daftar akun gratis atau langsung browse koleksi fashion vintage kami. Filter berdasarkan kategori, harga, dan kondisi produk untuk menemukan treasure favorit Anda!</p>
                </div>

                <!-- Step 2: Add to Cart -->
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                    </div>
                    <h3 class="step-title">Tambah ke Keranjang</h3>
                    <p class="step-description">Pilih produk favorit dan tambahkan ke keranjang belanja. Setiap item adalah barang preloved unik dengan quantity terbatas - first come, first served!</p>
                </div>

                <!-- Step 3: Checkout & Payment -->
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                    </div>
                    <h3 class="step-title">Checkout & Pembayaran</h3>
                    <p class="step-description">Isi data pengiriman dan pilih metode pembayaran. Kami menerima transfer bank dan e-wallet untuk kemudahan Anda.</p>
                </div>

                <!-- Step 4: Upload Proof -->
                <div class="step-card">
                    <div class="step-number">4</div>
                    <div class="step-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                    </div>
                    <h3 class="step-title">Upload Bukti Pembayaran</h3>
                    <p class="step-description">Upload bukti pembayaran melalui halaman My Orders. Tim kami akan memverifikasi pembayaran Anda dalam 1x24 jam.</p>
                </div>

                <!-- Step 5: We Ship It -->
                <div class="step-card">
                    <div class="step-number">5</div>
                    <div class="step-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="3" width="15" height="13"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                        </svg>
                    </div>
                    <h3 class="step-title">Kami Kirimkan</h3>
                    <p class="step-description">Setelah pembayaran terverifikasi, pesanan Anda akan segera kami proses dan kirim ke alamat yang Anda berikan.</p>
                </div>

                <!-- Step 6: Receive & Enjoy -->
                <div class="step-card">
                    <div class="step-number">6</div>
                    <div class="step-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"></path>
                        </svg>
                    </div>
                    <h3 class="step-title">Terima & Nikmati</h3>
                    <p class="step-description">Terima paket Anda dan nikmati fashion vintage berkualitas. Jangan lupa untuk konfirmasi penerimaan pesanan.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- KEY FEATURES -->
    <section class="content-section">
        <div class="container">
            <div class="content-wrapper">
                <div class="content-block">
                    <h2 class="content-title">Fitur Utama</h2>
                    <p class="content-text">Platform lengkap dengan berbagai fitur untuk pengalaman belanja yang sempurna</p>
                    
                    <div class="feature-list">
                        <div class="feature-item">
                            <div class="feature-check">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Manajemen Profil</h4>
                                <p>Kelola profil lengkap dengan foto profil dan update data diri</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-check">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Banyak Alamat</h4>
                                <p>Simpan banyak alamat pengiriman dan atur alamat default</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-check">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Lacak Pesanan</h4>
                                <p>Track status pesanan real-time dari pending hingga delivered</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-check">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Notifikasi Real-time</h4>
                                <p>Dapatkan notifikasi instant untuk setiap update status pesanan</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-check">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Keranjang Belanja Pintar</h4>
                                <p>Keranjang belanja dengan auto-save dan validasi stok</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-check">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Pembayaran Aman</h4>
                                <p>Sistem pembayaran aman dengan verifikasi admin</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PAYMENT METHODS -->
    <section class="info-section">
        <div class="container">
            <div class="info-content">
                <h2 class="section-title">Metode Pembayaran</h2>
                <p class="section-subtitle">Kami menerima berbagai metode pembayaran untuk kemudahan Anda</p>
                
                <div class="payment-grid">
                    <div class="payment-card">
                        <div class="payment-icon-box">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                <line x1="2" y1="10" x2="22" y2="10"></line>
                            </svg>
                        </div>
                        <h4>Transfer Bank</h4>
                        <p>BCA, Mandiri, BNI, BRI</p>
                    </div>
                    
                    <div class="payment-card">
                        <div class="payment-icon-box">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                <line x1="12" y1="18" x2="12.01" y2="18"></line>
                            </svg>
                        </div>
                        <h4>E-Wallet</h4>
                        <p>DANA, OVO, GoPay, ShopeePay</p>
                    </div>
                </div>
                
                <div class="info-note">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <p>Setelah checkout, upload bukti pembayaran di halaman <strong>My Orders</strong>. Tim kami akan memverifikasi dalam 1x24 jam.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SHIPPING INFO -->
    <section class="info-section alternate">
        <div class="container">
            <div class="info-content">
                <h2 class="section-title">Informasi Pengiriman</h2>
                <p class="section-subtitle">Pengiriman cepat dan aman ke seluruh Indonesia</p>
                
                <div class="shipping-info-grid">
                    <div class="info-box">
                        <div class="info-box-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <h4>Waktu Proses</h4>
                        <p>1-2 hari kerja setelah pembayaran terverifikasi</p>
                    </div>
                    
                    <div class="info-box">
                        <div class="info-box-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <h4>Waktu Pengiriman</h4>
                        <p>2-5 hari kerja tergantung lokasi pengiriman</p>
                    </div>
                    
                    <div class="info-box">
                        <div class="info-box-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                            </svg>
                        </div>
                        <h4>Pengemasan</h4>
                        <p>Dikemas dengan rapi dan aman untuk melindungi produk</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <div class="container" style="padding-top: 40px; padding-bottom: 80px;">
        <div class="faq-contact">
            <h3>Butuh Bantuan Lebih Lanjut?</h3>
            <p>Tim customer service kami siap membantu Anda</p>
            <a href="javascript:void(0)" onclick="showContactSupportModal()" class="btn btn-primary">Contact Support</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
