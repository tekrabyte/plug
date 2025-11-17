<?php
/**
 * Settings View
 */
if (!defined('ABSPATH')) exit;

$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
?>

<div class="wrap">
    <h1><?php _e('ERP POS Settings', 'erp-pos'); ?></h1>
    
    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Settings saved successfully!', 'erp-pos'); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Tabs Navigation -->
    <nav class="nav-tab-wrapper" style="margin: 20px 0;">
        <a href="?page=erp-pos-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
            <?php _e('General', 'erp-pos'); ?>
        </a>
        <a href="?page=erp-pos-settings&tab=receipt" class="nav-tab <?php echo $active_tab === 'receipt' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Receipt', 'erp-pos'); ?>
        </a>
        <a href="?page=erp-pos-settings&tab=payment" class="nav-tab <?php echo $active_tab === 'payment' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Payment Methods', 'erp-pos'); ?>
        </a>
        <a href="?page=erp-pos-settings&tab=printer" class="nav-tab <?php echo $active_tab === 'printer' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Printer', 'erp-pos'); ?>
        </a>
        <a href="?page=erp-pos-settings&tab=tax" class="nav-tab <?php echo $active_tab === 'tax' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Tax & Discount', 'erp-pos'); ?>
        </a>
    </nav>
    
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('erp_pos_save_settings'); ?>
        <input type="hidden" name="action" value="erp_pos_save_settings">
        <input type="hidden" name="tab" value="<?php echo esc_attr($active_tab); ?>">
        
        <div class="erp-pos-settings-content" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            
            <?php if ($active_tab === 'general'): ?>
                <!-- General Settings -->
                <h2><?php _e('General Settings', 'erp-pos'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="store_name"><?php _e('Store Name', 'erp-pos'); ?></label></th>
                        <td>
                            <input type="text" name="store_name" id="store_name" value="<?php echo esc_attr($general_settings['store_name']); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="store_address"><?php _e('Store Address', 'erp-pos'); ?></label></th>
                        <td>
                            <textarea name="store_address" id="store_address" class="large-text" rows="3"><?php echo esc_textarea($general_settings['store_address']); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="store_phone"><?php _e('Store Phone', 'erp-pos'); ?></label></th>
                        <td>
                            <input type="text" name="store_phone" id="store_phone" value="<?php echo esc_attr($general_settings['store_phone']); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="store_email"><?php _e('Store Email', 'erp-pos'); ?></label></th>
                        <td>
                            <input type="email" name="store_email" id="store_email" value="<?php echo esc_attr($general_settings['store_email']); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="currency_symbol"><?php _e('Currency Symbol', 'erp-pos'); ?></label></th>
                        <td>
                            <input type="text" name="currency_symbol" id="currency_symbol" value="<?php echo esc_attr($general_settings['currency_symbol']); ?>" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="currency_position"><?php _e('Currency Position', 'erp-pos'); ?></label></th>
                        <td>
                            <select name="currency_position" id="currency_position">
                                <option value="left" <?php selected($general_settings['currency_position'], 'left'); ?>><?php _e('Left (Rp 10.000)', 'erp-pos'); ?></option>
                                <option value="right" <?php selected($general_settings['currency_position'], 'right'); ?>><?php _e('Right (10.000 Rp)', 'erp-pos'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="thousand_separator"><?php _e('Thousand Separator', 'erp-pos'); ?></label></th>
                        <td>
                            <input type="text" name="thousand_separator" id="thousand_separator" value="<?php echo esc_attr($general_settings['thousand_separator']); ?>" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="decimal_separator"><?php _e('Decimal Separator', 'erp-pos'); ?></label></th>
                        <td>
                            <input type="text" name="decimal_separator" id="decimal_separator" value="<?php echo esc_attr($general_settings['decimal_separator']); ?>" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="number_decimals"><?php _e('Number of Decimals', 'erp-pos'); ?></label></th>
                        <td>
                            <input type="number" name="number_decimals" id="number_decimals" value="<?php echo esc_attr($general_settings['number_decimals']); ?>" class="small-text" min="0" max="4">
                        </td>
                    </tr>
                </table>
                
            <?php elseif ($active_tab === 'receipt'): ?>
                <!-- Receipt Settings -->
                <h2><?php _e('Receipt Settings', 'erp-pos'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="header_text"><?php _e('Receipt Header', 'erp-pos'); ?></label></th>
                        <td>
                            <textarea name="header_text" id="header_text" class="large-text" rows="3"><?php echo esc_textarea($receipt_settings['header_text']); ?></textarea>
                            <p class="description"><?php _e('Text to display at the top of receipt', 'erp-pos'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="footer_text"><?php _e('Receipt Footer', 'erp-pos'); ?></label></th>
                        <td>
                            <textarea name="footer_text" id="footer_text" class="large-text" rows="3"><?php echo esc_textarea($receipt_settings['footer_text']); ?></textarea>
                            <p class="description"><?php _e('Text to display at the bottom of receipt', 'erp-pos'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="show_logo"><?php _e('Show Logo', 'erp-pos'); ?></label></th>
                        <td>
                            <select name="show_logo" id="show_logo">
                                <option value="yes" <?php selected($receipt_settings['show_logo'], 'yes'); ?>><?php _e('Yes', 'erp-pos'); ?></option>
                                <option value="no" <?php selected($receipt_settings['show_logo'], 'no'); ?>><?php _e('No', 'erp-pos'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="logo_url"><?php _e('Logo URL', 'erp-pos'); ?></label></th>
                        <td>
                            <input type="url" name="logo_url" id="logo_url" value="<?php echo esc_url($receipt_settings['logo_url']); ?>" class="regular-text">
                            <button type="button" class="button" onclick="openMediaUploader()"><?php _e('Upload', 'erp-pos'); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="paper_width"><?php _e('Paper Width', 'erp-pos'); ?></label></th>
                        <td>
                            <select name="paper_width" id="paper_width">
                                <option value="58mm" <?php selected($receipt_settings['paper_width'], '58mm'); ?>>58mm</option>
                                <option value="80mm" <?php selected($receipt_settings['paper_width'], '80mm'); ?>>80mm</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="font_size"><?php _e('Font Size', 'erp-pos'); ?></label></th>
                        <td>
                            <select name="font_size" id="font_size">
                                <option value="10px" <?php selected($receipt_settings['font_size'], '10px'); ?>>10px</option>
                                <option value="12px" <?php selected($receipt_settings['font_size'], '12px'); ?>>12px</option>
                                <option value="14px" <?php selected($receipt_settings['font_size'], '14px'); ?>>14px</option>
                            </select>
                        </td>
                    </tr>
                </table>
                
            <?php elseif ($active_tab === 'payment'): ?>
                <!-- Payment Methods Settings -->
                <h2><?php _e('Payment Methods', 'erp-pos'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;"><?php _e('Enabled', 'erp-pos'); ?></th>
                            <th><?php _e('Payment Method', 'erp-pos'); ?></th>
                            <th><?php _e('Display Name', 'erp-pos'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payment_methods as $key => $method): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="payment_methods[<?php echo esc_attr($key); ?>][enabled]" value="yes" <?php checked($method['enabled'], 'yes'); ?>>
                                </td>
                                <td><strong><?php echo esc_html(ucfirst($key)); ?></strong></td>
                                <td>
                                    <input type="text" name="payment_methods[<?php echo esc_attr($key); ?>][label]" value="<?php echo esc_attr($method['label']); ?>" class="regular-text">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
            <?php elseif ($active_tab === 'printer'): ?>
                <!-- Printer Settings -->
                <h2><?php _e('Printer Settings', 'erp-pos'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="auto_print"><?php _e('Auto Print Receipt', 'erp-pos'); ?></label></th>
                        <td>
                            <select name="auto_print" id="auto_print">
                                <option value="yes" <?php selected($printer_settings['auto_print'], 'yes'); ?>><?php _e('Yes', 'erp-pos'); ?></option>
                                <option value="no" <?php selected($printer_settings['auto_print'], 'no'); ?>><?php _e('No', 'erp-pos'); ?></option>
                            </select>
                            <p class="description"><?php _e('Automatically print receipt after completing transaction', 'erp-pos'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="print_copies"><?php _e('Number of Copies', 'erp-pos'); ?></label></th>
                        <td>
                            <input type="number" name="print_copies" id="print_copies" value="<?php echo esc_attr($printer_settings['print_copies']); ?>" class="small-text" min="1" max="5">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="printer_name"><?php _e('Printer Name', 'erp-pos'); ?></label></th>
                        <td>
                            <input type="text" name="printer_name" id="printer_name" value="<?php echo esc_attr($printer_settings['printer_name']); ?>" class="regular-text">
                            <p class="description"><?php _e('Leave empty to use default printer', 'erp-pos'); ?></p>
                        </td>
                    </tr>
                </table>
                
            <?php elseif ($active_tab === 'tax'): ?>
                <!-- Tax Settings -->
                <h2><?php _e('Tax & Discount Settings', 'erp-pos'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="tax_enabled"><?php _e('Enable Tax', 'erp-pos'); ?></label></th>
                        <td>
                            <select name="tax_enabled" id="tax_enabled">
                                <option value="yes" <?php selected($tax_settings['tax_enabled'], 'yes'); ?>><?php _e('Yes', 'erp-pos'); ?></option>
                                <option value="no" <?php selected($tax_settings['tax_enabled'], 'no'); ?>><?php _e('No', 'erp-pos'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="tax_rate"><?php _e('Tax Rate (%)', 'erp-pos'); ?></label></th>
                        <td>
                            <input type="number" name="tax_rate" id="tax_rate" value="<?php echo esc_attr($tax_settings['tax_rate']); ?>" class="small-text" step="0.01" min="0" max="100">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="tax_label"><?php _e('Tax Label', 'erp-pos'); ?></label></th>
                        <td>
                            <input type="text" name="tax_label" id="tax_label" value="<?php echo esc_attr($tax_settings['tax_label']); ?>" class="regular-text">
                            <p class="description"><?php _e('E.g., VAT, PPN, Tax', 'erp-pos'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="prices_include_tax"><?php _e('Prices Include Tax', 'erp-pos'); ?></label></th>
                        <td>
                            <select name="prices_include_tax" id="prices_include_tax">
                                <option value="yes" <?php selected($tax_settings['prices_include_tax'], 'yes'); ?>><?php _e('Yes', 'erp-pos'); ?></option>
                                <option value="no" <?php selected($tax_settings['prices_include_tax'], 'no'); ?>><?php _e('No', 'erp-pos'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>
            
            <p class="submit">
                <button type="submit" class="button button-primary"><?php _e('Save Changes', 'erp-pos'); ?></button>
            </p>
        </div>
    </form>
</div>

<script>
function openMediaUploader() {
    if (typeof wp.media !== 'undefined') {
        const mediaUploader = wp.media({
            title: '<?php _e('Select Logo', 'erp-pos'); ?>',
            button: {
                text: '<?php _e('Select', 'erp-pos'); ?>'
            },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            document.getElementById('logo_url').value = attachment.url;
        });
        
        mediaUploader.open();
    }
}
</script>
