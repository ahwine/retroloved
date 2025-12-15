/**
 * File JavaScript Utama
 * Fungsi-fungsi global dan utilities untuk website
 * RetroLoved E-Commerce System
 */

/**
 * Smooth scrolling untuk link anchor
 * Membuat navigasi ke section tertentu dengan efek smooth
 */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        
        // Skip if href is just "#" or not a valid selector
        if (href === '#' || href.length <= 1) {
            e.preventDefault();
            return;
        }
        
        try {
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        } catch (error) {
            // Invalid selector, let the link work normally
            console.log('Invalid selector for smooth scroll:', href);
        }
    });
});

/**
 * Animasi feedback saat menambahkan produk ke keranjang
 * Memberikan feedback visual kepada pengguna bahwa produk berhasil ditambahkan
 */
document.querySelectorAll('button[name="add_to_cart"]').forEach(button => {
    button.addEventListener('click', function() {
        this.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;"><polyline points="20 6 9 17 4 12"></polyline></svg> Ditambahkan!';
        this.style.backgroundColor = '#10B981';
        setTimeout(() => {
            this.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg> Tambah ke Cart';
            this.style.backgroundColor = '';
        }, 2000);
    });
});

/**
 * Validasi form dengan feedback visual
 * Validasi field required sebelum form disubmit
 * Menampilkan border merah pada field yang kosong
 */
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const requiredFields = this.querySelectorAll('[required]');
        let isValid = true;
        
        // Cek setiap field yang wajib diisi
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.style.borderColor = '#EF4444'; // Border merah untuk field kosong
            } else {
                field.style.borderColor = ''; // Reset border
            }
        });
        
        // Jika ada field kosong, cegah submit dan tampilkan notifikasi error
        if (!isValid) {
            e.preventDefault();
            toastError('Mohon lengkapi semua field yang wajib diisi!');
        }
    });
});

/**
 * Penanganan error gambar dengan fallback otomatis
 * Jika gambar gagal dimuat, akan mencoba menggunakan placeholder
 * Jika placeholder juga gagal, gunakan SVG placeholder inline
 */
document.querySelectorAll('img').forEach(img => {
    let errorAttempts = 0;
    
    img.addEventListener('error', function() {
        if (errorAttempts === 0) {
            // Coba gunakan gambar placeholder terlebih dahulu
            errorAttempts++;
            const pathPrefix = this.src.includes('assets/images/products') ? '' : '../';
            this.src = pathPrefix + 'assets/images/products/placeholder.jpg';
        } else if (errorAttempts === 1) {
            // Fallback terakhir: gunakan SVG inline sebagai placeholder
            errorAttempts++;
            this.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="400"%3E%3Crect fill="%23f3f4f6" width="400" height="400"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dominant-baseline="middle" font-family="system-ui, -apple-system, sans-serif" font-size="20" fill="%239ca3af"%3ENo Image%3C/text%3E%3C/svg%3E';
        }
    });
});

/**
 * Auto-hide alert setelah 5 detik
 * Alert akan fade out dan kemudian dihapus dari DOM
 */
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    }, 5000);
});

/**
 * Validasi input angka
 * Memastikan nilai tidak kurang dari minimum dan tidak lebih dari maksimum
 */
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('change', function() {
        const min = parseInt(this.min) || 1;
        const max = parseInt(this.max) || 999;
        let value = parseInt(this.value);
        
        if (value < min) this.value = min;
        if (value > max) this.value = max;
    });
});

/**
 * Efek hover untuk kartu produk
 * Memberikan efek terangkat saat di-hover
 */
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-8px)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});

/**
 * Toggle dropdown menu profil
 * Menampilkan/menyembunyikan menu profil pengguna
 */
function toggleProfileDropdown() {
    const wrapper = document.querySelector('.user-profile-wrapper');
    wrapper.classList.toggle('active');
}

/**
 * Tutup dropdown saat klik di luar area dropdown
 * Meningkatkan pengalaman pengguna dengan menutup dropdown otomatis
 */
document.addEventListener('click', function(event) {
    const wrapper = document.querySelector('.user-profile-wrapper');
    if (wrapper && !wrapper.contains(event.target)) {
        wrapper.classList.remove('active');
    }
});

/**
 * Toggle menu sidebar mobile
 * Menampilkan/menyembunyikan menu mobile di layar kecil
 * Mencegah scroll halaman saat menu terbuka
 */
function toggleMobileMenu() {
    const mobileSidebar = document.getElementById('mobileSidebar');
    const menuIcon = document.querySelector('.mobile-menu-icon');
    const body = document.body;
    
    if (mobileSidebar && menuIcon) {
        mobileSidebar.classList.toggle('active');
        menuIcon.classList.toggle('hidden');
        
        // Cegah scroll body saat menu mobile terbuka
        if (mobileSidebar.classList.contains('active')) {
            body.style.overflow = 'hidden';
        } else {
            body.style.overflow = '';
        }
    }
}

/**
 * Tutup menu mobile saat klik di luar area menu
 * Menutup menu otomatis saat pengguna klik area lain (overlay)
 */
document.addEventListener('click', function(event) {
    const mobileSidebar = document.getElementById('mobileSidebar');
    const menuIcon = document.querySelector('.mobile-menu-icon');
    
    // Tutup menu jika klik di luar menu dan ikon
    if (mobileSidebar && menuIcon && !mobileSidebar.contains(event.target) && !menuIcon.contains(event.target)) {
        mobileSidebar.classList.remove('active');
        menuIcon.classList.remove('hidden');
        document.body.style.overflow = '';
    }
});

/**
 * Tutup menu mobile otomatis saat klik link navigasi
 * Meningkatkan pengalaman pengguna dengan menutup menu setelah navigasi
 */
document.querySelectorAll('.mobile-nav-link').forEach(link => {
    link.addEventListener('click', function() {
        const mobileSidebar = document.getElementById('mobileSidebar');
        const menuIcon = document.querySelector('.mobile-menu-icon');
        if (mobileSidebar && menuIcon) {
            mobileSidebar.classList.remove('active');
            menuIcon.classList.remove('hidden');
            document.body.style.overflow = '';
        }
    });
});