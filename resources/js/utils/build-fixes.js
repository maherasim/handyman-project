/**
 * Build Fixes Utility
 * Addresses common build-related issues with Vue/Laravel applications
 */

export class BuildFixes {
    constructor() {
        this.initialized = false;
        this.conflicts = [];
    }

    /**
     * Initialize all build fixes
     */
    init() {
        if (this.initialized) {
            return;
        }

        this.fixScrollbarConflicts();
        this.fixSelect2Conflicts();
        this.fixDropdownConflicts();
        this.fixZIndexConflicts();
        this.fixCacheIssues();

        this.initialized = true;
        console.log('Build fixes initialized successfully');
    }

    /**
     * Fix scrollbar initialization conflicts
     */
    fixScrollbarConflicts() {
        // Ensure smooth-scrollbar is only initialized once
        if (window.Scrollbar && !window.scrollbarGlobalInitialized) {
            document.querySelectorAll('.data-scrollbar[data-scroll]').forEach(element => {
                if (!element.hasAttribute('data-scrollbar-initialized')) {
                    try {
                        window.Scrollbar.init(element, {
                            damping: 0.1,
                            thumbMinSize: 20,
                            renderByPixels: true,
                            alwaysShowTracks: false,
                            continuousScrolling: true
                        });
                        element.setAttribute('data-scrollbar-initialized', 'true');
                    } catch (error) {
                        console.warn('Scrollbar initialization failed:', error);
                    }
                }
            });
            window.scrollbarGlobalInitialized = true;
        }
    }

    /**
     * Fix Select2 initialization conflicts
     */
    fixSelect2Conflicts() {
        if (window.$ && !window.select2GlobalInitialized) {
            // Initialize Select2 for all relevant elements
            const selectors = ['.select2', '.select2js', 'select[class*="select2"]'];

            selectors.forEach(selector => {
                document.querySelectorAll(selector).forEach(element => {
                    const $element = $(element);
                    if (!$element.hasClass('select2-hidden-accessible')) {
                        try {
                            $element.select2({
                                width: '100%',
                                dropdownParent: $element.parent(),
                                allowClear: true
                            });
                        } catch (error) {
                            console.warn('Select2 initialization failed:', error);
                        }
                    }
                });
            });

            window.select2GlobalInitialized = true;
        }
    }

    /**
     * Fix dropdown menu conflicts
     */
    fixDropdownConflicts() {
        // Fix Bootstrap dropdown z-index issues
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (!menu.style.zIndex) {
                menu.style.zIndex = '1050';
            }
        });

        // Fix Select2 dropdown z-index
        if (window.$) {
            $(document).on('select2:open', function (e) {
                const dropdown = $('.select2-dropdown');
                dropdown.css('z-index', '9999');
            });
        }
    }

    /**
     * Fix z-index conflicts
     */
    fixZIndexConflicts() {
        const zIndexMap = {
            '.modal': '1055',
            '.modal-backdrop': '1050',
            '.tooltip': '1070',
            '.popover': '1060',
            '.iq-top-navbar.fixed': '1030',
            '.iq-sidebar': '1020'
        };

        Object.entries(zIndexMap).forEach(([selector, zIndex]) => {
            document.querySelectorAll(selector).forEach(element => {
                if (!element.style.zIndex) {
                    element.style.zIndex = zIndex;
                }
            });
        });
    }

    /**
     * Fix cache-related issues
     */
    fixCacheIssues() {
        // Force reflow to prevent cached styles
        document.querySelectorAll('.cache-bust').forEach(element => {
            element.style.transform = 'translateZ(0)';
        });

        // Clear any cached scrollbar instances
        if (window.Scrollbar) {
            document.querySelectorAll('[data-scrollbar-initialized="true"]').forEach(element => {
                if (element.scrollbar) {
                    try {
                        element.scrollbar.destroy();
                        element.removeAttribute('data-scrollbar-initialized');
                    } catch (error) {
                        console.warn('Scrollbar destruction failed:', error);
                    }
                }
            });
        }
    }

    /**
     * Reinitialize components after dynamic content changes
     */
    reinitializeComponents() {
        // Reinitialize scrollbars
        if (window.Scrollbar) {
            window.scrollbarGlobalInitialized = false;
            this.fixScrollbarConflicts();
        }

        // Reinitialize Select2
        if (window.$) {
            window.select2GlobalInitialized = false;
            this.fixSelect2Conflicts();
        }

        // Fix other conflicts
        this.fixDropdownConflicts();
        this.fixZIndexConflicts();
    }

    /**
     * Clean up resources
     */
    destroy() {
        // Destroy scrollbars
        if (window.Scrollbar) {
            document.querySelectorAll('[data-scrollbar-initialized="true"]').forEach(element => {
                if (element.scrollbar) {
                    try {
                        element.scrollbar.destroy();
                    } catch (error) {
                        console.warn('Scrollbar destruction failed:', error);
                    }
                }
            });
        }

        // Destroy Select2 instances
        if (window.$) {
            document.querySelectorAll('.select2-hidden-accessible').forEach(element => {
                try {
                    $(element).select2('destroy');
                } catch (error) {
                    console.warn('Select2 destruction failed:', error);
                }
            });
        }

        this.initialized = false;
    }
}

// Global instance
window.buildFixes = new BuildFixes();

// Auto-initialize on DOM ready
if (typeof window !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.buildFixes.init();
        });
    } else {
        window.buildFixes.init();
    }
}

export default BuildFixes;
