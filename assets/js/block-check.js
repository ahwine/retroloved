/**
 * Block Check - Auto Logout untuk User yang Diblokir
 * Memeriksa status akun secara berkala dan logout otomatis jika diblokir
 * RetroLoved E-Commerce System
 */

(function() {
    'use strict';
    
    // Hanya jalankan untuk customer yang sudah login
    if (!window.location.pathname.includes('/customer/')) {
        return;
    }
    
    // Fungsi untuk cek status akun
    function checkAccountStatus() {
        fetch('../auth/check-account-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'check_status'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.blocked) {
                // Akun diblokir, logout otomatis
                if (typeof toastError === 'function') {
                    toastError('Akun Anda telah diblokir oleh admin. Anda akan logout otomatis.');
                }
                
                // Tunggu 2 detik lalu logout
                setTimeout(function() {
                    window.location.href = '../auth/logout.php';
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error checking account status:', error);
        });
    }
    
    // Cek status saat halaman dimuat
    checkAccountStatus();
    
    // Cek status setiap 30 detik
    setInterval(checkAccountStatus, 30000);
    
})();
