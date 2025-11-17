<?php
/**
 * Reports & Analytics View
 */
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1><?php _e('Reports & Analytics', 'erp-pos'); ?></h1>
    
    <!-- Date Range Filter -->
    <div class="erp-pos-filters" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0;">
        <form method="get" action="">
            <input type="hidden" name="page" value="erp-pos-reports">
            
            <div style="display: flex; gap: 15px; align-items: end;">
                <div>
                    <label><?php _e('Start Date', 'erp-pos'); ?></label>
                    <input type="date" name="start_date" value="<?php echo esc_attr($start_date); ?>" class="regular-text">
                </div>
                
                <div>
                    <label><?php _e('End Date', 'erp-pos'); ?></label>
                    <input type="date" name="end_date" value="<?php echo esc_attr($end_date); ?>" class="regular-text">
                </div>
                
                <div>
                    <button type="submit" class="button button-primary"><?php _e('Generate Report', 'erp-pos'); ?></button>
                    <button type="button" class="button" onclick="exportReport()"><?php _e('Export PDF', 'erp-pos'); ?></button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
        <?php
        $total_sales = array_sum(array_column($sales_data, 'total_sales'));
        $total_transactions = array_sum(array_column($sales_data, 'transactions'));
        $avg_transaction = $total_transactions > 0 ? $total_sales / $total_transactions : 0;
        ?>
        
        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="color: #666; font-size: 14px; margin-bottom: 5px;"><?php _e('Total Sales', 'erp-pos'); ?></div>
            <div style="font-size: 28px; font-weight: bold; color: #2271b1;">
                <?php echo wc_price($total_sales); ?>
            </div>
        </div>
        
        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="color: #666; font-size: 14px; margin-bottom: 5px;"><?php _e('Total Transactions', 'erp-pos'); ?></div>
            <div style="font-size: 28px; font-weight: bold; color: #00a32a;">
                <?php echo number_format($total_transactions); ?>
            </div>
        </div>
        
        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="color: #666; font-size: 14px; margin-bottom: 5px;"><?php _e('Avg Transaction', 'erp-pos'); ?></div>
            <div style="font-size: 28px; font-weight: bold; color: #d63638;">
                <?php echo wc_price($avg_transaction); ?>
            </div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin: 20px 0;">
        <!-- Sales Chart -->
        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2><?php _e('Sales Trend', 'erp-pos'); ?></h2>
            <canvas id="sales-chart" width="600" height="300"></canvas>
        </div>
        
        <!-- Payment Methods -->
        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2><?php _e('Payment Methods', 'erp-pos'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Method', 'erp-pos'); ?></th>
                        <th><?php _e('Total', 'erp-pos'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payment_data)): ?>
                        <?php foreach ($payment_data as $payment): ?>
                            <tr>
                                <td><?php echo esc_html(ucfirst($payment->payment_method)); ?></td>
                                <td><?php echo wc_price($payment->total); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center; padding: 20px; color: #666;">
                                <?php _e('No data', 'erp-pos'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Top Products -->
    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0;">
        <h2><?php _e('Top Products', 'erp-pos'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Product', 'erp-pos'); ?></th>
                    <th><?php _e('Quantity Sold', 'erp-pos'); ?></th>
                    <th><?php _e('Total Sales', 'erp-pos'); ?></th>
                    <th><?php _e('Avg Price', 'erp-pos'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($top_products)): ?>
                    <?php foreach ($top_products as $product): ?>
                        <tr>
                            <td><strong><?php echo esc_html($product->product_name); ?></strong></td>
                            <td><?php echo number_format($product->total_qty); ?></td>
                            <td><?php echo wc_price($product->total_sales); ?></td>
                            <td><?php echo wc_price($product->total_sales / $product->total_qty); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px; color: #666;">
                            <?php _e('No products data', 'erp-pos'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Hourly Sales Pattern -->
    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0;">
        <h2><?php _e('Sales by Hour', 'erp-pos'); ?></h2>
        <canvas id="hourly-chart" width="800" height="200"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Sales Trend Chart
const salesData = <?php echo json_encode($sales_data); ?>;
const salesCtx = document.getElementById('sales-chart').getContext('2d');
const salesChart = new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: salesData.map(d => d.date),
        datasets: [{
            label: '<?php _e('Sales', 'erp-pos'); ?>',
            data: salesData.map(d => parseFloat(d.total_sales)),
            borderColor: '#2271b1',
            backgroundColor: 'rgba(34, 113, 177, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});

// Hourly Sales Chart
const hourlyData = <?php echo json_encode($hourly_sales); ?>;
const hourlyCtx = document.getElementById('hourly-chart').getContext('2d');
const hourlyChart = new Chart(hourlyCtx, {
    type: 'bar',
    data: {
        labels: Array.from({length: 24}, (_, i) => i + ':00'),
        datasets: [{
            label: '<?php _e('Transactions', 'erp-pos'); ?>',
            data: Array.from({length: 24}, (_, i) => {
                const found = hourlyData.find(d => parseInt(d.hour) === i);
                return found ? parseInt(found.transactions) : 0;
            }),
            backgroundColor: '#00a32a'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

function exportReport() {
    alert('Export PDF feature - Coming soon!');
}
</script>