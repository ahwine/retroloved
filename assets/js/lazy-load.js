/**
 * LAZY LOADING IMAGES - PERFORMANCE OPTIMIZATION
 * Automatically lazy load images to improve page load performance
 */

(function() {
    'use strict';

    // Check if browser supports Intersection Observer
    const supportsIntersectionObserver = 'IntersectionObserver' in window;

    // Lazy load configuration
    const config = {
        rootMargin: '50px 0px',
        threshold: 0.01
    };

    // Get all lazy load images
    function getLazyImages() {
        return document.querySelectorAll('img[data-src], img[loading="lazy"]');
    }

    // Load image function
    function loadImage(img) {
        // Check if data-src exists
        if (img.dataset.src) {
            // Create a new image to preload
            const tempImg = new Image();
            
            tempImg.onload = function() {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                img.classList.add('loaded');
                
                // Add fade-in effect
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.3s ease-in-out';
                setTimeout(() => {
                    img.style.opacity = '1';
                }, 10);
            };
            
            tempImg.onerror = function() {
                // Fallback to placeholder if image fails to load
                if (img.dataset.fallback) {
                    img.src = img.dataset.fallback;
                }
                img.classList.add('error');
            };
            
            tempImg.src = img.dataset.src;
        }
    }

    // Intersection Observer callback
    function onIntersection(entries, observer) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                loadImage(img);
                observer.unobserve(img);
            }
        });
    }

    // Initialize lazy loading
    function initLazyLoad() {
        const images = getLazyImages();

        if (images.length === 0) return;

        if (supportsIntersectionObserver) {
            // Use Intersection Observer for modern browsers
            const observer = new IntersectionObserver(onIntersection, config);

            images.forEach(img => {
                // Add placeholder class
                img.classList.add('lazy-loading');
                observer.observe(img);
            });
        } else {
            // Fallback for older browsers - load all images immediately
            images.forEach(img => {
                loadImage(img);
            });
        }
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLazyLoad);
    } else {
        initLazyLoad();
    }

    // Re-initialize when new content is added dynamically
    window.reinitLazyLoad = initLazyLoad;

    // Add CSS for lazy loading effect
    const lazyLoadStyle = document.createElement('style');
    lazyLoadStyle.textContent = `
        img.lazy-loading {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: lazy-loading 1.5s ease-in-out infinite;
        }

        @keyframes lazy-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        img.loaded {
            background: none;
            animation: none;
        }

        img.error {
            background: #f3f4f6;
            border: 2px dashed #d1d5db;
        }
    `;
    document.head.appendChild(lazyLoadStyle);

    // Preload critical images (above the fold)
    function preloadCriticalImages() {
        const criticalImages = document.querySelectorAll('img.critical, .hero img, .banner img');
        criticalImages.forEach(img => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
            }
        });
    }

    // Run on load
    window.addEventListener('load', preloadCriticalImages);

})();
