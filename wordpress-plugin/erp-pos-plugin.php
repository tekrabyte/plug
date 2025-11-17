<?php
/**
 * Plugin Name: ERP POS System
 * Plugin URI: https://example.com/erp-pos
 * Description: Sistem Point of Sale terintegrasi dengan WooCommerce - Multi-tenant, Real-time inventory, Advanced reporting
 * Version: 2.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: erp-pos
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('ERP_POS_VERSION', '2.0.0');
define('ERP_POS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ERP_POS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ERP_POS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function() {
        echo '<div class="error"><p><strong>ERP POS System</strong> memerlukan WooCommerce untuk diaktifkan.</p></div>';
    });
    return;
}

// Autoload classes
require_once ERP_POS_PLUGIN_DIR . 'includes/class-database.php';
require_once ERP_POS_PLUGIN_DIR . 'includes/class-api.php';
require_once ERP_POS_PLUGIN_DIR . 'includes/class-woocommerce.php';
require_once ERP_POS_PLUGIN_DIR . 'includes/class-admin.php';
require_once ERP_POS_PLUGIN_DIR . 'includes/class-tenant.php';
require_once ERP_POS_PLUGIN_DIR . 'includes/class-permissions.php';

/**
 * Main Plugin Class
 */
class ERP_POS_Plugin {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));
        
        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // Deactivation hook
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load text domain
        load_plugin_textdomain('erp-pos', false, dirname(ERP_POS_PLUGIN_BASENAME) . '/languages');
        
        // Initialize components
        ERP_POS_Database::get_instance();
        ERP_POS_API::get_instance();
        ERP_POS_WooCommerce::get_instance();
        ERP_POS_Admin::get_instance();
        ERP_POS_Tenant::get_instance();
        ERP_POS_Permissions::get_instance();
        
        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add shortcode
        add_shortcode('erp_pos', array($this, 'pos_shortcode'));
    }
    
    public function activate() {
        // Create database tables
        ERP_POS_Database::create_tables();
        
        // Add default capabilities
        ERP_POS_Permissions::add_capabilities();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Cleanup if needed
        flush_rewrite_rules();
    }
    
    public function enqueue_scripts() {
        // Only load on POS page
        if (!is_page() && !has_shortcode(get_post()->post_content ?? '', 'erp_pos')) {
            return;
        }
        
        wp_enqueue_style('erp-pos-styles', ERP_POS_PLUGIN_URL . 'assets/css/pos-styles.css', array(), ERP_POS_VERSION);
        wp_enqueue_script('erp-pos-app', ERP_POS_PLUGIN_URL . 'assets/js/pos-app.js', array(), ERP_POS_VERSION, true);
        
        // Localize script with data
        $current_user = wp_get_current_user();
        $tenant = ERP_POS_Tenant::get_user_tenant($current_user->ID);
        
        wp_localize_script('erp-pos-app', 'ERP_POS_DATA', array(
            'apiUrl' => rest_url('erp/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'tenantId' => $tenant ? $tenant->id : null,
            'userId' => $current_user->ID,
            'userName' => $current_user->display_name,
            'currency' => get_woocommerce_currency(),
            'currencySymbol' => get_woocommerce_currency_symbol(),
            'dateFormat' => get_option('date_format'),
            'timeFormat' => get_option('time_format'),
        ));
    }
    
    public function enqueue_admin_scripts($hook) {
        // Only load on plugin admin pages
        if (strpos($hook, 'erp-pos') === false) {
            return;
        }
        
        wp_enqueue_style('erp-pos-admin', ERP_POS_PLUGIN_URL . 'assets/css/admin-styles.css', array(), ERP_POS_VERSION);
        wp_enqueue_script('erp-pos-admin', ERP_POS_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), ERP_POS_VERSION, true);
    }
    
    public function pos_shortcode($atts) {
        // Check if user has permission
        if (!current_user_can('use_erp_pos')) {
            return '<div class="erp-pos-error">Anda tidak memiliki akses ke POS system.</div>';
        }
        
        return '<div id="pos-root" class="erp-pos-container"></div>';
    }
}

// Initialize plugin
function erp_pos() {
    return ERP_POS_Plugin::get_instance();
}

erp_pos();