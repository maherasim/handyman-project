/**
 * Centralized Select2 initialization utility
 * Prevents multiple initializations and conflicts
 */

/**
 * Select2 needs the dropdown attached to a parent that is not overflow-clipped.
 * Using the immediate parent() breaks often in admin layouts after production CSS/minify.
 */
export function resolveSelect2DropdownParent($el) {
    const $ = window.$ || window.jQuery;
    if (!$) {
        return null;
    }
    if (!$el || !$el.length) {
        return $(document.body);
    }
    const $modal = $el.closest('.modal');
    if ($modal.length) {
        return $modal;
    }
    const $off = $el.closest('.offcanvas');
    if ($off.length) {
        return $off;
    }
    return $(document.body);
}

export function initializeSelect2(element, options = {}) {
    if (!element || !window.$) {
        console.warn('Select2 element or jQuery not available');
        return null;
    }

    const $element = $(element);

    // Check if already initialized
    if ($element.hasClass('select2-hidden-accessible')) {
        return $element;
    }

    try {
        const $parent = resolveSelect2DropdownParent($element);
        const defaultOptions = {
            width: '100%',
            ...( $parent ? { dropdownParent: $parent } : {} ),
            allowClear: true,
            placeholder: $element.attr('placeholder') || 'Select an option...',
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
export function initializeAllSelect2() {
    if (window.select2GlobalInitialized) {
        return;
    }

    // Initialize elements with .select2 class
    document.querySelectorAll('.select2').forEach(element => {
        initializeSelect2(element);
    });

    // Initialize elements with .select2js class
    document.querySelectorAll('.select2js').forEach(element => {
        initializeSelect2(element);
    });

    window.select2GlobalInitialized = true;
}

// Auto-initialize on DOM ready
if (typeof window !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAllSelect2);
    } else {
        initializeAllSelect2();
    }
}
