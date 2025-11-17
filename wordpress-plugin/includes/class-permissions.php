<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Permissions and capabilities management
 */
class TEKRAERPOS_Permissions {
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
            'use_tekraerpos',
            'manage_tekraerpos',
            'view_tekraerpos_transactions',
            'manage_tekraerpos_tenants',
            'view_tekraerpos_reports',
        );
        
        foreach ($caps as $cap) {
            if ($admin) $admin->add_cap($cap);
            if ($shop_manager) $shop_manager->add_cap($cap);
        }
        
        // Add cashier role
        add_role(
            'tekraerpos_cashier',
            'Tekra ErPos Cashier',
            array(
                'read' => true,
                'use_tekraerpos' => true,
            )
        );
    }
    
    public static function remove_capabilities() {
        $roles = array('administrator', 'shop_manager', 'tekraerpos_cashier');
        $caps = array(
            'use_tekraerpos',
            'manage_tekraerpos',
            'view_tekraerpos_transactions',
            'manage_tekraerpos_tenants',
            'view_tekraerpos_reports',
        );
        
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($caps as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
        
        remove_role('tekraerpos_cashier');
    }
}
