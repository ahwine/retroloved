/**
 * Sistem Notifikasi Toast
 * Sistem notifikasi modern yang tidak memblokir UI seperti alert()
 * Digunakan untuk memberikan feedback visual kepada pengguna
 * RetroLoved E-Commerce System
 */

// Prevent double loading
if (typeof window.Toast !== 'undefined') {
    console.log('⚠️ toast.js already loaded, skipping...');
} else {
    console.log('✅ Toast.js Loading - Version 1.3');

class ToastNotification {
    constructor() {
        this.container = null;
        this.init();
    }
    
    /**
     * Inisialisasi container toast
     * Membuat container untuk menampung semua notifikasi toast
     */
    init() {
        // Tunggu sampai body ready
        if (!document.body) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.init());
                return;
            }
        }
        
        // Buat container jika belum ada
        if (!document.getElementById('toast-container')) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('toast-container');
        }
    }
    
    /**
     * Tampilkan notifikasi toast
     * @param {string} message - Pesan yang akan ditampilkan
     * @param {string} type - Tipe toast (success, error, warning, info)
     * @param {number} duration - Durasi tampil dalam milidetik (0 = tidak otomatis tutup)
     * @param {string} title - Judul toast (opsional)
     * @returns {HTMLElement} - Element toast yang dibuat
     */
    show(message, type = 'info', duration = 3000, title = '') {
        // Pastikan container sudah diinisialisasi
        if (!this.container) {
            this.init();
        }
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        // Tentukan ikon dan judul default berdasarkan tipe toast
        let icon = '';
        let defaultTitle = '';
        
        switch(type) {
            case 'success':
                icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                defaultTitle = 'Berhasil!';
                break;
            case 'error':
                icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
                defaultTitle = 'Error!';
                break;
            case 'warning':
                icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
                defaultTitle = 'Peringatan!';
                break;
            case 'info':
                icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
                defaultTitle = 'Info';
                break;
        }
        
        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">
                ${title || defaultTitle ? `<p class="toast-title">${title || defaultTitle}</p>` : ''}
                <p class="toast-message">${message}</p>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            ${duration > 0 ? '<div class="toast-progress"></div>' : ''}
        `;
        
        this.container.appendChild(toast);
        
        // Hapus otomatis setelah durasi tertentu
        if (duration > 0) {
            setTimeout(() => {
                this.remove(toast);
            }, duration);
        }
        
        return toast;
    }
    
    /**
     * Hapus toast dengan animasi
     * @param {HTMLElement} toast - Element toast yang akan dihapus
     */
    remove(toast) {
        toast.classList.add('toast-removing');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    }
    
    /**
     * Tampilkan toast sukses (hijau)
     */
    success(message, title = '', duration = 3000) {
        return this.show(message, 'success', duration, title);
    }
    
    /**
     * Tampilkan toast error (merah) - durasi lebih lama karena penting
     */
    error(message, title = '', duration = 4000) {
        return this.show(message, 'error', duration, title);
    }
    
    /**
     * Tampilkan toast peringatan (kuning)
     */
    warning(message, title = '', duration = 3500) {
        return this.show(message, 'warning', duration, title);
    }
    
    /**
     * Tampilkan toast info (biru)
     */
    info(message, title = '', duration = 3000) {
        return this.show(message, 'info', duration, title);
    }
}

// Inisialisasi instance toast global agar bisa dipanggil dari mana saja
window.Toast = new ToastNotification();
console.log('✅ Toast.js initialized successfully - Ready to use!');

// Fungsi-fungsi helper untuk kemudahan penggunaan
// Bisa dipanggil langsung: toastSuccess('Berhasil!')

/**
 * Tampilkan toast dengan tipe kustom
 */
window.showToast = (message, type = 'info', duration = 3000, title = '') => {
    return window.Toast.show(message, type, duration, title);
};

/**
 * Shortcut untuk toast sukses
 * Contoh: toastSuccess('Data berhasil disimpan!')
 */
window.toastSuccess = (message, title = '') => {
    console.log('🎉 toastSuccess called:', message);
    return window.Toast.success(message, title);
};

/**
 * Shortcut untuk toast error
 * Contoh: toastError('Gagal menyimpan data!')
 */
window.toastError = (message, title = '') => {
    console.log('❌ toastError called:', message);
    return window.Toast.error(message, title);
};

/**
 * Shortcut untuk toast peringatan
 * Contoh: toastWarning('Periksa kembali data Anda!')
 */
window.toastWarning = (message, title = '') => {
    return window.Toast.warning(message, title);
};

/**
 * Shortcut untuk toast info
 * Contoh: toastInfo('Proses sedang berjalan...')
 */
window.toastInfo = (message, title = '') => {
    return window.Toast.info(message, title);
};

} // End of guard check
