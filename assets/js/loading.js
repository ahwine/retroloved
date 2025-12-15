// Loading Helper Functions for RetroLoved
// Step 8A: Loading States Implementation

/**
 * Show loading overlay with spinner
 */
function showLoadingOverlay() {
    let overlay = document.querySelector('.loading-overlay');
    
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="loading-spinner"></div>';
        document.body.appendChild(overlay);
    }
    
    setTimeout(() => {
        overlay.classList.add('active');
    }, 10);
}

/**
 * Hide loading overlay
 */
function hideLoadingOverlay() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
}

/**
 * Add loading state to button
 * @param {HTMLElement} button - Button element
 * @param {string} originalText - Original button text (optional)
 */
function setButtonLoading(button, originalText = null) {
    if (!button) return;
    
    if (originalText) {
        button.setAttribute('data-original-text', originalText);
    } else if (!button.hasAttribute('data-original-text')) {
        button.setAttribute('data-original-text', button.innerHTML);
    }
    
    button.classList.add('btn-loading');
    button.disabled = true;
}

/**
 * Remove loading state from button
 * @param {HTMLElement} button - Button element
 */
function removeButtonLoading(button) {
    if (!button) return;
    
    const originalText = button.getAttribute('data-original-text');
    if (originalText) {
        button.innerHTML = originalText;
    }
    
    button.classList.remove('btn-loading');
    button.disabled = false;
}

/**
 * Create skeleton loader for product grid
 * @param {number} count - Number of skeleton cards
 * @returns {string} HTML string
 */
function createProductGridSkeleton(count = 6) {
    let html = '<div class=\"skeleton-product-grid\">';
    
    for (let i = 0; i < count; i++) {
        html += `
            <div class="skeleton-product-card">
                <div class="skeleton-product-image"></div>
                <div class="skeleton-product-info">
                    <div class="skeleton skeleton-product-title"></div>
                    <div class="skeleton skeleton-product-price"></div>
                    <div class="skeleton skeleton-product-button"></div>
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    return html;
}

/**
 * Create skeleton loader for order list
 * @param {number} count - Number of skeleton cards
 * @returns {string} HTML string
 */
function createOrderListSkeleton(count = 3) {
    let html = '<div class=\"skeleton-order-list\">';
    
    for (let i = 0; i < count; i++) {
        html += `
            <div class="skeleton-order-card">
                <div class="skeleton-order-header">
                    <div class="skeleton skeleton-order-id"></div>
                    <div class="skeleton skeleton-order-status"></div>
                </div>
                <div class="skeleton-order-product">
                    <div class="skeleton skeleton-order-product-image"></div>
                    <div class="skeleton-order-product-info">
                        <div class="skeleton skeleton-order-product-name"></div>
                        <div class="skeleton skeleton-order-product-price"></div>
                    </div>
                </div>
                <div class="skeleton skeleton-order-total"></div>
            </div>
        `;
    }
    
    html += '</div>';
    return html;
}

/**
 * Create skeleton loader for notification list
 * @param {number} count - Number of skeleton items
 * @returns {string} HTML string
 */
function createNotificationSkeleton(count = 5) {
    let html = '<div class=\"skeleton-notification-list\">';
    
    for (let i = 0; i < count; i++) {
        html += `
            <div class="skeleton-notification-item">
                <div class="skeleton skeleton-notification-icon"></div>
                <div class="skeleton-notification-content">
                    <div class="skeleton skeleton-notification-title"></div>
                    <div class="skeleton skeleton-notification-message"></div>
                    <div class="skeleton skeleton-notification-message"></div>
                    <div class="skeleton skeleton-notification-time"></div>
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    return html;
}

/**
 * Create skeleton loader for profile page
 * @returns {string} HTML string
 */
function createProfileSkeleton() {
    return `
        <div class="skeleton-profile">
            <div class="skeleton-profile-header">
                <div class="skeleton skeleton-profile-avatar"></div>
                <div class="skeleton skeleton-profile-name"></div>
                <div class="skeleton skeleton-profile-email"></div>
            </div>
            
            <div class="skeleton-profile-tabs">
                <div class="skeleton skeleton-profile-tab"></div>
                <div class="skeleton skeleton-profile-tab"></div>
                <div class="skeleton skeleton-profile-tab"></div>
            </div>
            
            <div class="skeleton-profile-form">
                <div class="skeleton-form-field">
                    <div class="skeleton skeleton-form-label"></div>
                    <div class="skeleton skeleton-form-input"></div>
                </div>
                <div class="skeleton-form-field">
                    <div class="skeleton skeleton-form-label"></div>
                    <div class="skeleton skeleton-form-input"></div>
                </div>
                <div class="skeleton-form-field">
                    <div class="skeleton skeleton-form-label"></div>
                    <div class="skeleton skeleton-form-input"></div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Create skeleton loader for product detail
 * @returns {string} HTML string
 */
function createProductDetailSkeleton() {
    return `
        <div class="skeleton-product-detail">
            <div class="skeleton-product-detail-grid">
                <div>
                    <div class="skeleton skeleton-product-main-image"></div>
                    <div class="skeleton-product-thumbnails">
                        <div class="skeleton skeleton-product-thumbnail"></div>
                        <div class="skeleton skeleton-product-thumbnail"></div>
                        <div class="skeleton skeleton-product-thumbnail"></div>
                        <div class="skeleton skeleton-product-thumbnail"></div>
                    </div>
                </div>
                <div class="skeleton-product-detail-info">
                    <div class="skeleton skeleton-product-detail-title"></div>
                    <div class="skeleton skeleton-product-detail-price"></div>
                    <div class="skeleton skeleton-product-detail-description"></div>
                    <div class="skeleton skeleton-product-detail-description"></div>
                    <div class="skeleton skeleton-product-detail-description"></div>
                    <div class="skeleton skeleton-product-detail-button"></div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Show skeleton and hide content
 * @param {string} contentSelector - Selector for content container
 * @param {string} skeletonType - Type of skeleton (product-grid, order-list, notification, profile, product-detail)
 * @param {number} count - Number of skeleton items (for list types)
 */
function showSkeleton(contentSelector, skeletonType = 'product-grid', count = 6) {
    const container = document.querySelector(contentSelector);
    if (!container) return;
    
    // Hide original content
    const originalContent = container.innerHTML;
    container.setAttribute('data-original-content', originalContent);
    
    // Show skeleton
    let skeletonHTML = '';
    switch(skeletonType) {
        case 'product-grid':
            skeletonHTML = createProductGridSkeleton(count);
            break;
        case 'order-list':
            skeletonHTML = createOrderListSkeleton(count);
            break;
        case 'notification':
            skeletonHTML = createNotificationSkeleton(count);
            break;
        case 'profile':
            skeletonHTML = createProfileSkeleton();
            break;
        case 'product-detail':
            skeletonHTML = createProductDetailSkeleton();
            break;
    }
    
    container.innerHTML = skeletonHTML;
}

/**
 * Hide skeleton and show content
 * @param {string} contentSelector - Selector for content container
 */
function hideSkeleton(contentSelector) {
    const container = document.querySelector(contentSelector);
    if (!container) return;
    
    const originalContent = container.getAttribute('data-original-content');
    if (originalContent) {
        container.innerHTML = originalContent;
        container.removeAttribute('data-original-content');
    }
}

/**
 * Simulate loading with skeleton (for demonstration)
 * @param {string} contentSelector - Selector for content container
 * @param {string} skeletonType - Type of skeleton
 * @param {number} duration - Duration in milliseconds
 */
function simulateLoading(contentSelector, skeletonType = 'product-grid', duration = 2000) {
    showSkeleton(contentSelector, skeletonType);
    
    setTimeout(() => {
        hideSkeleton(contentSelector);
    }, duration);
}

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showLoadingOverlay,
        hideLoadingOverlay,
        setButtonLoading,
        removeButtonLoading,
        createProductGridSkeleton,
        createOrderListSkeleton,
        createNotificationSkeleton,
        createProfileSkeleton,
        createProductDetailSkeleton,
        showSkeleton,
        hideSkeleton,
        simulateLoading
    };
}
