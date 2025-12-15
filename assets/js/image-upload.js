/**
 * Image Upload Handler dengan Compression dan Loading Spinner
 * Mengkompress gambar besar sebelum upload untuk menghemat bandwidth dan storage
 * Menampilkan loading spinner saat proses upload berlangsung
 */

/**
 * Kompress gambar menggunakan canvas
 * @param {File} file - File gambar yang akan dikompress
 * @param {number} maxWidth - Lebar maksimal gambar (default: 1200px)
 * @param {number} maxHeight - Tinggi maksimal gambar (default: 1200px)
 * @param {number} quality - Kualitas kompresi 0-1 (default: 0.8)
 * @returns {Promise<Blob>} - Promise yang resolve dengan blob gambar terkompress
 */
function compressImage(file, maxWidth = 1200, maxHeight = 1200, quality = 0.8) {
    return new Promise((resolve, reject) => {
        // Validasi tipe file
        if (!file.type.match(/image.*/)) {
            reject(new Error('File bukan gambar'));
            return;
        }

        const reader = new FileReader();
        
        reader.onload = function(e) {
            const img = new Image();
            
            img.onload = function() {
                // Hitung dimensi baru dengan mempertahankan aspect ratio
                let width = img.width;
                let height = img.height;
                
                // Resize jika gambar lebih besar dari maxWidth atau maxHeight
                if (width > maxWidth || height > maxHeight) {
                    const ratio = Math.min(maxWidth / width, maxHeight / height);
                    width = width * ratio;
                    height = height * ratio;
                }
                
                // Buat canvas untuk kompresi
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                
                const ctx = canvas.getContext('2d');
                
                // Gambar image ke canvas dengan dimensi baru
                ctx.drawImage(img, 0, 0, width, height);
                
                // Convert canvas ke blob dengan kompresi
                canvas.toBlob(
                    (blob) => {
                        if (blob) {
                            resolve(blob);
                        } else {
                            reject(new Error('Gagal mengkompress gambar'));
                        }
                    },
                    file.type,
                    quality
                );
            };
            
            img.onerror = function() {
                reject(new Error('Gagal memuat gambar'));
            };
            
            img.src = e.target.result;
        };
        
        reader.onerror = function() {
            reject(new Error('Gagal membaca file'));
        };
        
        reader.readAsDataURL(file);
    });
}

/**
 * Tampilkan loading spinner
 * @param {string} message - Pesan yang ditampilkan di loading (default: 'Uploading...')
 * @returns {HTMLElement} - Element loading overlay
 */
function showLoadingSpinner(message = 'Uploading...') {
    // Hapus loading yang sudah ada (jika ada)
    hideLoadingSpinner();
    
    // Buat loading overlay
    const overlay = document.createElement('div');
    overlay.id = 'upload-loading-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(4px);
    `;
    
    // Buat spinner
    const spinner = document.createElement('div');
    spinner.style.cssText = `
        width: 60px;
        height: 60px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #D97706;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    `;
    
    // Buat text message
    const text = document.createElement('p');
    text.textContent = message;
    text.style.cssText = `
        color: white;
        margin-top: 20px;
        font-size: 16px;
        font-weight: 500;
    `;
    
    overlay.appendChild(spinner);
    overlay.appendChild(text);
    
    // Tambahkan CSS animation untuk spinner
    if (!document.getElementById('spinner-keyframes')) {
        const imageUploadStyle = document.createElement('style');
        imageUploadStyle.id = 'spinner-keyframes';
        imageUploadStyle.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(imageUploadStyle);
    }
    
    document.body.appendChild(overlay);
    return overlay;
}

/**
 * Sembunyikan loading spinner
 */
function hideLoadingSpinner() {
    const overlay = document.getElementById('upload-loading-overlay');
    if (overlay) {
        overlay.remove();
    }
}

/**
 * Handle file input change dengan compression
 * @param {HTMLInputElement} input - Input file element
 * @param {Function} callback - Callback function yang dipanggil setelah kompresi selesai
 * @param {Object} options - Opsi kompresi {maxWidth, maxHeight, quality}
 */
async function handleImageUpload(input, callback, options = {}) {
    const {
        maxWidth = 1200,
        maxHeight = 1200,
        quality = 0.8,
        maxSize = 5 * 1024 * 1024, // 5MB
        showSpinner = true
    } = options;
    
    const files = input.files;
    
    if (!files || files.length === 0) {
        return;
    }
    
    // Validasi ukuran file
    for (let file of files) {
        if (file.size > maxSize) {
            toastError(`File ${file.name} terlalu besar! Maksimal 5MB`);
            input.value = ''; // Reset input
            return;
        }
    }
    
    if (showSpinner) {
        showLoadingSpinner('Memproses gambar...');
    }
    
    try {
        const compressedFiles = [];
        
        // Kompress setiap file
        for (let file of files) {
            try {
                const compressedBlob = await compressImage(file, maxWidth, maxHeight, quality);
                
                // Convert blob ke file dengan nama yang sama
                const compressedFile = new File([compressedBlob], file.name, {
                    type: file.type,
                    lastModified: Date.now()
                });
                
                compressedFiles.push(compressedFile);
                
                // Log info kompresi
                const originalSize = (file.size / 1024).toFixed(2);
                const compressedSize = (compressedBlob.size / 1024).toFixed(2);
                const reduction = ((1 - compressedBlob.size / file.size) * 100).toFixed(1);
                
                // Kompresi berhasil: ${file.name} dari ${originalSize}KB menjadi ${compressedSize}KB (${reduction}% reduksi)
                
            } catch (error) {
                // Error saat kompresi ${file.name}
                // Jika gagal compress, gunakan file original
                compressedFiles.push(file);
            }
        }
        
        // Panggil callback dengan files yang sudah dikompress
        if (callback) {
            callback(compressedFiles);
        }
        
        if (showSpinner) {
            hideLoadingSpinner();
        }
        
    } catch (error) {
        // Error saat menangani upload gambar
        toastError('Gagal memproses gambar');
        
        if (showSpinner) {
            hideLoadingSpinner();
        }
    }
}

/**
 * Setup auto-compression pada file input
 * Otomatis compress gambar saat user memilih file
 * @param {string} selector - CSS selector untuk input file
 * @param {Object} options - Opsi kompresi
 */
function setupImageCompression(selector, options = {}) {
    const inputs = document.querySelectorAll(selector);
    
    inputs.forEach(input => {
        input.addEventListener('change', async function() {
            await handleImageUpload(this, null, options);
        });
    });
}

// Export functions untuk digunakan di file lain
window.compressImage = compressImage;
window.showLoadingSpinner = showLoadingSpinner;
window.hideLoadingSpinner = hideLoadingSpinner;
window.handleImageUpload = handleImageUpload;
window.setupImageCompression = setupImageCompression;

/**
 * Preview gambar sebelum upload
 * @param {File} file - File gambar
 * @param {HTMLElement} previewElement - Element untuk preview
 */
function previewImage(file, previewElement) {
    if (file && file.type.match(/image.*/)) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (previewElement.tagName === 'IMG') {
                previewElement.src = e.target.result;
            } else {
                previewElement.style.backgroundImage = `url(${e.target.result})`;
                previewElement.style.backgroundSize = 'cover';
                previewElement.style.backgroundPosition = 'center';
            }
        };
        
        reader.readAsDataURL(file);
    }
}

window.previewImage = previewImage;

// Image Upload Handler berhasil dimuat
