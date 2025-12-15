/**
 * Product Detail Page - Image Gallery & Lightbox
 * Handles image gallery navigation and lightbox functionality
 */

let allImages = [];
let currentLightboxIndex = 0;
let totalImages = 0;

/**
 * Initialize the gallery with images
 * @param {Array} images - Array of image URLs
 * @param {Number} total - Total number of images
 */
function initializeGallery(images, total) {
    allImages = images;
    totalImages = total;
    
    // Update total images counter
    const totalImagesEl = document.getElementById('totalImages');
    if (totalImagesEl) {
        totalImagesEl.textContent = totalImages;
    }
    
    // Set up event listeners
    setupEventListeners();
}

/**
 * Setup all event listeners for gallery and lightbox
 */
function setupEventListeners() {
    // Keyboard navigation
    document.addEventListener('keydown', handleKeyboardNavigation);
    
    // Lightbox background click to close
    const lightboxModal = document.getElementById('lightboxModal');
    if (lightboxModal) {
        lightboxModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });
    }
}

/**
 * Handle keyboard navigation for gallery and lightbox
 * @param {KeyboardEvent} e - The keyboard event
 */
function handleKeyboardNavigation(e) {
    const modal = document.getElementById('lightboxModal');
    
    if (modal && modal.classList.contains('active')) {
        // Lightbox is open
        if (e.key === 'Escape') {
            closeLightbox();
        } else if (e.key === 'ArrowLeft') {
            lightboxPrev();
        } else if (e.key === 'ArrowRight') {
            lightboxNext();
        }
    } else {
        // Regular gallery navigation
        const currentImageNumberEl = document.getElementById('currentImageNumber');
        if (!currentImageNumberEl) return;
        
        const currentIndex = parseInt(currentImageNumberEl.textContent) - 1;
        
        if (e.key === 'ArrowLeft' && currentIndex > 0) {
            const thumbnails = document.querySelectorAll('.thumbnail');
            if (thumbnails[currentIndex - 1]) {
                thumbnails[currentIndex - 1].click();
            }
        } else if (e.key === 'ArrowRight' && currentIndex < totalImages - 1) {
            const thumbnails = document.querySelectorAll('.thumbnail');
            if (thumbnails[currentIndex + 1]) {
                thumbnails[currentIndex + 1].click();
            }
        }
    }
}

/**
 * Change the main image and update UI
 * @param {String} imageUrl - The image URL to display
 * @param {Number} index - The index of the image
 */
function changeImage(imageUrl, index) {
    // Update main image
    const mainImage = document.getElementById('mainImage');
    if (mainImage) {
        mainImage.src = '../assets/images/products/' + imageUrl;
    }
    
    // Update image counter
    const currentImageNumberEl = document.getElementById('currentImageNumber');
    if (currentImageNumberEl) {
        currentImageNumberEl.textContent = index + 1;
    }
    
    // Update current lightbox index
    currentLightboxIndex = index;
    
    // Update active thumbnail
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach((thumb, i) => {
        if (i === index) {
            thumb.classList.add('active');
        } else {
            thumb.classList.remove('active');
        }
    });
}

/**
 * Open the lightbox modal
 */
function openLightbox() {
    const modal = document.getElementById('lightboxModal');
    const lightboxImage = document.getElementById('lightboxImage');
    const mainImage = document.getElementById('mainImage');
    
    if (!modal || !lightboxImage || !mainImage) {
        console.error('Lightbox elements not found');
        return;
    }
    
    // Set lightbox image to current main image
    lightboxImage.src = mainImage.src;
    
    // Show modal with smooth transition
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);
    
    document.body.style.overflow = 'hidden';
    
    // Update counter and navigation buttons
    updateLightboxCounter();
    updateNavigationButtons();
}

/**
 * Close the lightbox modal
 */
function closeLightbox() {
    const modal = document.getElementById('lightboxModal');
    if (!modal) return;
    
    modal.classList.remove('active');
    
    // Wait for transition to complete before hiding
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
    
    document.body.style.overflow = 'auto';
}

/**
 * Navigate to previous image in lightbox
 */
function lightboxPrev() {
    if (currentLightboxIndex > 0) {
        currentLightboxIndex--;
        updateLightboxImage();
        updateNavigationButtons();
    }
}

/**
 * Navigate to next image in lightbox
 */
function lightboxNext() {
    if (currentLightboxIndex < totalImages - 1) {
        currentLightboxIndex++;
        updateLightboxImage();
        updateNavigationButtons();
    }
}

/**
 * Update the lightbox image source
 */
function updateLightboxImage() {
    const lightboxImage = document.getElementById('lightboxImage');
    if (!lightboxImage || !allImages[currentLightboxIndex]) return;
    
    lightboxImage.src = '../assets/images/products/' + allImages[currentLightboxIndex];
    updateLightboxCounter();
}

/**
 * Update the lightbox counter display
 */
function updateLightboxCounter() {
    const counter = document.getElementById('lightboxCounter');
    if (counter) {
        counter.textContent = (currentLightboxIndex + 1) + ' / ' + totalImages;
    }
}

/**
 * Update navigation buttons state (enable/disable)
 */
function updateNavigationButtons() {
    const prevBtn = document.querySelector('.lightbox-nav.prev');
    const nextBtn = document.querySelector('.lightbox-nav.next');
    
    if (prevBtn) {
        prevBtn.disabled = currentLightboxIndex === 0;
    }
    
    if (nextBtn) {
        nextBtn.disabled = currentLightboxIndex === totalImages - 1;
    }
}

// Export functions to global scope
window.initializeGallery = initializeGallery;
window.changeImage = changeImage;
window.openLightbox = openLightbox;
window.closeLightbox = closeLightbox;
window.lightboxPrev = lightboxPrev;
window.lightboxNext = lightboxNext;