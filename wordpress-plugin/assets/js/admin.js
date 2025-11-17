/**
 * ERP POS Admin JavaScript
 * Handles admin panel interactions
 */

(function($) {
    'use strict';
    
    // Initialize on document ready
    $(document).ready(function() {
        initCharts();
        initDataTables();
        initModals();
        initFormValidation();
    });
    
    /**
     * Initialize Charts
     */
    function initCharts() {
        // Chart initialization is handled by Chart.js in the view files
        console.log('ERP POS: Charts initialized');
    }
    
    /**
     * Initialize DataTables (if needed)
     */
    function initDataTables() {
        // Can add jQuery DataTables for better table functionality
        console.log('ERP POS: DataTables ready');
    }
    
    /**
     * Initialize Modals
     */
    function initModals() {
        // Close modal on overlay click
        $(document).on('click', '.erp-pos-modal-overlay', function(e) {
            if (e.target === this) {
                $(this).fadeOut();
            }
        });
        
        // Close modal on close button
        $(document).on('click', '.erp-pos-modal-close', function() {
            $(this).closest('.erp-pos-modal-overlay').fadeOut();
        });
    }
    
    /**
     * Form Validation
     */
    function initFormValidation() {
        // Add validation to forms
        $('form.erp-pos-form').on('submit', function(e) {
            const $form = $(this);
            let isValid = true;
            
            // Check required fields
            $form.find('[required]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    $(this).addClass('error').focus();
                    return false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill all required fields', 'error');
            }
        });
        
        // Remove error class on input
        $('input, select, textarea').on('input change', function() {
            $(this).removeClass('error');
        });
    }
    
    /**
     * Show Notification
     */
    function showNotification(message, type = 'info') {
        const $notification = $('<div class="erp-pos-message ' + type + '">' + message + '</div>');
        $('.wrap').prepend($notification);
        
        setTimeout(function() {
            $notification.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    /**
     * AJAX Helper
     */
    window.erpPosAjax = function(action, data, callback) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: action,
                nonce: erpPosAdmin.nonce,
                ...data
            },
            success: function(response) {
                if (callback) callback(response);
            },
            error: function(xhr, status, error) {
                showNotification('An error occurred: ' + error, 'error');
            }
        });
    };
    
    /**
     * Export Functions
     */
    window.erpPosExport = function(type) {
        showNotification('Preparing export...', 'info');
        
        // Build export URL with current filters
        const params = new URLSearchParams(window.location.search);
        params.set('export', type);
        
        window.location.href = window.location.pathname + '?' + params.toString();
    };
    
    /**
     * Print Function
     */
    window.erpPosPrint = function(elementId) {
        const content = document.getElementById(elementId).innerHTML;
        const printWindow = window.open('', '', 'height=600,width=800');
        
        printWindow.document.write('<html><head><title>Print</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: Arial, sans-serif; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
        printWindow.document.write('th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }');
        printWindow.document.write('th { background: #f0f0f0; }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');
        
        printWindow.document.close();
        printWindow.print();
    };
    
})(jQuery);
