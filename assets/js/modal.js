// ===== SISTEM MODAL KUSTOM =====

// Prevent double loading
if (typeof window.confirmModal !== 'undefined') {
    console.log('modal.js already loaded, skipping...');
} else {

/**
 * Modal Konfirmasi Kustom
 * Menampilkan dialog konfirmasi yang lebih menarik daripada confirm() bawaan browser
 * @param {string} message - Pesan yang akan ditampilkan
 * @param {function} onConfirm - Callback saat tombol konfirmasi diklik
 * @param {function} onCancel - Callback saat tombol batal diklik (optional)
 * @param {object} options - Opsi kustomisasi (optional)
 *   - title: string - Judul modal (default: "Konfirmasi")
 *   - confirmText: string - Text tombol konfirmasi (default: "Ya, Hapus")
 *   - cancelText: string - Text tombol batal (default: "Batal")
 *   - confirmColor: string - Warna tombol konfirmasi (default: "#DC2626")
 *   - iconType: string - Tipe icon: "warning", "danger", "info", "success" (default: "warning")
 */
window.confirmModal = function confirmModal(message, onConfirm, onCancel, options) {
    // Default options
    const defaultOptions = {
        title: 'Konfirmasi',
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal',
        confirmColor: '#DC2626',
        confirmColorHover: '#B91C1C',
        iconType: 'warning'
    };
    
    // Merge with provided options
    const opts = { ...defaultOptions, ...(options || {}) };
    // Pastikan body sudah ready
    if (!document.body) {
        console.error('confirmModal: document.body not ready yet!');
        return;
    }
    
    // Buat overlay
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.65);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    // Buat modal
    const modal = document.createElement('div');
    modal.className = 'custom-modal';
    modal.style.cssText = `
        background: white;
        border-radius: 16px;
        padding: 32px;
        max-width: 420px;
        width: 90%;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transform: scale(0.9);
        opacity: 0;
        transition: all 0.3s ease;
    `;
    
    // Determine icon and colors based on type
    let iconSvg = '';
    let iconBgColor = '';
    let iconColor = '';
    
    switch(opts.iconType) {
        case 'danger':
        case 'warning':
            iconBgColor = '#FEF3C7';
            iconColor = '#D97706';
            iconSvg = `
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="${iconColor}" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            `;
            break;
        case 'info':
            iconBgColor = '#DBEAFE';
            iconColor = '#2563EB';
            iconSvg = `
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="${iconColor}" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
            `;
            break;
        case 'success':
            iconBgColor = '#D1FAE5';
            iconColor = '#10B981';
            iconSvg = `
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="${iconColor}" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="9 11 12 14 16 10"></polyline>
                </svg>
            `;
            break;
        default:
            iconBgColor = '#FEF3C7';
            iconColor = '#D97706';
            iconSvg = `
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="${iconColor}" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            `;
    }
    
    modal.innerHTML = `
        <div style="text-align: center;">
            <div style="width: 64px; height: 64px; margin: 0 auto 20px; background: ${iconBgColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                ${iconSvg}
            </div>
            <h3 style="font-size: 20px; font-weight: 700; color: #1F2937; margin-bottom: 12px;">${opts.title}</h3>
            <p style="font-size: 15px; color: #6B7280; line-height: 1.6; margin-bottom: 28px;">${message}</p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button class="modal-btn-cancel" style="flex: 1; padding: 12px 24px; border: 2px solid #E5E7EB; background: white; color: #374151; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s; font-family: inherit;">
                    ${opts.cancelText}
                </button>
                <button class="modal-btn-confirm" style="flex: 1; padding: 12px 24px; border: none; background: ${opts.confirmColor}; color: white; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s; font-family: inherit;">
                    ${opts.confirmText}
                </button>
            </div>
        </div>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Jalankan animasi
    setTimeout(() => {
        overlay.style.opacity = '1';
        modal.style.opacity = '1';
        modal.style.transform = 'scale(1)';
    }, 10);
    
    // Fungsi untuk menutup modal
    function closeModal() {
        overlay.style.opacity = '0';
        modal.style.transform = 'scale(0.9)';
        setTimeout(() => {
            document.body.removeChild(overlay);
        }, 300);
    }
    
    // Event listeners untuk tombol
    const btnCancel = modal.querySelector('.modal-btn-cancel');
    const btnConfirm = modal.querySelector('.modal-btn-confirm');
    
    btnCancel.addEventListener('mouseover', function() {
        this.style.background = '#F3F4F6';
        this.style.borderColor = '#D1D5DB';
    });
    btnCancel.addEventListener('mouseout', function() {
        this.style.background = 'white';
        this.style.borderColor = '#E5E7EB';
    });
    
    btnConfirm.addEventListener('mouseover', function() {
        this.style.background = opts.confirmColorHover;
        this.style.transform = 'translateY(-2px)';
    });
    btnConfirm.addEventListener('mouseout', function() {
        this.style.background = opts.confirmColor;
        this.style.transform = 'translateY(0)';
    });
    
    btnCancel.addEventListener('click', () => {
        closeModal();
        if (onCancel) onCancel();
    });
    
    btnConfirm.addEventListener('click', () => {
        closeModal();
        if (onConfirm) onConfirm();
    });
    
    // Tutup modal saat klik overlay
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeModal();
            if (onCancel) onCancel();
        }
    });
    
    // Tutup modal saat tekan tombol ESC
    const escHandler = (e) => {
        if (e.key === 'Escape') {
            closeModal();
            if (onCancel) onCancel();
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
};

} // End of guard check
