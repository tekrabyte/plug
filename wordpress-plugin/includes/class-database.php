<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database management class
 */
class ERP_POS_Database {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Tenants table
        $table_tenants = $wpdb->prefix . 'erp_tenants';
        $sql_tenants = "CREATE TABLE IF NOT EXISTS $table_tenants (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            status varchar(20) DEFAULT 'active',
            address text,
            phone varchar(50),
            email varchar(100),
            tax_id varchar(100),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";
        dbDelta($sql_tenants);
        
        // User-Tenant relationships
        $table_user_tenants = $wpdb->prefix . 'erp_user_tenants';
        $sql_user_tenants = "CREATE TABLE IF NOT EXISTS $table_user_tenants (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            tenant_id bigint(20) NOT NULL,
            role varchar(50) DEFAULT 'cashier',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_tenant (user_id, tenant_id)
        ) $charset_collate;";
        dbDelta($sql_user_tenants);
        
        // Transactions table
        $table_transactions = $wpdb->prefix . 'erp_transactions';
        $sql_transactions = "CREATE TABLE IF NOT EXISTS $table_transactions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id bigint(20),
            tenant_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            transaction_type varchar(20) DEFAULT 'pos',
            subtotal decimal(10,2) DEFAULT 0,
            tax decimal(10,2) DEFAULT 0,
            discount decimal(10,2) DEFAULT 0,
            total decimal(10,2) DEFAULT 0,
            payment_method varchar(50),
            payment_status varchar(20) DEFAULT 'completed',
            notes text,
            receipt_number varchar(50),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY tenant_id (tenant_id),
            KEY order_id (order_id),
            KEY user_id (user_id),
            KEY receipt_number (receipt_number)
        ) $charset_collate;";
        dbDelta($sql_transactions);
        
        // Transaction items table
        $table_transaction_items = $wpdb->prefix . 'erp_transaction_items';
        $sql_transaction_items = "CREATE TABLE IF NOT EXISTS $table_transaction_items (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            transaction_id bigint(20) NOT NULL,
            product_id bigint(20) NOT NULL,
            variation_id bigint(20) DEFAULT 0,
            product_name varchar(255),
            sku varchar(100),
            price decimal(10,2) DEFAULT 0,
            quantity int(11) DEFAULT 1,
            subtotal decimal(10,2) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY transaction_id (transaction_id),
            KEY product_id (product_id)
        ) $charset_collate;";
        dbDelta($sql_transaction_items);
        
        // Payment history table
        $table_payments = $wpdb->prefix . 'erp_payments';
        $sql_payments = "CREATE TABLE IF NOT EXISTS $table_payments (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            transaction_id bigint(20) NOT NULL,
            payment_method varchar(50),
            amount decimal(10,2) DEFAULT 0,
            reference_number varchar(100),
            status varchar(20) DEFAULT 'completed',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY transaction_id (transaction_id)
        ) $charset_collate;";
        dbDelta($sql_payments);
        
        // Settings table
        $table_settings = $wpdb->prefix . 'erp_settings';
        $sql_settings = "CREATE TABLE IF NOT EXISTS $table_settings (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            tenant_id bigint(20),
            setting_key varchar(100) NOT NULL,
            setting_value longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY setting_key_tenant (setting_key, tenant_id)
        ) $charset_collate;";
        dbDelta($sql_settings);
    }
}