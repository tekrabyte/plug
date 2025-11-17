<?php
/**
 * Admin Pages Handler
 * Handles all admin-related functionality for ERP POS Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class ERP_POS_Admin {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_post_erp_pos_save_settings', array($this, 'save_settings'));
        add_action('admin_post_erp_pos_create_tenant', array($this, 'create_tenant'));
        add_action('admin_post_erp_pos_delete_tenant', array($this, 'delete_tenant'));
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('ERP POS', 'erp-pos'),
            __('ERP POS', 'erp-pos'),
            'manage_options',
            'erp-pos',
            array($this, 'dashboard_page'),
            'dashicons-store',
            56
        );
        
        // Dashboard submenu
        add_submenu_page(
            'erp-pos',
            __('Dashboard', 'erp-pos'),
            __('Dashboard', 'erp-pos'),
            'manage_options',
            'erp-pos',
            array($this, 'dashboard_page')
        );
        
        // Transactions submenu
        add_submenu_page(
            'erp-pos',
            __('Transactions', 'erp-pos'),
            __('Transactions', 'erp-pos'),
            'manage_erp_pos',
            'erp-pos-transactions',
            array($this, 'transactions_page')
        );
        
        // Tenants submenu
        add_submenu_page(
            'erp-pos',
            __('Tenants', 'erp-pos'),
            __('Tenants', 'erp-pos'),
            'manage_options',
            'erp-pos-tenants',
            array($this, 'tenants_page')
        );
        
        // Reports submenu
        add_submenu_page(
            'erp-pos',
            __('Reports', 'erp-pos'),
            __('Reports', 'erp-pos'),
            'manage_erp_pos',
            'erp-pos-reports',
            array($this, 'reports_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'erp-pos',
            __('Settings', 'erp-pos'),
            __('Settings', 'erp-pos'),
            'manage_options',
            'erp-pos-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('erp_pos_settings', 'erp_pos_general_settings');
        register_setting('erp_pos_settings', 'erp_pos_receipt_settings');
        register_setting('erp_pos_settings', 'erp_pos_payment_methods');
        register_setting('erp_pos_settings', 'erp_pos_printer_settings');
        register_setting('erp_pos_settings', 'erp_pos_tax_settings');
    }
    
    /**
     * Dashboard page
     */
    public function dashboard_page() {
        global $wpdb;
        $table_transactions = $wpdb->prefix . 'erp_pos_transactions';
        
        // Get today's stats
        $today_start = date('Y-m-d 00:00:00');
        $today_sales = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(total) FROM $table_transactions WHERE created_at >= %s",
            $today_start
        ));
        
        $today_transactions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_transactions WHERE created_at >= %s",
            $today_start
        ));
        
        // Get this month's stats
        $month_start = date('Y-m-01 00:00:00');
        $month_sales = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(total) FROM $table_transactions WHERE created_at >= %s",
            $month_start
        ));
        
        // Get recent transactions
        $recent_transactions = $wpdb->get_results(
            "SELECT * FROM $table_transactions ORDER BY created_at DESC LIMIT 10"
        );
        
        // Get top products
        $table_items = $wpdb->prefix . 'erp_pos_transaction_items';
        $top_products = $wpdb->get_results(
            "SELECT product_id, product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_sales 
             FROM $table_items 
             GROUP BY product_id 
             ORDER BY total_qty DESC 
             LIMIT 10"
        );
        
        include ERP_POS_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * Transactions page
     */
    public function transactions_page() {
        global $wpdb;
        $table_transactions = $wpdb->prefix . 'erp_pos_transactions';
        
        // Handle filters
        $where = '1=1';
        $params = array();
        
        if (!empty($_GET['start_date'])) {
            $where .= ' AND created_at >= %s';
            $params[] = sanitize_text_field($_GET['start_date']) . ' 00:00:00';
        }
        
        if (!empty($_GET['end_date'])) {
            $where .= ' AND created_at <= %s';
            $params[] = sanitize_text_field($_GET['end_date']) . ' 23:59:59';
        }
        
        if (!empty($_GET['tenant_id'])) {
            $where .= ' AND tenant_id = %d';
            $params[] = intval($_GET['tenant_id']);
        }
        
        if (!empty($_GET['search'])) {
            $where .= ' AND (transaction_number LIKE %s OR customer_name LIKE %s)';
            $search = '%' . $wpdb->esc_like(sanitize_text_field($_GET['search'])) . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        // Pagination
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        // Get total count
        $total_items = $wpdb->get_var(
            empty($params) 
                ? "SELECT COUNT(*) FROM $table_transactions WHERE $where"
                : $wpdb->prepare("SELECT COUNT(*) FROM $table_transactions WHERE $where", $params)
        );
        
        // Get transactions
        $query = "SELECT * FROM $table_transactions WHERE $where ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;
        
        $transactions = $wpdb->get_results($wpdb->prepare($query, $params));
        
        // Get tenants for filter
        $table_tenants = $wpdb->prefix . 'erp_pos_tenants';
        $tenants = $wpdb->get_results("SELECT * FROM $table_tenants WHERE status = 'active'");
        
        include ERP_POS_PLUGIN_DIR . 'admin/views/transactions.php';
    }
    
    /**
     * Tenants page
     */
    public function tenants_page() {
        global $wpdb;
        $table_tenants = $wpdb->prefix . 'erp_pos_tenants';
        
        $tenants = $wpdb->get_results("SELECT * FROM $table_tenants ORDER BY name ASC");
        
        // Get transaction counts for each tenant
        $table_transactions = $wpdb->prefix . 'erp_pos_transactions';
        foreach ($tenants as $tenant) {
            $tenant->transaction_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_transactions WHERE tenant_id = %d",
                $tenant->id
            ));
            
            $tenant->total_sales = $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(total) FROM $table_transactions WHERE tenant_id = %d",
                $tenant->id
            ));
        }
        
        include ERP_POS_PLUGIN_DIR . 'admin/views/tenants.php';
    }
    
    /**
     * Reports page
     */
    public function reports_page() {
        global $wpdb;
        $table_transactions = $wpdb->prefix . 'erp_pos_transactions';
        $table_payments = $wpdb->prefix . 'erp_pos_payments';
        $table_items = $wpdb->prefix . 'erp_pos_transaction_items';
        
        // Get date range
        $start_date = !empty($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : date('Y-m-01');
        $end_date = !empty($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : date('Y-m-d');
        
        // Sales summary
        $sales_data = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as date, COUNT(*) as transactions, SUM(total) as total_sales 
             FROM $table_transactions 
             WHERE created_at BETWEEN %s AND %s 
             GROUP BY DATE(created_at) 
             ORDER BY date ASC",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ));
        
        // Payment method breakdown
        $payment_data = $wpdb->get_results($wpdb->prepare(
            "SELECT p.payment_method, COUNT(DISTINCT t.id) as transactions, SUM(p.amount) as total 
             FROM $table_payments p
             LEFT JOIN $table_transactions t ON p.transaction_id = t.id
             WHERE t.created_at BETWEEN %s AND %s 
             GROUP BY p.payment_method",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ));
        
        // Top products
        $top_products = $wpdb->get_results($wpdb->prepare(
            "SELECT i.product_id, i.product_name, SUM(i.quantity) as total_qty, SUM(i.subtotal) as total_sales 
             FROM $table_items i
             LEFT JOIN $table_transactions t ON i.transaction_id = t.id
             WHERE t.created_at BETWEEN %s AND %s 
             GROUP BY i.product_id 
             ORDER BY total_qty DESC 
             LIMIT 20",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ));
        
        // Hourly sales pattern
        $hourly_sales = $wpdb->get_results($wpdb->prepare(
            "SELECT HOUR(created_at) as hour, COUNT(*) as transactions, SUM(total) as total_sales 
             FROM $table_transactions 
             WHERE created_at BETWEEN %s AND %s 
             GROUP BY HOUR(created_at) 
             ORDER BY hour ASC",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ));
        
        include ERP_POS_PLUGIN_DIR . 'admin/views/reports.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        $general_settings = get_option('erp_pos_general_settings', array());
        $receipt_settings = get_option('erp_pos_receipt_settings', array());
        $payment_methods = get_option('erp_pos_payment_methods', array());
        $printer_settings = get_option('erp_pos_printer_settings', array());
        $tax_settings = get_option('erp_pos_tax_settings', array());
        
        // Default values
        $general_settings = wp_parse_args($general_settings, array(
            'store_name' => get_bloginfo('name'),
            'store_address' => '',
            'store_phone' => '',
            'store_email' => get_option('admin_email'),
            'currency_symbol' => 'Rp',
            'currency_position' => 'left',
            'thousand_separator' => '.',
            'decimal_separator' => ',',
            'number_decimals' => 0
        ));
        
        $receipt_settings = wp_parse_args($receipt_settings, array(
            'header_text' => '',
            'footer_text' => 'Terima kasih atas kunjungan Anda',
            'show_logo' => 'yes',
            'logo_url' => '',
            'paper_width' => '80mm',
            'font_size' => '12px'
        ));
        
        $payment_methods = wp_parse_args($payment_methods, array(
            'cash' => array('enabled' => 'yes', 'label' => 'Cash'),
            'card' => array('enabled' => 'yes', 'label' => 'Card'),
            'qris' => array('enabled' => 'yes', 'label' => 'QRIS'),
            'gopay' => array('enabled' => 'yes', 'label' => 'GoPay'),
            'ovo' => array('enabled' => 'yes', 'label' => 'OVO'),
            'dana' => array('enabled' => 'yes', 'label' => 'DANA'),
            'shopeepay' => array('enabled' => 'yes', 'label' => 'ShopeePay')
        ));
        
        $printer_settings = wp_parse_args($printer_settings, array(
            'auto_print' => 'no',
            'print_copies' => '1',
            'printer_name' => ''
        ));
        
        $tax_settings = wp_parse_args($tax_settings, array(
            'tax_enabled' => 'no',
            'tax_rate' => '10',
            'tax_label' => 'PPN',
            'prices_include_tax' => 'no'
        ));
        
        include ERP_POS_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * Save settings
     */
    public function save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_admin_referer('erp_pos_save_settings');
        
        $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'general';
        
        switch ($tab) {
            case 'general':
                $settings = array(
                    'store_name' => sanitize_text_field($_POST['store_name']),
                    'store_address' => sanitize_textarea_field($_POST['store_address']),
                    'store_phone' => sanitize_text_field($_POST['store_phone']),
                    'store_email' => sanitize_email($_POST['store_email']),
                    'currency_symbol' => sanitize_text_field($_POST['currency_symbol']),
                    'currency_position' => sanitize_text_field($_POST['currency_position']),
                    'thousand_separator' => sanitize_text_field($_POST['thousand_separator']),
                    'decimal_separator' => sanitize_text_field($_POST['decimal_separator']),
                    'number_decimals' => intval($_POST['number_decimals'])
                );
                update_option('erp_pos_general_settings', $settings);
                break;
                
            case 'receipt':
                $settings = array(
                    'header_text' => sanitize_textarea_field($_POST['header_text']),
                    'footer_text' => sanitize_textarea_field($_POST['footer_text']),
                    'show_logo' => sanitize_text_field($_POST['show_logo']),
                    'logo_url' => esc_url_raw($_POST['logo_url']),
                    'paper_width' => sanitize_text_field($_POST['paper_width']),
                    'font_size' => sanitize_text_field($_POST['font_size'])
                );
                update_option('erp_pos_receipt_settings', $settings);
                break;
                
            case 'payment':
                $methods = array();
                if (isset($_POST['payment_methods'])) {
                    foreach ($_POST['payment_methods'] as $key => $method) {
                        $methods[$key] = array(
                            'enabled' => isset($method['enabled']) ? 'yes' : 'no',
                            'label' => sanitize_text_field($method['label'])
                        );
                    }
                }
                update_option('erp_pos_payment_methods', $methods);
                break;
                
            case 'printer':
                $settings = array(
                    'auto_print' => sanitize_text_field($_POST['auto_print']),
                    'print_copies' => intval($_POST['print_copies']),
                    'printer_name' => sanitize_text_field($_POST['printer_name'])
                );
                update_option('erp_pos_printer_settings', $settings);
                break;
                
            case 'tax':
                $settings = array(
                    'tax_enabled' => sanitize_text_field($_POST['tax_enabled']),
                    'tax_rate' => floatval($_POST['tax_rate']),
                    'tax_label' => sanitize_text_field($_POST['tax_label']),
                    'prices_include_tax' => sanitize_text_field($_POST['prices_include_tax'])
                );
                update_option('erp_pos_tax_settings', $settings);
                break;
        }
        
        wp_redirect(add_query_arg(array(
            'page' => 'erp-pos-settings',
            'tab' => $tab,
            'updated' => 'true'
        ), admin_url('admin.php')));
        exit;
    }
    
    /**
     * Create tenant
     */
    public function create_tenant() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_admin_referer('erp_pos_create_tenant');
        
        $tenant = ERP_POS_Tenant::get_instance();
        $tenant_id = $tenant->create_tenant(array(
            'name' => sanitize_text_field($_POST['tenant_name']),
            'code' => sanitize_text_field($_POST['tenant_code']),
            'address' => sanitize_textarea_field($_POST['tenant_address']),
            'phone' => sanitize_text_field($_POST['tenant_phone']),
            'email' => sanitize_email($_POST['tenant_email'])
        ));
        
        if ($tenant_id) {
            wp_redirect(add_query_arg(array(
                'page' => 'erp-pos-tenants',
                'created' => 'true'
            ), admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg(array(
                'page' => 'erp-pos-tenants',
                'error' => 'create_failed'
            ), admin_url('admin.php')));
        }
        exit;
    }
    
    /**
     * Delete tenant
     */
    public function delete_tenant() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_admin_referer('erp_pos_delete_tenant');
        
        $tenant_id = intval($_POST['tenant_id']);
        
        global $wpdb;
        $table = $wpdb->prefix . 'erp_pos_tenants';
        $wpdb->update(
            $table,
            array('status' => 'inactive'),
            array('id' => $tenant_id),
            array('%s'),
            array('%d')
        );
        
        wp_redirect(add_query_arg(array(
            'page' => 'erp-pos-tenants',
            'deleted' => 'true'
        ), admin_url('admin.php')));
        exit;
    }
}
