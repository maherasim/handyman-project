/**
 * Centralized Select2 initialization utility
 * Prevents multiple initializations and conflicts
 */

export function initializeSelect2(element, options = {}) {
    if (!element || !window.$) {
        console.warn('Select2 element or jQuery not available');
        return null;
    }

    const $element = $(element);

    // IMPORTANT: Skip elements that use data-ajax--url.
    // These are Ajax-powered selects (e.g. category, country, provider dropdowns)
    // that MUST be initialized by their page-level scripts with proper Ajax config.
    // If we initialize them here with generic options, Ajax loading breaks silently.
    if ($element.attr('data-ajax--url') || $element.data('ajax--url') || $element.data('ajaxUrl')) {
        return null;
    }

    // Check if already initialized
    if ($element.hasClass('select2-hidden-accessible')) {
        return $element;
    }

    try {
        const defaultOptions = {
            width: '100%',
            dropdownParent: $element.parent(),
            allowClear: true,
            placeholder: $element.attr('placeholder') || $element.attr('data-placeholder') || 'Select an option...',
            ...options
        };

        $element.select2(defaultOptions);
        return $element;
    } catch (error) {
        console.warn('Select2 initialization failed:', error);
        return null;
    }
}

export function destroySelect2(element) {
    if (!element || !window.$) {
        return;
    }

    const $element = $(element);
    if ($element.hasClass('select2-hidden-accessible')) {
        try {
            $element.select2('destroy');
        } catch (error) {
            console.warn('Select2 destruction failed:', error);
        }
    }
}

export function reinitializeSelect2(element, options = {}) {
    destroySelect2(element);
    return initializeSelect2(element, options);
}

// Global initialization for elements with select2 class
// NOTE: Elements with data-ajax--url are skipped — they are initialized by page-level scripts.
export function initializeAllSelect2() {
    // Initialize elements with .select2 class
    document.querySelectorAll('.select2').forEach(element => {
        initializeSelect2(element);
    });

    // Initialize elements with .select2js class
    document.querySelectorAll('.select2js').forEach(element => {
        initializeSelect2(element);
    });
}

// Auto-initialize on DOM ready
if (typeof window !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAllSelect2);
    } else {
        initializeAllSelect2();
    }
}
