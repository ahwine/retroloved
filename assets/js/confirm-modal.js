/**
 * Modal Konfirmasi Custom - RetroLoved
 * Menggantikan confirm() bawaan browser dengan UI yang lebih baik
 */

// Buat modal confirmation saat halaman load
document.addEventListener('DOMContentLoaded', function() {
    createConfirmModal();
});

// Fungsi untuk membuat modal confirmation
function createConfirmModal() {
    // Cek apakah modal sudah ada
    if (document.getElementById('customConfirmModal')) {
        return;
    }

    const modalHTML = `
        <div id="customConfirmModal" class="custom-confirm-modal" style="display: none;">
            <div class="custom-confirm-overlay"></div>
            <div class="custom-confirm-content">
                <div class="custom-confirm-header">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2" class="custom-confirm-icon">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <h3 class="custom-confirm-title" id="customConfirmTitle">Konfirmasi</h3>
                <p class="custom-confirm-message" id="customConfirmMessage"></p>
                <div class="custom-confirm-actions">
                    <button type="button" class="custom-confirm-btn custom-confirm-btn-cancel" id="customConfirmCancel">
                        Batal
                    </button>
                    <button type="button" class="custom-confirm-btn custom-confirm-btn-confirm" id="customConfirmOk">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Tambahkan CSS
    const style = document.createElement('style');
    style.textContent = `
        .custom-confirm-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.2s ease-out;
        }

        .custom-confirm-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .custom-confirm-content {
            position: relative;
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 440px;
            width: 90%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            animation: slideUp 0.3s ease-out;
            text-align: center;
        }

        .custom-confirm-header {
            margin-bottom: 20px;
        }

        .custom-confirm-icon {
            margin: 0 auto;
            display: block;
        }

        .custom-confirm-title {
            font-size: 20px;
            font-weight: 700;
            color: #1F2937;
            margin: 0 0 12px 0;
        }

        .custom-confirm-message {
            font-size: 15px;
            color: #6B7280;
            line-height: 1.6;
            margin: 0 0 24px 0;
        }

        .custom-confirm-actions {
            display: flex;
            gap: 12px;
        }

        .custom-confirm-btn {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .custom-confirm-btn-cancel {
            background: white;
            color: #6B7280;
            border: 1.5px solid #E5E7EB;
        }

        .custom-confirm-btn-cancel:hover {
            background: #F9FAFB;
            border-color: #D1D5DB;
        }

        .custom-confirm-btn-confirm {
            background: #D97706;
            color: white;
        }

        .custom-confirm-btn-confirm:hover {
            background: #B45309;
        }

        .custom-confirm-btn-confirm.danger {
            background: #DC2626;
        }

        .custom-confirm-btn-confirm.danger:hover {
            background: #B91C1C;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 640px) {
            .custom-confirm-content {
                padding: 24px;
                max-width: 95%;
            }

            .custom-confirm-title {
                font-size: 18px;
            }

            .custom-confirm-message {
                font-size: 14px;
            }

            .custom-confirm-actions {
                flex-direction: column-reverse;
            }

            .custom-confirm-btn {
                width: 100%;
            }
        }
    `;
    document.head.appendChild(style);
}

/**
 * Tampilkan modal konfirmasi custom
 * @param {string} message - Pesan konfirmasi
 * @param {function} onConfirm - Callback saat user klik "Ya"
 * @param {object} options - Opsi tambahan (title, confirmText, cancelText, isDanger)
 */
function showConfirm(message, onConfirm, options = {}) {
    const modal = document.getElementById('customConfirmModal');
    const title = document.getElementById('customConfirmTitle');
    const messageEl = document.getElementById('customConfirmMessage');
    const cancelBtn = document.getElementById('customConfirmCancel');
    const confirmBtn = document.getElementById('customConfirmOk');

    if (!modal) {
        createConfirmModal();
        // Retry setelah modal dibuat
        setTimeout(() => showConfirm(message, onConfirm, options), 100);
        return;
    }

    // Set content
    title.textContent = options.title || 'Konfirmasi';
    messageEl.textContent = message;
    cancelBtn.textContent = options.cancelText || 'Batal';
    confirmBtn.textContent = options.confirmText || 'Ya, Lanjutkan';

    // Set danger style jika diperlukan
    if (options.isDanger) {
        confirmBtn.classList.add('danger');
    } else {
        confirmBtn.classList.remove('danger');
    }

    // Show modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Remove old event listeners
    const newCancelBtn = cancelBtn.cloneNode(true);
    const newConfirmBtn = confirmBtn.cloneNode(true);
    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

    // Add new event listeners
    newCancelBtn.addEventListener('click', function() {
        closeConfirm();
    });

    newConfirmBtn.addEventListener('click', function() {
        closeConfirm();
        if (typeof onConfirm === 'function') {
            onConfirm();
        }
    });

    // Close on overlay click
    const overlay = modal.querySelector('.custom-confirm-overlay');
    overlay.addEventListener('click', function() {
        closeConfirm();
    });

    // Close on ESC key
    function handleEscape(e) {
        if (e.key === 'Escape') {
            closeConfirm();
            document.removeEventListener('keydown', handleEscape);
        }
    }
    document.addEventListener('keydown', handleEscape);
}

/**
 * Tutup modal konfirmasi
 */
function closeConfirm() {
    const modal = document.getElementById('customConfirmModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

/**
 * Wrapper untuk kompatibilitas dengan kode lama
 * Menggantikan confirm() bawaan browser
 */
function customConfirm(message, onConfirm, options = {}) {
    showConfirm(message, onConfirm, options);
}

// Export untuk digunakan di file lain
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { showConfirm, closeConfirm, customConfirm };
}
