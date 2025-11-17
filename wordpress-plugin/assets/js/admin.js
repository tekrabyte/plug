/**
 * ERP POS Admin JavaScript
 */
(function($) {
    'use strict';
    $(document).ready(function() {
        console.log('ERP POS Admin: Loaded');
    });
    
    window.erpPosAjax = function(action, data, callback) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: action, nonce: erpPosAdmin.nonce, ...data },
            success: function(response) { if (callback) callback(response); },
            error: function(xhr, status, error) { console.error('Error:', error); }
        });
    };
})(jQuery);
