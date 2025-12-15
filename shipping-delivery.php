<?php
/**
 * Halaman Shipping & Delivery
 * Menampilkan informasi pengiriman dan estimasi waktu delivery
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
    <title>Shipping & Delivery - RetroLoved</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/toast.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- PAGE HERO -->
    <section class="page-hero">
        <div class="container">
            <h1 class="page-title">Pengiriman & Delivery</h1>
            <p class="page-subtitle">Informasi lengkap tentang pengiriman dan estimasi waktu</p>
        </div>
    </section>

    <!-- SHIPPING INFO CONTENT -->
    <section class="content-section">
        <div class="container">
            <div class="content-wrapper">
                
                <!-- Shipping Methods -->
                <div class="content-block">
                    <h2 class="content-title">Metode Pengiriman</h2>
                    <p class="content-text">Kami bekerja sama dengan berbagai ekspedisi terpercaya untuk memastikan paket Anda sampai dengan aman dan tepat waktu.</p>
                    
                    <div class="shipping-methods-list">
                        <?php
                        require_once 'config/shipping.php';
                        $couriers = query("SELECT DISTINCT sc.courier_name, sc.courier_code 
                                          FROM shipping_couriers sc
                                          JOIN shipping_services ss ON sc.courier_id = ss.courier_id
                                          WHERE sc.is_active = 1 AND ss.is_active = 1
                                          ORDER BY sc.courier_id ASC");
                        
                        while($courier = mysqli_fetch_assoc($couriers)):
                            // Get services for this courier
                            $services = query("SELECT service_name FROM shipping_services ss
                                             JOIN shipping_couriers sc ON ss.courier_id = sc.courier_id
                                             WHERE sc.courier_code = '{$courier['courier_code']}' 
                                             AND ss.is_active = 1
                                             ORDER BY ss.display_order ASC");
                            
                            $service_names = [];
                            while($svc = mysqli_fetch_assoc($services)) {
                                $service_names[] = $svc['service_name'];
                            }
                        ?>
                        <div class="method-item">
                            <div class="method-icon-inline">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="1" y="3" width="15" height="13"></rect>
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                </svg>
                            </div>
                            <div class="method-info">
                                <strong><?php echo htmlspecialchars($courier['courier_name']); ?></strong>
                                <span class="method-services"><?php echo implode(', ', $service_names); ?></span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Processing & Delivery Time -->
                <div class="content-block">
                    <h2 class="content-title">Waktu Proses & Pengiriman</h2>
                    
                    <div class="timeline-container">
                        <div class="timeline-item">
                            <div class="timeline-marker">1</div>
                            <div class="timeline-content">
                                <h4>Pesanan Dibuat</h4>
                                <p>Pesanan diterima dan menunggu pembayaran</p>
                                <span class="timeline-time">Hari ke-0</span>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker">2</div>
                            <div class="timeline-content">
                                <h4>Verifikasi Pembayaran</h4>
                                <p>Tim kami memverifikasi bukti pembayaran Anda</p>
                                <span class="timeline-time">1-24 jam</span>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker">3</div>
                            <div class="timeline-content">
                                <h4>Proses Pesanan</h4>
                                <p>Produk dikemas dengan rapi dan siap kirim</p>
                                <span class="timeline-time">1-2 hari kerja</span>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker">4</div>
                            <div class="timeline-content">
                                <h4>Dikirim</h4>
                                <p>Paket dikirim melalui ekspedisi pilihan Anda</p>
                                <span class="timeline-time">Hari ke-3</span>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker">5</div>
                            <div class="timeline-content">
                                <h4>Terkirim</h4>
                                <p>Paket sampai di alamat tujuan</p>
                                <span class="timeline-time">2-5 hari kerja</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Rates -->
                <div class="content-block">
                    <h2 class="content-title">Tarif Pengiriman</h2>
                    <p class="content-text">Biaya pengiriman bervariasi tergantung ekspedisi dan layanan yang dipilih. Berikut estimasi biaya pengiriman kami:</p>
                    
                    <div class="rates-table">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: var(--bg-gray); border-bottom: 2px solid var(--border);">
                                    <th style="padding: 12px; text-align: left; font-weight: 600;">Ekspedisi</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600;">Layanan</th>
                                    <th style="padding: 12px; text-align: center; font-weight: 600;">Estimasi</th>
                                    <th style="padding: 12px; text-align: right; font-weight: 600;">Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $all_services = query("SELECT sc.courier_name, ss.service_name, ss.base_cost, 
                                                             ss.estimated_days_min, ss.estimated_days_max
                                                      FROM shipping_services ss
                                                      JOIN shipping_couriers sc ON ss.courier_id = sc.courier_id
                                                      WHERE ss.is_active = 1 AND sc.is_active = 1
                                                      ORDER BY ss.display_order ASC");
                                
                                while($rate = mysqli_fetch_assoc($all_services)):
                                ?>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 12px; color: var(--text-dark); font-weight: 500;">
                                        <?php echo htmlspecialchars($rate['courier_name']); ?>
                                    </td>
                                    <td style="padding: 12px; color: var(--text-gray);">
                                        <?php echo htmlspecialchars($rate['service_name']); ?>
                                    </td>
                                    <td style="padding: 12px; text-align: center; color: var(--text-gray); font-size: 14px;">
                                        <?php 
                                        if($rate['estimated_days_min'] == 0 && $rate['estimated_days_max'] == 0) {
                                            echo 'Instant';
                                        } elseif($rate['estimated_days_min'] == $rate['estimated_days_max']) {
                                            echo $rate['estimated_days_min'] . ' hari';
                                        } else {
                                            echo $rate['estimated_days_min'] . '-' . $rate['estimated_days_max'] . ' hari';
                                        }
                                        ?>
                                    </td>
                                    <td style="padding: 12px; text-align: right; color: var(--primary); font-weight: 600;">
                                        <?php 
                                        if($rate['base_cost'] == 0) {
                                            echo '<span style="color: var(--success);">GRATIS</span>';
                                        } else {
                                            echo 'Rp ' . number_format($rate['base_cost'], 0, ',', '.');
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: 20px; padding: 16px; background: var(--bg-gray); border-radius: 8px; border-left: 4px solid var(--primary);">
                        <p style="margin: 0; color: var(--text-gray); font-size: 14px; line-height: 1.6;">
                            <strong style="color: var(--text-dark);">Catatan:</strong> Biaya pengiriman di atas adalah estimasi. 
                            Biaya aktual akan ditampilkan saat Anda memilih layanan di halaman checkout.
                        </p>
                    </div>
                </div>

                <!-- Packaging -->
                <div class="content-block">
                    <h2 class="content-title">Standar Pengemasan</h2>
                    <p class="content-text">Kami memastikan setiap produk dikemas dengan standar terbaik untuk melindungi barang selama pengiriman.</p>
                    
                    <div class="feature-list">
                        <div class="feature-item">
                            <div class="feature-check">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Perlindungan Bubble Wrap</h4>
                                <p>Produk dibungkus dengan bubble wrap untuk perlindungan ekstra</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-check">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Kardus Kokoh</h4>
                                <p>Menggunakan kardus tebal dan kokoh</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-check">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Plastik Tahan Air</h4>
                                <p>Lapisan plastik tahan air untuk melindungi dari hujan</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-check">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Segel Lakban Kuat</h4>
                                <p>Disegel dengan lakban kuat untuk keamanan maksimal</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tracking -->
                <div class="content-block">
                    <h2 class="content-title">Lacak Pesanan</h2>
                    <p class="content-text">Pantau status pengiriman pesanan Anda dengan mudah:</p>
                    
                    <div class="steps-simple">
                        <div class="step-simple">
                            <div class="step-number-small">1</div>
                            <p>Login ke akun Anda</p>
                        </div>
                        <div class="step-simple">
                            <div class="step-number-small">2</div>
                            <p>Buka halaman "My Orders"</p>
                        </div>
                        <div class="step-simple">
                            <div class="step-number-small">3</div>
                            <p>Klik "Track Order" untuk melihat status pengiriman</p>
                        </div>
                        <div class="step-simple">
                            <div class="step-number-small">4</div>
                            <p>Salin nomor resi untuk tracking di website ekspedisi</p>
                        </div>
                    </div>
                </div>

                <!-- Important Notes -->
                <div class="content-block alert-block">
                    <h2 class="content-title">Catatan Penting</h2>
                    <div class="alert-list">
                        <div class="alert-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <p>Estimasi waktu pengiriman tidak termasuk hari Sabtu, Minggu, dan hari libur nasional</p>
                        </div>
                        <div class="alert-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <p>Keterlambatan pengiriman di luar kendali kami menjadi tanggung jawab pihak ekspedisi</p>
                        </div>
                        <div class="alert-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <p>Pastikan alamat pengiriman yang Anda berikan lengkap dan akurat</p>
                        </div>
                        <div class="alert-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <p>Harap periksa kondisi paket sebelum menerima dari kurir. Jika ada kerusakan, segera hubungi customer service</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <div class="container" style="padding-top: 40px; padding-bottom: 80px;">
        <div class="faq-contact">
            <h3>Butuh Bantuan Seputar Pengiriman?</h3>
            <p>Tim customer service kami siap membantu Anda</p>
            <a href="javascript:void(0)" onclick="showContactSupportModal()" class="btn btn-primary">Contact Support</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
