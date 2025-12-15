    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <!-- Bagian Brand -->
                <div class="footer-section">
                    <div class="footer-logo">
                        <h3>RetroLoved</h3>
                        <div class="footer-tagline">Vintage Fashion, Modern Style</div>
                    </div>
                    <p class="footer-description">Platform penjualan fashion vintage dan preloved terpercaya di Indonesia. Temukan gaya unik Anda bersama kami.</p>
                    
                    <!-- Link Media Sosial -->
                    <div class="social-links">
                        <a href="https://www.instagram.com/retroloved.ofc?igsh=dHV5djN4cWdtcTZm" target="_blank" rel="noopener noreferrer" class="social-icon" title="Ikuti kami di Instagram" aria-label="Instagram">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                            </svg>
                        </a>
                        <a href="https://www.facebook.com/share/1BxsCG2uey/" target="_blank" rel="noopener noreferrer" class="social-icon" title="Like us on Facebook" aria-label="Facebook">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                            </svg>
                        </a>
                        <a href="https://x.com/RetroLoved" target="_blank" rel="noopener noreferrer" class="social-icon" title="Ikuti kami di X" aria-label="X (Twitter)">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                        <a href="https://www.tiktok.com/@retroloved?_r=1&_t=ZS-92EQeBEFgQC" target="_blank" rel="noopener noreferrer" class="social-icon" title="Ikuti kami di TikTok" aria-label="TikTok">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Link Cepat -->
                <div class="footer-section">
                    <h4 class="footer-title">Link Cepat</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo isset($base_url) ? $base_url : '../'; ?>index.php">Beranda</a></li>
                        <li><a href="<?php echo isset($base_url) ? $base_url : '../'; ?>shop.php">Belanja</a></li>
                        <li><a href="<?php echo isset($base_url) ? $base_url : '../'; ?>index.php#about">Tentang Kami</a></li>
                        <li><a href="<?php echo isset($base_url) ? $base_url : '../'; ?>how-it-works.php">Cara Kerja</a></li>
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'customer'): ?>
                            <li><a href="<?php echo isset($base_url) ? $base_url : '../'; ?>customer/orders.php">Pesanan Saya</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Layanan Pelanggan -->
                <div class="footer-section">
                    <h4 class="footer-title">Layanan Pelanggan</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo isset($base_url) ? $base_url : '../'; ?>faq.php">FAQ</a></li>
                        <li><a href="<?php echo isset($base_url) ? $base_url : '../'; ?>shipping-delivery.php">Pengiriman & Delivery</a></li>
                        <li><a href="<?php echo isset($base_url) ? $base_url : '../'; ?>size-guide.php">Panduan Ukuran</a></li>
                        <li><a href="<?php echo isset($base_url) ? $base_url : '../'; ?>terms-conditions.php">Syarat & Ketentuan</a></li>
                        <li><a href="<?php echo isset($base_url) ? $base_url : '../'; ?>privacy-policy.php">Kebijakan Privasi</a></li>
                    </ul>
                </div>

                <!-- Informasi Kontak -->
                <div class="footer-section">
                    <h4 class="footer-title">Hubungi Kami</h4>
                    <ul class="footer-contact">
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span>Jl. Tentara Genie Pelajar No.26, Petemon, Kec. Sawahan, Surabaya, Jawa Timur 60252</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 10px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <a href="mailto:retroloved.ofc@gmail.com" style="display: block;">retroloved.ofc@gmail.com</a>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 10px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <span style="display: flex; flex-direction: column;"><a href="https://wa.me/6281336019251" target="_blank" rel="noopener noreferrer">+62 813-3601-9251</a><a href="https://wa.me/6281231793810" target="_blank" rel="noopener noreferrer">+62 812-3179-3810</a></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bagian Bawah Footer -->
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p class="copyright">&copy; <?php echo date('Y'); ?> RetroLoved. Hak Cipta Dilindungi.</p>
                    <p class="credits">Dibuat dengan <svg width="14" height="14" viewBox="0 0 24 24" fill="#EF4444" stroke="none" style="display: inline-block; vertical-align: middle; margin: 0 2px;"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg> di Surabaya, Indonesia</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Contact Support Modal -->
    <div id="contactSupportModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Hubungi Support
                </h3>
                <button type="button" class="modal-close" onclick="closeContactSupportModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="contact-support-intro">
                    <div class="support-icon-wrapper">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <h4>Ada yang bisa kami bantu?</h4>
                    <p>Tim support kami siap membantu Anda. Kirimkan pesan dan kami akan merespons secepat mungkin.</p>
                    <div style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 8px; padding: 14px; margin-top: 16px; display: flex; align-items: start; gap: 12px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="min-width: 22px; margin-top: 2px;">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <div style="flex: 1;">
                            <p style="margin: 0; font-size: 13px; color: white; line-height: 1.6;">
                                <strong style="display: block; margin-bottom: 4px;">Pesan Anda akan dikirim ke:</strong>
                                <a href="mailto:retroloved.ofc@gmail.com" style="color: white; text-decoration: none; font-weight: 600; opacity: 0.95;">retroloved.ofc@gmail.com</a>
                            </p>
                        </div>
                    </div>
                </div>

                <form id="contactSupportForm">
                    <!-- Hidden fields for logged-in users -->
                    <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'customer'): ?>
                        <?php
                        $user_id = $_SESSION['user_id'];
                        $user_query = query("SELECT full_name, email FROM users WHERE user_id = '$user_id'");
                        $user = mysqli_fetch_assoc($user_query);
                        ?>
                        <div style="display: none;">
                            <span data-user-name="<?php echo htmlspecialchars($user['full_name']); ?>"></span>
                            <span data-user-email="<?php echo htmlspecialchars($user['email']); ?>"></span>
                        </div>
                    <?php endif; ?>

                    <div class="support-form-group">
                        <label for="supportName">
                            Nama Lengkap <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="supportName" 
                            name="name" 
                            placeholder="Masukkan nama lengkap Anda"
                            required
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'customer'): ?>
                                value="<?php echo htmlspecialchars($user['full_name']); ?>"
                            <?php endif; ?>
                        >
                    </div>

                    <div class="support-form-group">
                        <label for="supportEmail">
                            Email <span class="required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="supportEmail" 
                            name="email" 
                            placeholder="email@example.com"
                            required
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'customer'): ?>
                                value="<?php echo htmlspecialchars($user['email']); ?>"
                            <?php endif; ?>
                        >
                        <div class="form-hint">Kami akan menghubungi Anda melalui email ini</div>
                    </div>

                    <div class="support-form-group">
                        <label for="supportSubject">
                            Subjek <span class="required">*</span>
                        </label>
                        <select id="supportSubject" name="subject" required>
                            <option value="">Pilih subjek...</option>
                            <option value="Pertanyaan Produk">Pertanyaan Produk</option>
                            <option value="Pertanyaan Pesanan">Pertanyaan Pesanan</option>
                            <option value="Masalah Pembayaran">Masalah Pembayaran</option>
                            <option value="Masalah Pengiriman">Masalah Pengiriman</option>
                            <option value="Pengembalian/Refund">Pengembalian/Refund</option>
                            <option value="Masalah Akun">Masalah Akun</option>
                            <option value="Saran & Feedback">Saran & Feedback</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="support-form-group">
                        <label for="supportMessage">
                            Pesan <span class="required">*</span>
                        </label>
                        <textarea 
                            id="supportMessage" 
                            name="message" 
                            placeholder="Jelaskan pertanyaan atau masalah Anda secara detail..."
                            required
                            minlength="10"
                        ></textarea>
                        <div class="form-hint">Minimal 10 karakter</div>
                    </div>

                    <div class="support-form-actions">
                        <button type="button" class="btn-cancel" onclick="closeContactSupportModal()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            Batal
                        </button>
                        <button type="submit" class="btn-submit">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                            Kirim Pesan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo isset($base_url) ? $base_url : '../'; ?>assets/js/toast.js?v=1.3"></script>
    <script src="<?php echo isset($base_url) ? $base_url : '../'; ?>assets/js/modal.js?v=1.3"></script>
    <script src="<?php echo isset($base_url) ? $base_url : '../'; ?>assets/js/auth-modal.js?v=1.3"></script>
    <script src="<?php echo isset($base_url) ? $base_url : '../'; ?>assets/js/contact-support.js?v=1.3"></script>
    <script src="<?php echo isset($base_url) ? $base_url : '../'; ?>assets/js/script.js?v=1.3"></script>
    
    <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'customer'): ?>
    <!-- Auto-logout untuk customer yang diblokir -->
    <script src="<?php echo isset($base_url) ? $base_url : '../'; ?>assets/js/block-check.js?v=1.0"></script>
    <?php endif; ?>

    <!-- Floating Contact Support Button -->
    <?php
    // Daftar halaman yang TIDAK menampilkan floating contact button
    $hide_floating_contact = [
        'orders.php',
        'profile.php', 
        'cart.php',
        'notifications.php'
    ];
    
    // Cek apakah halaman saat ini ada di daftar hide
    $current_page = basename($_SERVER['PHP_SELF']);
    $show_floating_contact = !in_array($current_page, $hide_floating_contact);
    
    // Tampilkan floating button jika tidak di daftar hide
    if ($show_floating_contact):
    ?>
    <button id="floatingContactBtn" class="floating-contact-btn" onclick="showContactSupportModal()" aria-label="Hubungi Support">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            <path d="M9 10h.01"></path>
            <path d="M15 10h.01"></path>
            <path d="M9.5 15a3.5 3.5 0 0 0 5 0"></path>
        </svg>
        <span class="floating-contact-tooltip">Butuh Bantuan?</span>
    </button>
    <?php endif; ?>
</body>
</html>