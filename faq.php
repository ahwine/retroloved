<?php
/**
 * Halaman FAQ (Frequently Asked Questions)
 * Menampilkan pertanyaan yang sering ditanyakan beserta jawabannya
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
    <title>FAQ - RetroLoved</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/toast.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- FAQ HERO -->
    <section class="page-hero">
        <div class="container">
            <h1 class="page-title">Pertanyaan yang Sering Ditanyakan</h1>
            <p class="page-subtitle">Temukan jawaban untuk pertanyaan yang sering ditanyakan</p>
        </div>
    </section>

    <!-- FAQ CONTENT -->
    <section class="faq-section">
        <div class="container">
            <div class="faq-container">
                
                <!-- Pertanyaan Umum -->
                <div class="faq-category">
                    <h2 class="faq-category-title">Pertanyaan Umum</h2>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Apa itu RetroLoved?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>RetroLoved adalah platform e-commerce yang menghadirkan koleksi fashion vintage dan preloved berkualitas tinggi. Kami menjual pakaian second-hand dari era 70s-90s yang telah diseleksi dan diverifikasi kondisinya.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Apa perbedaan antara vintage dan preloved?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Vintage mengacu pada pakaian yang berusia 20 tahun atau lebih dan memiliki nilai historis atau gaya khas dari era tertentu. Preloved adalah istilah untuk pakaian bekas berkualitas baik yang pernah digunakan sebelumnya, tanpa batasan usia tertentu.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Apakah semua produk dijamin original?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Ya, semua produk yang kami jual telah melalui proses verifikasi keaslian dan quality check. Kami hanya menjual produk original dan berkualitas baik.</p>
                        </div>
                    </div>
                </div>

                <!-- Belanja & Pesanan -->
                <div class="faq-category">
                    <h2 class="faq-category-title">Belanja & Pesanan</h2>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Bagaimana cara memesan produk?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>1. Pilih produk yang Anda inginkan dan klik "Tambah ke Keranjang" atau "Beli Sekarang"<br>
                            2. Jika memilih "Tambah ke Keranjang", Anda bisa melanjutkan belanja atau langsung checkout<br>
                            3. Di halaman checkout, isi data pengiriman lengkap Anda<br>
                            4. Pilih metode pembayaran (Transfer Bank atau E-Wallet)<br>
                            5. Selesaikan pesanan dan Anda akan mendapat batas waktu pembayaran 5 menit<br>
                            6. Upload bukti pembayaran dalam waktu yang ditentukan<br>
                            7. Tunggu konfirmasi dari admin (maksimal 1x24 jam)</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Apakah saya perlu membuat akun untuk berbelanja?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Ya, Anda perlu membuat akun untuk melakukan pembelian. Akun membantu Anda melacak pesanan, menyimpan alamat pengiriman, dan melihat riwayat transaksi.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Berapa lama proses verifikasi pembayaran?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Proses verifikasi pembayaran dilakukan dalam waktu maksimal 1x24 jam setelah Anda mengupload bukti pembayaran. Anda dapat memantau status pesanan di halaman "Pesanan Saya" dan akan mendapatkan notifikasi real-time ketika status berubah.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Bagaimana jika produk yang saya inginkan sold out?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Produk preloved kami adalah item unik dengan quantity terbatas (biasanya hanya 1 item per produk). Jika produk sudah sold out, Anda dapat menjelajahi koleksi serupa di halaman Shop atau menunggu update koleksi baru kami. Anda juga bisa menggunakan fitur "Recently Viewed" untuk melihat produk yang pernah Anda lihat sebelumnya.</p>
                        </div>
                    </div>
                </div>

                <!-- Pembayaran & Harga -->
                <div class="faq-category">
                    <h2 class="faq-category-title">Pembayaran & Harga</h2>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Metode pembayaran apa saja yang diterima?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Kami menerima pembayaran melalui transfer bank (BCA, Mandiri, BNI, BRI) dan e-wallet (DANA, OVO, GoPay, ShopeePay). Pilih metode yang paling nyaman untuk Anda saat checkout.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Apakah harga yang tertera sudah termasuk ongkir?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Harga yang tertera adalah harga produk saja, belum termasuk ongkos kirim. Biaya pengiriman akan dihitung otomatis saat checkout berdasarkan alamat tujuan Anda.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Apakah ada biaya tambahan selain harga produk dan ongkir?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Tidak ada biaya tambahan. Total yang Anda bayar adalah harga produk ditambah ongkos kirim saja. Kami tidak mengenakan biaya administrasi atau biaya tersembunyi lainnya.</p>
                        </div>
                    </div>
                </div>

                <!-- Kondisi Produk -->
                <div class="faq-category">
                    <h2 class="faq-category-title">Kondisi Produk</h2>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Apa arti dari grade kondisi produk (Excellent, Good, Fair)?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p><strong>Excellent:</strong> Kondisi sangat baik, seperti baru, tanpa cacat berarti.<br>
                            <strong>Good:</strong> Kondisi baik, mungkin ada sedikit tanda pemakaian normal.<br>
                            <strong>Fair:</strong> Kondisi layak pakai, mungkin ada beberapa tanda pemakaian atau cacat minor yang telah dijelaskan di deskripsi.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Apakah produk sudah dicuci sebelum dikirim?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Ya, semua produk telah melalui proses pembersihan dan quality check sebelum dipasarkan. Namun, kami tetap merekomendasikan untuk mencuci produk sebelum digunakan.</p>
                        </div>
                    </div>
                </div>

                <!-- Akun & Keamanan -->
                <div class="faq-category">
                    <h2 class="faq-category-title">Akun & Keamanan</h2>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Bagaimana cara mengubah password akun saya?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Login ke akun Anda, klik icon user di pojok kanan atas, pilih "Pengaturan", lalu klik "Ubah Password". Masukkan password lama dan password baru Anda, kemudian klik "Update Password".</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Apa itu batas waktu pembayaran 5 menit?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Setelah checkout, Anda memiliki waktu 5 menit untuk melakukan pembayaran dan mengupload bukti pembayaran. Ini untuk memastikan produk tidak tertahan terlalu lama. Jika waktu habis, pesanan akan otomatis dibatalkan dan produk kembali tersedia untuk pembeli lain. Anda dapat melihat countdown timer di halaman "Pesanan Saya".</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Bagaimana cara melacak pesanan saya?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Anda dapat melacak pesanan melalui halaman "Pesanan Saya". Klik tombol "Lihat Detail" pada pesanan yang ingin dilacak. Di halaman detail, Anda akan melihat status pesanan, nomor resi (jika sudah dikirim), dan timeline lengkap perjalanan pesanan Anda dari pemesanan hingga pengiriman.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Apa fungsi fitur "Recently Viewed"?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Fitur "Recently Viewed" (Baru Dilihat) menampilkan produk-produk yang baru saja Anda lihat. Ini memudahkan Anda untuk kembali ke produk yang menarik perhatian tanpa harus mencari lagi. Fitur ini tersimpan di browser Anda selama 30 hari.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Bagaimana cara mengarsipkan pesanan?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Di halaman "Pesanan Saya", Anda dapat mengarsipkan pesanan yang sudah selesai untuk merapikan daftar pesanan. Centang pesanan yang ingin diarsipkan, lalu klik "Arsipkan yang Dipilih". Pesanan yang diarsipkan dapat dilihat di tab "Diarsipkan" dan dapat dikembalikan kapan saja.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Apakah data pribadi saya aman?</span>
                            <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Ya, kami sangat menjaga keamanan data pribadi Anda. Semua informasi disimpan dengan enkripsi dan tidak akan dibagikan kepada pihak ketiga tanpa izin Anda. Baca <a href="privacy-policy.php">Privacy Policy</a> kami untuk detail lebih lanjut.</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Contact CTA -->
            <div class="faq-contact">
                <h3>Tidak menemukan jawaban yang Anda cari?</h3>
                <p>Tim customer service kami siap membantu Anda</p>
                <a href="javascript:void(0)" onclick="showContactSupportModal()" class="btn btn-primary">Contact Support</a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- FAQ JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const faqQuestions = document.querySelectorAll('.faq-question');
            
            faqQuestions.forEach(question => {
                question.addEventListener('click', function() {
                    const faqItem = this.parentElement;
                    const isActive = faqItem.classList.contains('active');
                    
                    // Close all other FAQ items
                    document.querySelectorAll('.faq-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    
                    // Toggle current item
                    if (!isActive) {
                        faqItem.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>
</html>
