<?php
/**
 * Transactions View
 */
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1><?php _e('Transactions', 'erp-pos'); ?></h1>
    
    <!-- Filters -->
    <div class="erp-pos-filters" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0;">
        <form method="get" action="">
            <input type="hidden" name="page" value="erp-pos-transactions">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <label><?php _e('Start Date', 'erp-pos'); ?></label>
                    <input type="date" name="start_date" value="<?php echo esc_attr($_GET['start_date'] ?? ''); ?>" class="regular-text">
                </div>
                
                <div>
                    <label><?php _e('End Date', 'erp-pos'); ?></label>
                    <input type="date" name="end_date" value="<?php echo esc_attr($_GET['end_date'] ?? ''); ?>" class="regular-text">
                </div>
                
                <div>
                    <label><?php _e('Tenant', 'erp-pos'); ?></label>
                    <select name="tenant_id" class="regular-text">
                        <option value=""><?php _e('All Tenants', 'erp-pos'); ?></option>
                        <?php foreach ($tenants as $tenant): ?>
                            <option value="<?php echo esc_attr($tenant->id); ?>" <?php selected($_GET['tenant_id'] ?? '', $tenant->id); ?>>
                                <?php echo esc_html($tenant->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label><?php _e('Search', 'erp-pos'); ?></label>
                    <input type="text" name="search" value="<?php echo esc_attr($_GET['search'] ?? ''); ?>" placeholder="<?php _e('Transaction # or Customer', 'erp-pos'); ?>" class="regular-text">
                </div>
            </div>
            
            <div style="margin-top: 15px;">
                <button type="submit" class="button button-primary"><?php _e('Filter', 'erp-pos'); ?></button>
                <a href="<?php echo admin_url('admin.php?page=erp-pos-transactions'); ?>" class="button"><?php _e('Reset', 'erp-pos'); ?></a>
                <button type="button" class="button" onclick="exportTransactions()"><?php _e('Export CSV', 'erp-pos'); ?></button>
            </div>
        </form>
    </div>
    
    <!-- Transactions Table -->
    <div class="erp-pos-transactions" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Transaction #', 'erp-pos'); ?></th>
                    <th><?php _e('Date', 'erp-pos'); ?></th>
                    <th><?php _e('Tenant', 'erp-pos'); ?></th>
                    <th><?php _e('Customer', 'erp-pos'); ?></th>
                    <th><?php _e('Items', 'erp-pos'); ?></th>
                    <th><?php _e('Total', 'erp-pos'); ?></th>
                    <th><?php _e('Payment', 'erp-pos'); ?></th>
                    <th><?php _e('Status', 'erp-pos'); ?></th>
                    <th><?php _e('Actions', 'erp-pos'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><strong><?php echo esc_html($transaction->transaction_number); ?></strong></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($transaction->created_at)); ?></td>
                            <td><?php echo esc_html($transaction->tenant_id); ?></td>
                            <td><?php echo esc_html($transaction->customer_name ?: '-'); ?></td>
                            <td><?php echo intval($transaction->total_items); ?></td>
                            <td><?php echo wc_price($transaction->total); ?></td>
                            <td><?php echo esc_html($transaction->payment_method ?: 'Cash'); ?></td>
                            <td>
                                <span class="erp-pos-status erp-pos-status-<?php echo esc_attr($transaction->status); ?>">
                                    <?php echo esc_html(ucfirst($transaction->status)); ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="button button-small" onclick="viewTransaction(<?php echo esc_attr($transaction->id); ?>)">
                                    <?php _e('View', 'erp-pos'); ?>
                                </button>
                                <button type="button" class="button button-small" onclick="printReceipt(<?php echo esc_attr($transaction->id); ?>)">
                                    <?php _e('Print', 'erp-pos'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: #666;">
                            <?php _e('No transactions found', 'erp-pos'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($total_items > $per_page): ?>
            <div class="tablenav bottom" style="margin-top: 20px;">
                <div class="tablenav-pages">
                    <?php
                    $total_pages = ceil($total_items / $per_page);
                    $pagination = paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $total_pages,
                        'current' => $current_page
                    ));
                    echo $pagination;
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Transaction Detail Modal -->
<div id="transaction-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999999; align-items: center; justify-content: center;">
    <div style="background: #fff; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto; border-radius: 8px; padding: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0;"><?php _e('Transaction Details', 'erp-pos'); ?></h2>
            <button type="button" class="button" onclick="closeTransactionModal()">&times;</button>
        </div>
        <div id="transaction-detail-content"></div>
    </div>
</div>

<script>
function viewTransaction(id) {
    // Show modal
    document.getElementById('transaction-modal').style.display = 'flex';
    
    // Load transaction details via AJAX
    jQuery.post(ajaxurl, {
        action: 'erp_pos_get_transaction',
        transaction_id: id
    }, function(response) {
        if (response.success) {
            document.getElementById('transaction-detail-content').innerHTML = response.data.html;
        }
    });
}

function closeTransactionModal() {
    document.getElementById('transaction-modal').style.display = 'none';
}

function printReceipt(id) {
    window.open('<?php echo home_url('/wp-json/erp-pos/v1/receipt/'); ?>' + id, '_blank');
}

function exportTransactions() {
    let url = '<?php echo admin_url('admin-ajax.php?action=erp_pos_export_transactions'); ?>';
    const params = new URLSearchParams(window.location.search);
    window.location.href = url + '&' + params.toString();
}
</script>

<style>
.erp-pos-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}
.erp-pos-status-completed { background: #d4edda; color: #155724; }
.erp-pos-status-pending { background: #fff3cd; color: #856404; }
.erp-pos-status-cancelled { background: #f8d7da; color: #721c24; }
</style>