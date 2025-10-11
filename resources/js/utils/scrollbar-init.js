/**
 * Centralized scrollbar initialization utility
 * Prevents multiple initializations and conflicts
 */

export function initializeScrollbar(element, options = {}) {
    if (!element || !window.Scrollbar) {
        console.warn('Scrollbar element or library not available');
        return null;
    }

    // Check if already initialized
    if (element.hasAttribute('data-scrollbar-initialized')) {
        return element.scrollbar;
    }

    try {
        const scrollbar = window.Scrollbar.init(element, {
            damping: 0.1,
            thumbMinSize: 20,
            renderByPixels: true,
            alwaysShowTracks: false,
            continuousScrolling: true,
            ...options
        });

        element.setAttribute('data-scrollbar-initialized', 'true');
        return scrollbar;
    } catch (error) {
        console.warn('Scrollbar initialization failed:', error);
        return null;
    }
}

export function destroyScrollbar(element) {
    if (element && element.scrollbar) {
        try {
            element.scrollbar.destroy();
            element.removeAttribute('data-scrollbar-initialized');
        } catch (error) {
            console.warn('Scrollbar destruction failed:', error);
        }
    }
}

export function reinitializeScrollbar(element, options = {}) {
    destroyScrollbar(element);
    return initializeScrollbar(element, options);
}

// Global initialization for elements with data-scroll attribute
export function initializeAllScrollbars() {
    if (window.scrollbarGlobalInitialized) {
        return;
    }

    document.querySelectorAll('.data-scrollbar[data-scroll]').forEach(element => {
        const scrollId = element.getAttribute('data-scroll');
        if (scrollId) {
            initializeScrollbar(element);
        }
    });

    window.scrollbarGlobalInitialized = true;
}

// Auto-initialize on DOM ready
if (typeof window !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAllScrollbars);
    } else {
        initializeAllScrollbars();
    }
}
