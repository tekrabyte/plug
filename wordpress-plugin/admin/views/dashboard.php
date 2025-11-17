<?php
/**
 * Dashboard View
 */
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1><?php _e('ERP POS Dashboard', 'erp-pos'); ?></h1>
    
    <!-- Stats Cards -->
    <div class="erp-pos-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
        <div class="erp-pos-stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="color: #666; font-size: 14px; margin-bottom: 5px;"><?php _e('Today Sales', 'erp-pos'); ?></div>
            <div style="font-size: 28px; font-weight: bold; color: #2271b1;">
                <?php echo wc_price($today_sales ?: 0); ?>
            </div>
            <div style="color: #666; font-size: 12px; margin-top: 5px;">
                <?php printf(__('%d transactions', 'erp-pos'), $today_transactions); ?>
            </div>
        </div>
        
        <div class="erp-pos-stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="color: #666; font-size: 14px; margin-bottom: 5px;"><?php _e('This Month', 'erp-pos'); ?></div>
            <div style="font-size: 28px; font-weight: bold; color: #00a32a;">
                <?php echo wc_price($month_sales ?: 0); ?>
            </div>
            <div style="color: #666; font-size: 12px; margin-top: 5px;">
                <?php echo date('F Y'); ?>
            </div>
        </div>
        
        <div class="erp-pos-stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="color: #666; font-size: 14px; margin-bottom: 5px;"><?php _e('Avg. Transaction', 'erp-pos'); ?></div>
            <div style="font-size: 28px; font-weight: bold; color: #d63638;">
                <?php 
                $avg = $today_transactions > 0 ? $today_sales / $today_transactions : 0;
                echo wc_price($avg);
                ?>
            </div>
            <div style="color: #666; font-size: 12px; margin-top: 5px;"><?php _e('Today', 'erp-pos'); ?></div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin: 20px 0;">
        <!-- Recent Transactions -->
        <div class="erp-pos-panel" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0;"><?php _e('Recent Transactions', 'erp-pos'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Transaction #', 'erp-pos'); ?></th>
                        <th><?php _e('Date', 'erp-pos'); ?></th>
                        <th><?php _e('Customer', 'erp-pos'); ?></th>
                        <th><?php _e('Total', 'erp-pos'); ?></th>
                        <th><?php _e('Status', 'erp-pos'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_transactions)): ?>
                        <?php foreach ($recent_transactions as $transaction): ?>
                            <tr>
                                <td><strong><?php echo esc_html($transaction->transaction_number); ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($transaction->created_at)); ?></td>
                                <td><?php echo esc_html($transaction->customer_name ?: '-'); ?></td>
                                <td><?php echo wc_price($transaction->total); ?></td>
                                <td>
                                    <span class="erp-pos-status erp-pos-status-<?php echo esc_attr($transaction->status); ?>">
                                        <?php echo esc_html(ucfirst($transaction->status)); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: #666;">
                                <?php _e('No transactions yet', 'erp-pos'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <p style="text-align: right; margin-top: 10px;">
                <a href="<?php echo admin_url('admin.php?page=erp-pos-transactions'); ?>" class="button">
                    <?php _e('View All Transactions', 'erp-pos'); ?>
                </a>
            </p>
        </div>
        
        <!-- Top Products -->
        <div class="erp-pos-panel" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0;"><?php _e('Top Products', 'erp-pos'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Product', 'erp-pos'); ?></th>
                        <th><?php _e('Sold', 'erp-pos'); ?></th>
                        <th><?php _e('Sales', 'erp-pos'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($top_products)): ?>
                        <?php foreach (array_slice($top_products, 0, 5) as $product): ?>
                            <tr>
                                <td><?php echo esc_html($product->product_name); ?></td>
                                <td><?php echo intval($product->total_qty); ?></td>
                                <td><?php echo wc_price($product->total_sales); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 20px; color: #666;">
                                <?php _e('No data', 'erp-pos'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

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