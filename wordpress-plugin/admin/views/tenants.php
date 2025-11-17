<?php
/**
 * Tenants Management View
 */
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1>
        <?php _e('Tenant Management', 'erp-pos'); ?>
        <button type="button" class="button button-primary" onclick="showAddTenantModal()" style="margin-left: 10px;">
            <?php _e('Add New Tenant', 'erp-pos'); ?>
        </button>
    </h1>
    
    <?php if (isset($_GET['created'])): ?>
        <div class="notice notice-success is-dismissible"><p><?php _e('Tenant created successfully!', 'erp-pos'); ?></p></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible"><p><?php _e('Tenant deleted successfully!', 'erp-pos'); ?></p></div>
    <?php endif; ?>
    
    <!-- Tenants Grid -->
    <div class="erp-pos-tenants-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
        <?php foreach ($tenants as $tenant): ?>
            <div class="erp-pos-tenant-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                    <div>
                        <h3 style="margin: 0 0 5px 0; font-size: 18px;"><?php echo esc_html($tenant->name); ?></h3>
                        <span style="color: #666; font-size: 12px;"><?php echo esc_html($tenant->code); ?></span>
                    </div>
                    <span class="erp-pos-status erp-pos-status-<?php echo esc_attr($tenant->status); ?>">
                        <?php echo esc_html(ucfirst($tenant->status)); ?>
                    </span>
                </div>
                
                <div style="margin: 10px 0; padding: 10px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
                    <div style="display: flex; justify-content: space-between; margin: 5px 0;">
                        <span style="color: #666;"><?php _e('Transactions:', 'erp-pos'); ?></span>
                        <strong><?php echo number_format($tenant->transaction_count); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin: 5px 0;">
                        <span style="color: #666;"><?php _e('Total Sales:', 'erp-pos'); ?></span>
                        <strong><?php echo wc_price($tenant->total_sales ?: 0); ?></strong>
                    </div>
                </div>
                
                <div style="font-size: 12px; color: #666; margin: 10px 0;">
                    <?php if ($tenant->address): ?>
                        <div style="margin: 5px 0;">
                            <span class="dashicons dashicons-location" style="font-size: 14px;"></span>
                            <?php echo esc_html($tenant->address); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($tenant->phone): ?>
                        <div style="margin: 5px 0;">
                            <span class="dashicons dashicons-phone" style="font-size: 14px;"></span>
                            <?php echo esc_html($tenant->phone); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($tenant->email): ?>
                        <div style="margin: 5px 0;">
                            <span class="dashicons dashicons-email" style="font-size: 14px;"></span>
                            <?php echo esc_html($tenant->email); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <button type="button" class="button" onclick="editTenant(<?php echo $tenant->id; ?>)">
                        <?php _e('Edit', 'erp-pos'); ?>
                    </button>
                    <button type="button" class="button" onclick="viewTenantSettings(<?php echo $tenant->id; ?>)">
                        <?php _e('Settings', 'erp-pos'); ?>
                    </button>
                    <?php if ($tenant->status === 'active'): ?>
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="margin: 0;" onsubmit="return confirm('<?php _e('Are you sure?', 'erp-pos'); ?>')">
                            <?php wp_nonce_field('erp_pos_delete_tenant'); ?>
                            <input type="hidden" name="action" value="erp_pos_delete_tenant">
                            <input type="hidden" name="tenant_id" value="<?php echo $tenant->id; ?>">
                            <button type="submit" class="button" style="color: #d63638;">
                                <?php _e('Delete', 'erp-pos'); ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Tenant Modal -->
<div id="add-tenant-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999999; align-items: center; justify-content: center;">
    <div style="background: #fff; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; border-radius: 8px; padding: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0;"><?php _e('Add New Tenant', 'erp-pos'); ?></h2>
            <button type="button" class="button" onclick="closeAddTenantModal()">&times;</button>
        </div>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('erp_pos_create_tenant'); ?>
            <input type="hidden" name="action" value="erp_pos_create_tenant">
            
            <table class="form-table">
                <tr>
                    <th><label for="tenant_name"><?php _e('Tenant Name', 'erp-pos'); ?> *</label></th>
                    <td><input type="text" name="tenant_name" id="tenant_name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="tenant_code"><?php _e('Tenant Code', 'erp-pos'); ?> *</label></th>
                    <td><input type="text" name="tenant_code" id="tenant_code" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="tenant_address"><?php _e('Address', 'erp-pos'); ?></label></th>
                    <td><textarea name="tenant_address" id="tenant_address" class="large-text" rows="3"></textarea></td>
                </tr>
                <tr>
                    <th><label for="tenant_phone"><?php _e('Phone', 'erp-pos'); ?></label></th>
                    <td><input type="text" name="tenant_phone" id="tenant_phone" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="tenant_email"><?php _e('Email', 'erp-pos'); ?></label></th>
                    <td><input type="email" name="tenant_email" id="tenant_email" class="regular-text"></td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="button button-primary"><?php _e('Create Tenant', 'erp-pos'); ?></button>
                <button type="button" class="button" onclick="closeAddTenantModal()"><?php _e('Cancel', 'erp-pos'); ?></button>
            </p>
        </form>
    </div>
</div>

<script>
function showAddTenantModal() {
    document.getElementById('add-tenant-modal').style.display = 'flex';
}

function closeAddTenantModal() {
    document.getElementById('add-tenant-modal').style.display = 'none';
}

function editTenant(id) {
    alert('Edit tenant ' + id + ' - Feature coming soon!');
}

function viewTenantSettings(id) {
    alert('Tenant settings ' + id + ' - Feature coming soon!');
}
</script>

<style>
.erp-pos-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}
.erp-pos-status-active { background: #d4edda; color: #155724; }
.erp-pos-status-inactive { background: #f8d7da; color: #721c24; }
</style>