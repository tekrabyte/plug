<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Multi-tenant management class
 */
class ERP_POS_Tenant {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get tenant by ID
     */
    public static function get_tenant($tenant_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'erp_tenants';
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $tenant_id
        ));
    }
    
    /**
     * Get user's tenant
     */
    public static function get_user_tenant($user_id) {
        global $wpdb;
        $table_user_tenants = $wpdb->prefix . 'erp_user_tenants';
        $table_tenants = $wpdb->prefix . 'erp_tenants';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT t.* FROM $table_tenants t 
            INNER JOIN $table_user_tenants ut ON t.id = ut.tenant_id 
            WHERE ut.user_id = %d AND t.status = 'active' 
            LIMIT 1",
            $user_id
        ));
    }
    
    /**
     * Get all tenants
     */
    public static function get_all_tenants() {
        global $wpdb;
        $table = $wpdb->prefix . 'erp_tenants';
        return $wpdb->get_results("SELECT * FROM $table ORDER BY name ASC");
    }
    
    /**
     * Create new tenant
     */
    public static function create_tenant($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'erp_tenants';
        
        $slug = sanitize_title($data['name']);
        
        $result = $wpdb->insert(
            $table,
            array(
                'name' => sanitize_text_field($data['name']),
                'slug' => $slug,
                'status' => 'active',
                'address' => sanitize_textarea_field($data['address'] ?? ''),
                'phone' => sanitize_text_field($data['phone'] ?? ''),
                'email' => sanitize_email($data['email'] ?? ''),
                'tax_id' => sanitize_text_field($data['tax_id'] ?? ''),
            )
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        return false;
    }
    
    /**
     * Assign user to tenant
     */
    public static function assign_user_to_tenant($user_id, $tenant_id, $role = 'cashier') {
        global $wpdb;
        $table = $wpdb->prefix . 'erp_user_tenants';
        
        return $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'tenant_id' => $tenant_id,
                'role' => $role,
            )
        );
    }
    
    /**
     * Get tenant settings
     */
    public static function get_setting($tenant_id, $key, $default = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'erp_settings';
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM $table WHERE tenant_id = %d AND setting_key = %s",
            $tenant_id,
            $key
        ));
        
        return $value !== null ? maybe_unserialize($value) : $default;
    }
    
    /**
     * Update tenant settings
     */
    public static function update_setting($tenant_id, $key, $value) {
        global $wpdb;
        $table = $wpdb->prefix . 'erp_settings';
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE tenant_id = %d AND setting_key = %s",
            $tenant_id,
            $key
        ));
        
        $value = maybe_serialize($value);
        
        if ($existing) {
            return $wpdb->update(
                $table,
                array('setting_value' => $value),
                array('id' => $existing)
            );
        } else {
            return $wpdb->insert(
                $table,
                array(
                    'tenant_id' => $tenant_id,
                    'setting_key' => $key,
                    'setting_value' => $value,
                )
            );
        }
    }
}