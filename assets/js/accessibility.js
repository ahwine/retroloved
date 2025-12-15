// Accessibility Helper Functions
// Step 8C: Accessibility Improvements

/**
 * Detect keyboard navigation and add class to body
 */
function detectKeyboardNavigation() {
    let isKeyboardUser = false;
    
    // Detect first Tab key press
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab' && !isKeyboardUser) {
            isKeyboardUser = true;
            document.body.classList.add('keyboard-navigation-active');
        }
    });
    
    // Detect mouse usage
    document.addEventListener('mousedown', function() {
        if (isKeyboardUser) {
            isKeyboardUser = false;
            document.body.classList.remove('keyboard-navigation-active');
        }
    });
}

/**
 * Add skip to content link
 */
function addSkipToContent() {
    const skipLink = document.createElement('a');
    skipLink.href = '#main-content';
    skipLink.className = 'skip-to-content';
    skipLink.textContent = 'Skip to main content';
    skipLink.setAttribute('accesskey', 's');
    
    document.body.insertBefore(skipLink, document.body.firstChild);
    
    // Ensure main content has ID
    const mainContent = document.querySelector('main, .main-content, [role="main"]');
    if (mainContent && !mainContent.id) {
        mainContent.id = 'main-content';
        mainContent.setAttribute('tabindex', '-1');
    }
    
    // Handle skip link click
    skipLink.addEventListener('click', function(e) {
        e.preventDefault();
        if (mainContent) {
            mainContent.focus();
            mainContent.scrollIntoView({ behavior: 'smooth' });
        }
    });
}

/**
 * Add ARIA labels to elements missing them
 */
function addAriaLabels() {
    // Add aria-label to buttons without text
    document.querySelectorAll('button:not([aria-label]):not([aria-labelledby])').forEach(button => {
        if (!button.textContent.trim()) {
            // Try to infer from icon or context
            const icon = button.querySelector('svg, i, img');
            if (icon) {
                const title = icon.getAttribute('title') || icon.getAttribute('alt');
                if (title) {
                    button.setAttribute('aria-label', title);
                }
            }
        }
    });
    
    // Add aria-label to links without text
    document.querySelectorAll('a:not([aria-label]):not([aria-labelledby])').forEach(link => {
        if (!link.textContent.trim()) {
            const img = link.querySelector('img');
            if (img && img.alt) {
                link.setAttribute('aria-label', img.alt);
            }
        }
    });
    
    // Add aria-label to search inputs
    document.querySelectorAll('input[type="search"]:not([aria-label])').forEach(input => {
        if (!input.id || !document.querySelector('label[for="'+input.id+'"]')) {
            input.setAttribute('aria-label', 'Search');
        }
    });
    
    // Add role and aria-label to navigation
    document.querySelectorAll('nav:not([role])').forEach(nav => {
        nav.setAttribute('role', 'navigation');
        if (!nav.hasAttribute('aria-label')) {
            nav.setAttribute('aria-label', 'Main navigation');
        }
    });
}

/**
 * Improve image alt text
 */
function improveImageAltText() {
    // Find images without alt attribute
    document.querySelectorAll('img:not([alt])').forEach(img => {
        const src = img.src;
        const filename = src.split('/').pop().split('.')[0];
        
        // Try to generate meaningful alt text from filename
        const altText = filename
            .replace(/[-_]/g, ' ')
            .replace(/\d+/g, '')
            .trim();
        
        if (altText) {
            img.alt = altText;
            console.warn('Added alt text to image:', img.src, '→', altText);
        } else {
            img.alt = 'Image';
            console.warn('Added generic alt text to image:', img.src);
        }
    });
    
    // Mark decorative images
    document.querySelectorAll('img[alt=""]').forEach(img => {
        img.setAttribute('role', 'presentation');
    });
}

/**
 * Add ARIA live region for dynamic content
 */
function createLiveRegion() {
    let liveRegion = document.getElementById('aria-live-region');
    
    if (!liveRegion) {
        liveRegion = document.createElement('div');
        liveRegion.id = 'aria-live-region';
        liveRegion.className = 'sr-only';
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        document.body.appendChild(liveRegion);
    }
    
    return liveRegion;
}

/**
 * Announce message to screen readers
 * @param {string} message - Message to announce
 * @param {string} priority - 'polite' or 'assertive'
 */
function announceToScreenReader(message, priority = 'polite') {
    const liveRegion = createLiveRegion();
    liveRegion.setAttribute('aria-live', priority);
    
    // Clear previous message
    liveRegion.textContent = '';
    
    // Announce new message after short delay
    setTimeout(() => {
        liveRegion.textContent = message;
    }, 100);
    
    // Clear after announcement
    setTimeout(() => {
        liveRegion.textContent = '';
    }, 3000);
}

/**
 * Focus trap for modals
 * @param {HTMLElement} element - Modal element
 */
function trapFocus(element) {
    const focusableElements = element.querySelectorAll(
        'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
    );
    
    const firstFocusable = focusableElements[0];
    const lastFocusable = focusableElements[focusableElements.length - 1];
    
    // Focus first element
    if (firstFocusable) {
        firstFocusable.focus();
    }
    
    element.addEventListener('keydown', function(e) {
        if (e.key !== 'Tab') return;
        
        if (e.shiftKey) {
            // Shift + Tab
            if (document.activeElement === firstFocusable) {
                lastFocusable.focus();
                e.preventDefault();
            }
        } else {
            // Tab
            if (document.activeElement === lastFocusable) {
                firstFocusable.focus();
                e.preventDefault();
            }
        }
    });
}

/**
 * Add proper heading structure
 */
function validateHeadingStructure() {
    const headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
    let previousLevel = 0;
    
    headings.forEach((heading, index) => {
        const level = parseInt(heading.tagName.substring(1));
        
        // Check if heading level jumps (e.g., h1 to h3)
        if (previousLevel > 0 && level > previousLevel + 1) {
            console.warn(
                'Heading structure issue: h' + previousLevel + ' followed by h' + level,
                heading
            );
        }
        
        // Ensure page has only one h1
        if (level === 1 && index > 0) {
            const existingH1 = Array.from(headings).find(h => h.tagName === 'H1' && h !== heading);
            if (existingH1) {
                console.warn('Multiple h1 elements found. Should have only one h1 per page.');
            }
        }
        
        previousLevel = level;
    });
}

/**
 * Add form field descriptions
 */
function improveFormAccessibility() {
    // Link labels to inputs
    document.querySelectorAll('input, select, textarea').forEach(field => {
        if (!field.id) {
            field.id = 'field-' + Math.random().toString(36).substr(2, 9);
        }
        
        // Find associated label
        const label = field.parentElement.querySelector('label');
        if (label && !label.hasAttribute('for')) {
            label.setAttribute('for', field.id);
        }
        
        // Add aria-required for required fields
        if (field.hasAttribute('required') && !field.hasAttribute('aria-required')) {
            field.setAttribute('aria-required', 'true');
        }
        
        // Add aria-invalid for error states
        if (field.classList.contains('error') && !field.hasAttribute('aria-invalid')) {
            field.setAttribute('aria-invalid', 'true');
        }
    });
}

/**
 * Improve button accessibility
 */
function improveButtonAccessibility() {
    document.querySelectorAll('button, [role="button"]').forEach(button => {
        // Add type if missing
        if (button.tagName === 'BUTTON' && !button.hasAttribute('type')) {
            button.setAttribute('type', 'button');
        }
        
        // Ensure disabled buttons are not focusable
        if (button.hasAttribute('disabled')) {
            button.setAttribute('tabindex', '-1');
        }
    });
}

/**
 * Add table accessibility
 */
function improveTableAccessibility() {
    document.querySelectorAll('table').forEach(table => {
        // Add caption if missing
        if (!table.querySelector('caption')) {
            const caption = document.createElement('caption');
            caption.className = 'sr-only';
            caption.textContent = 'Data table';
            table.insertBefore(caption, table.firstChild);
        }
        
        // Ensure th elements have scope
        table.querySelectorAll('th:not([scope])').forEach(th => {
            const isHeader = th.parentElement.parentElement.tagName === 'THEAD';
            th.setAttribute('scope', isHeader ? 'col' : 'row');
        });
    });
}

/**
 * Initialize all accessibility improvements
 */
function initAccessibility() {
    detectKeyboardNavigation();
    // addSkipToContent(); // Removed as per request
    addAriaLabels();
    improveImageAltText();
    createLiveRegion();
    validateHeadingStructure();
    improveFormAccessibility();
    improveButtonAccessibility();
    improveTableAccessibility();
    
    console.log('✅ Accessibility improvements initialized');
}

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAccessibility);
} else {
    initAccessibility();
}

// Export functions
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        detectKeyboardNavigation,
        addSkipToContent,
        addAriaLabels,
        improveImageAltText,
        announceToScreenReader,
        trapFocus,
        validateHeadingStructure,
        improveFormAccessibility,
        improveButtonAccessibility,
        improveTableAccessibility,
        initAccessibility
    };
}
