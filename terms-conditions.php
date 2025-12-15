<?php
/**
 * Halaman Terms & Conditions
 * Menampilkan syarat dan ketentuan penggunaan layanan RetroLoved
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
    <title>Terms & Conditions - RetroLoved</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/toast.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- PAGE HERO -->
    <section class="page-hero">
        <div class="container">
            <h1 class="page-title">Syarat & Ketentuan</h1>
            <p class="page-subtitle">Syarat dan ketentuan penggunaan layanan RetroLoved</p>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="content-section">
        <div class="container">
            <div class="content-wrapper legal-content">
                
                <div class="legal-block">
                    <h2>1. Acceptance of Terms</h2>
                    <p>Dengan mengakses dan menggunakan website RetroLoved, Anda setuju untuk terikat dengan syarat dan ketentuan berikut. Jika Anda tidak setuju dengan syarat dan ketentuan ini, mohon untuk tidak menggunakan layanan kami.</p>
                </div>

                <div class="legal-block">
                    <h2>2. Account Registration</h2>
                    <h3>2.1 Persyaratan Akun</h3>
                    <p>Untuk melakukan pembelian, Anda harus membuat akun dengan memberikan informasi yang akurat, lengkap, dan terkini.</p>
                    
                    <h3>2.2 Keamanan Akun</h3>
                    <p>Anda bertanggung jawab untuk menjaga kerahasiaan password dan akun Anda. Segala aktivitas yang terjadi di bawah akun Anda adalah tanggung jawab Anda.</p>
                    
                    <h3>2.3 Usia Minimum</h3>
                    <p>Anda harus berusia minimal 17 tahun atau memiliki izin dari orang tua/wali untuk menggunakan layanan kami.</p>
                </div>

                <div class="legal-block">
                    <h2>3. Product Information</h2>
                    <h3>3.1 Deskripsi Produk</h3>
                    <p>Kami berusaha untuk menampilkan foto dan deskripsi produk seakurat mungkin. Setiap produk dilengkapi dengan:</p>
                    <ul class="legal-list">
                        <li>Foto produk dari berbagai sudut (hingga 4 foto per produk)</li>
                        <li>Deskripsi detail kondisi produk</li>
                        <li>Informasi ukuran, warna, material, dan berat</li>
                        <li>Grade kondisi (Excellent, Very Good, Good, Fair)</li>
                        <li>Merek dan kategori produk</li>
                    </ul>
                    <p>Namun, kami tidak menjamin bahwa warna yang ditampilkan 100% akurat karena dapat berbeda tergantung layar device Anda.</p>
                    
                    <h3>3.2 Ketersediaan Produk</h3>
                    <p>Semua produk preloved kami adalah item unik dengan quantity terbatas (biasanya 1 item per produk). Produk yang sudah ditambahkan ke cart tidak otomatis direservasi. Produk akan direservasi setelah Anda menyelesaikan checkout dan melakukan pembayaran dalam batas waktu yang ditentukan.</p>
                    
                    <h3>3.3 Harga</h3>
                    <p>Harga yang tercantum dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya. Harga yang berlaku adalah harga pada saat pembelian dilakukan. Harga belum termasuk ongkos kirim yang akan dihitung otomatis saat checkout berdasarkan alamat tujuan.</p>
                    
                    <h3>3.4 Size Guide</h3>
                    <p>Kami menyediakan Size Guide untuk membantu Anda memilih ukuran yang tepat. Namun, karena produk kami adalah vintage/preloved, ukuran dapat sedikit berbeda dari standar modern. Selalu periksa detail ukuran yang tercantum di deskripsi produk.</p>
                </div>

                <div class="legal-block">
                    <h2>4. Orders & Payment</h2>
                    <h3>4.1 Pemesanan</h3>
                    <p>Dengan melakukan pemesanan, Anda membuat penawaran untuk membeli produk. Kami berhak untuk menerima atau menolak pesanan Anda dengan alasan apapun.</p>
                    
                    <h3>4.2 Batas Waktu Pembayaran</h3>
                    <p>Setelah checkout, Anda memiliki waktu 5 menit untuk melakukan pembayaran dan mengupload bukti pembayaran. Jika batas waktu terlewati, pesanan akan otomatis dibatalkan dan produk akan kembali tersedia untuk pembeli lain. Produk yang dibatalkan akan otomatis dikembalikan ke katalog.</p>
                    
                    <h3>4.3 Metode Pembayaran</h3>
                    <p>Kami menerima pembayaran melalui:</p>
                    <ul class="legal-list">
                        <li>Transfer Bank: BCA, Mandiri, BNI, BRI</li>
                        <li>E-Wallet: DANA, OVO, GoPay, ShopeePay</li>
                    </ul>
                    <p>Pembayaran harus dilakukan sesuai dengan metode yang dipilih saat checkout.</p>
                    
                    <h3>4.4 Upload Bukti Pembayaran</h3>
                    <p>Setelah melakukan pembayaran, Anda wajib mengupload bukti pembayaran (screenshot atau foto) dalam waktu yang ditentukan. Bukti pembayaran harus jelas menunjukkan:</p>
                    <ul class="legal-list">
                        <li>Jumlah yang ditransfer sesuai dengan total pesanan</li>
                        <li>Tanggal dan waktu transaksi</li>
                        <li>Detail transaksi yang lengkap</li>
                    </ul>
                    <p>Format file yang diterima: JPG, JPEG, PNG, GIF (maksimal 5MB)</p>
                    
                    <h3>4.5 Verifikasi Pembayaran</h3>
                    <p>Admin akan memverifikasi pembayaran Anda dalam waktu maksimal 1x24 jam. Anda dapat memantau status verifikasi di halaman "Pesanan Saya". Jika pembayaran ditolak, Anda akan menerima notifikasi dengan alasan penolakan.</p>
                    
                    <h3>4.6 Status Pesanan</h3>
                    <p>Pesanan Anda akan melalui beberapa status:</p>
                    <ul class="legal-list">
                        <li><strong>Pending:</strong> Menunggu upload atau verifikasi pembayaran</li>
                        <li><strong>Processing:</strong> Pembayaran diterima, pesanan sedang diproses</li>
                        <li><strong>Shipped:</strong> Pesanan sudah dikirim dengan nomor resi</li>
                        <li><strong>Delivered:</strong> Paket sudah sampai di tujuan</li>
                        <li><strong>Completed:</strong> Transaksi selesai</li>
                        <li><strong>Cancelled:</strong> Pesanan dibatalkan (otomatis atau manual)</li>
                    </ul>
                </div>

                <div class="legal-block">
                    <h2>5. Shipping & Delivery</h2>
                    <h3>5.1 Waktu Pengiriman</h3>
                    <p>Estimasi waktu pengiriman adalah perkiraan dan dapat bervariasi. Kami tidak bertanggung jawab atas keterlambatan yang disebabkan oleh pihak ekspedisi atau force majeure.</p>
                    
                    <h3>5.2 Biaya Pengiriman</h3>
                    <p>Biaya pengiriman akan ditampilkan saat checkout dan menjadi tanggung jawab pembeli.</p>
                    
                    <h3>5.3 Risiko Pengiriman</h3>
                    <p>Risiko kehilangan atau kerusakan produk beralih kepada Anda setelah produk diserahkan kepada ekspedisi.</p>
                </div>

                <div class="legal-block">
                    <h2>6. No Returns & No Refunds Policy</h2>
                    <p><strong>PENTING: Semua penjualan bersifat final. Kami TIDAK menerima pengembalian barang atau pengembalian uang (refund) dalam kondisi apapun.</strong></p>
                    
                    <h3>6.1 Kebijakan Final Sale</h3>
                    <p>Mengingat sifat unik dari produk preloved/vintage kami, semua item yang dibeli tidak dapat dikembalikan atau ditukar. Setiap produk adalah piece yang unik dan tidak dapat digantikan.</p>
                    
                    <h3>6.2 Tanggung Jawab Pembeli</h3>
                    <p>Pembeli bertanggung jawab untuk:</p>
                    <ul class="legal-list">
                        <li>Membaca deskripsi produk dengan teliti sebelum membeli</li>
                        <li>Memeriksa ukuran, kondisi, dan detail produk yang tercantum</li>
                        <li>Mengajukan pertanyaan kepada kami sebelum melakukan pembelian jika ada keraguan</li>
                        <li>Memastikan alamat pengiriman yang benar dan lengkap</li>
                    </ul>
                    
                    <h3>6.3 Kondisi Khusus</h3>
                    <p>Kami hanya akan mempertimbangkan komplain dalam kondisi berikut:</p>
                    <ul class="legal-list">
                        <li><strong>Produk Salah Kirim:</strong> Jika produk yang diterima berbeda dengan pesanan Anda</li>
                        <li><strong>Kerusakan Pengiriman:</strong> Produk rusak akibat pengiriman (harus dilaporkan maksimal 1x24 jam setelah penerimaan dengan foto/video unboxing sebagai bukti)</li>
                        <li><strong>Cacat Tersembunyi:</strong> Cacat material yang tidak disebutkan dalam deskripsi produk</li>
                    </ul>
                    
                    <h3>6.4 Prosedur Komplain</h3>
                    <p>Jika Anda mengalami salah satu kondisi di atas:</p>
                    <ul class="legal-list">
                        <li>Hubungi kami dalam waktu 1x24 jam setelah penerimaan barang</li>
                        <li>Sertakan foto/video unboxing yang jelas</li>
                        <li>Berikan nomor order dan deskripsi masalah secara detail</li>
                        <li>Kami akan meninjau kasus Anda dalam 2-3 hari kerja</li>
                    </ul>
                    
                    <h3>6.5 Tidak Ada Refund untuk:</h3>
                    <ul class="legal-list">
                        <li>Perubahan pikiran atau "tidak suka" setelah menerima produk</li>
                        <li>Ukuran yang tidak pas (pastikan cek size guide sebelum membeli)</li>
                        <li>Warna yang sedikit berbeda dari foto (bisa disebabkan oleh layar device)</li>
                        <li>Kondisi "preloved" normal yang sudah dijelaskan dalam deskripsi</li>
                        <li>Keterlambatan pengiriman oleh pihak ekspedisi</li>
                        <li>Alamat pengiriman yang salah atau tidak lengkap</li>
                    </ul>
                    
                    <h3>6.6 Persetujuan Pembeli</h3>
                    <p>Dengan melakukan pembelian di RetroLoved, Anda menyatakan bahwa:</p>
                    <ul class="legal-list">
                        <li>Anda telah membaca dan memahami kebijakan No Returns & No Refunds ini</li>
                        <li>Anda setuju bahwa semua penjualan bersifat final</li>
                        <li>Anda tidak akan mengajukan dispute atau chargeback tanpa alasan yang sah</li>
                        <li>Anda memahami risiko membeli produk preloved/vintage</li>
                    </ul>
                </div>

                <div class="legal-block">
                    <h2>7. Intellectual Property</h2>
                    <h3>7.1 Hak Cipta</h3>
                    <p>Semua konten di website ini, termasuk teks, grafik, logo, gambar, dan software adalah milik RetroLoved atau pemberi lisensi kami dan dilindungi oleh hukum hak cipta.</p>
                    
                    <h3>7.2 Penggunaan Terbatas</h3>
                    <p>Anda tidak diperkenankan untuk mereproduksi, mendistribusikan, atau menggunakan konten kami untuk tujuan komersial tanpa izin tertulis.</p>
                </div>

                <div class="legal-block">
                    <h2>8. User Conduct</h2>
                    <p>Anda setuju untuk tidak:</p>
                    <ul class="legal-list">
                        <li>Menggunakan website untuk tujuan yang melanggar hukum</li>
                        <li>Mengirimkan konten yang menyinggung, mengancam, atau tidak pantas</li>
                        <li>Mencoba mengakses area yang tidak diizinkan</li>
                        <li>Mengganggu atau merusak website atau server</li>
                        <li>Menggunakan robot, spider, atau alat otomatis lainnya</li>
                    </ul>
                </div>

                <div class="legal-block">
                    <h2>9. Limitation of Liability</h2>
                    <p>RetroLoved tidak bertanggung jawab atas kerugian langsung, tidak langsung, insidental, atau konsekuensial yang timbul dari penggunaan atau ketidakmampuan menggunakan layanan kami.</p>
                </div>

                <div class="legal-block">
                    <h2>10. Privacy</h2>
                    <p>Penggunaan informasi pribadi Anda diatur dalam Privacy Policy kami yang merupakan bagian tidak terpisahkan dari Terms & Conditions ini.</p>
                </div>

                <div class="legal-block">
                    <h2>11. Modifications</h2>
                    <p>Kami berhak untuk mengubah Terms & Conditions ini sewaktu-waktu. Perubahan akan efektif segera setelah dipublikasikan di website. Penggunaan website setelah perubahan berarti Anda menerima Terms & Conditions yang baru.</p>
                </div>

                <div class="legal-block">
                    <h2>12. Termination</h2>
                    <p>Kami berhak untuk menangguhkan atau menghentikan akses Anda ke layanan kami kapan saja tanpa pemberitahuan sebelumnya jika Anda melanggar Terms & Conditions ini.</p>
                </div>

                <div class="legal-block">
                    <h2>13. Governing Law</h2>
                    <p>Terms & Conditions ini diatur oleh dan ditafsirkan sesuai dengan hukum Republik Indonesia. Setiap perselisihan akan diselesaikan melalui pengadilan yang berwenang di Indonesia.</p>
                </div>

                <div class="legal-block">
                    <h2>14. Contact Information</h2>
                    <p>Jika Anda memiliki pertanyaan tentang Terms & Conditions ini, silakan hubungi kami di:</p>
                    <ul class="contact-list">
                        <li>Email: retroloved.ofc@gmail.com</li>
                        <li>Phone: +62 813-3601-9251 / +62 812-3179-3810</li>
                        <li>Address: Surabaya, Indonesia</li>
                    </ul>
                </div>

                <div class="legal-footer">
                    <p><strong>Last Updated:</strong> <?php echo date('F Y'); ?></p>
                    <p>Dengan menggunakan website RetroLoved, Anda mengakui bahwa Anda telah membaca, memahami, dan setuju untuk terikat oleh Terms & Conditions ini.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <div class="container" style="padding-top: 40px; padding-bottom: 80px;">
        <div class="faq-contact">
            <h3>Ada Pertanyaan Tentang Syarat & Ketentuan?</h3>
            <p>Tim customer service kami siap membantu Anda</p>
            <a href="javascript:void(0)" onclick="showContactSupportModal()" class="btn btn-primary">Contact Support</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
