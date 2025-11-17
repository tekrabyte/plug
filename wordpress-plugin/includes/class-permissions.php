<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Permissions and capabilities management
 */
class ERP_POS_Permissions {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public static function add_capabilities() {
        $admin = get_role('administrator');
        $shop_manager = get_role('shop_manager');
        
        $caps = array(
            'use_erp_pos',
            'manage_erp_pos',
            'view_erp_transactions',
            'manage_erp_tenants',
            'view_erp_reports',
        );
        
        foreach ($caps as $cap) {
            if ($admin) $admin->add_cap($cap);
            if ($shop_manager) $shop_manager->add_cap($cap);
        }
        
        // Add cashier role
        add_role(
            'erp_cashier',
            'POS Cashier',
            array(
                'read' => true,
                'use_erp_pos' => true,
            )
        );
    }
    
    public static function remove_capabilities() {
        $roles = array('administrator', 'shop_manager', 'erp_cashier');
        $caps = array(
            'use_erp_pos',
            'manage_erp_pos',
            'view_erp_transactions',
            'manage_erp_tenants',
            'view_erp_reports',
        );
        
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($caps as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
        
        remove_role('erp_cashier');
    }
}