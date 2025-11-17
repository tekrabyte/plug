<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API endpoints
 */
class TEKRAERPOS_API {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    public function register_routes() {
        $namespace = 'tekraerpos/v1';
        
        // Products endpoint
        register_rest_route($namespace, '/products', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_products'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Product by barcode
        register_rest_route($namespace, '/product/barcode/(?P<barcode>[\w-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_product_by_barcode'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Categories endpoint
        register_rest_route($namespace, '/categories', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_categories'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Create order endpoint
        register_rest_route($namespace, '/order/create', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_order'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Transactions endpoint
        register_rest_route($namespace, '/transactions', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_transactions'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Single transaction
        register_rest_route($namespace, '/transaction/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_transaction'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Tenants endpoint
        register_rest_route($namespace, '/tenants', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_tenants'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Payment methods
        register_rest_route($namespace, '/payment-methods', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_payment_methods'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Receipt
        register_rest_route($namespace, '/receipt/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_receipt'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Reports
        register_rest_route($namespace, '/reports/sales', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_sales_report'),
            'permission_callback' => array($this, 'check_permission'),
        ));
    }
    
    public function check_permission() {
        return current_user_can('use_tekraerpos');
    }
    
    public function check_admin_permission() {
        return current_user_can('manage_tekraerpos');
    }
    
    public function get_products($request) {
        $search = $request->get_param('search');
        $category = $request->get_param('category');
        
        $args = array();
        
        if ($search) {
            $args['s'] = sanitize_text_field($search);
        }
        
        if ($category) {
            $args['category'] = array(sanitize_text_field($category));
        }
        
        $products = TEKRAERPOS_WooCommerce::get_products($args);
        
        return new WP_REST_Response($products, 200);
    }
    
    public function get_product_by_barcode($request) {
        $barcode = sanitize_text_field($request['barcode']);
        $product = TEKRAERPOS_WooCommerce::get_product_by_barcode($barcode);
        
        if ($product) {
            return new WP_REST_Response($product, 200);
        }
        
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'Product not found'
        ), 404);
    }
    
    public function get_categories($request) {
        $categories = TEKRAERPOS_WooCommerce::get_categories();
        return new WP_REST_Response($categories, 200);
    }
    
    public function create_order($request) {
        global $wpdb;
        
        $data = $request->get_json_params();
        
        // Validate data
        if (empty($data['items']) || empty($data['tenant_id'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Invalid order data'
            ), 400);
        }
        
        // Create WooCommerce order
        $result = TEKRAERPOS_WooCommerce::create_order($data);
        
        if (!$result['success']) {
            return new WP_REST_Response($result, 400);
        }
        
        $order = $result['order'];
        
        // Generate receipt number
        $receipt_number = 'RCP-' . date('Ymd') . '-' . str_pad($order->get_id(), 6, '0', STR_PAD_LEFT);
        
        // Save transaction to database
        $table_transactions = $wpdb->prefix . 'tekraerpos_transactions';
        $wpdb->insert(
            $table_transactions,
            array(
                'order_id' => $order->get_id(),
                'tenant_id' => $data['tenant_id'],
                'user_id' => get_current_user_id(),
                'transaction_type' => $data['type'] ?? 'pos',
                'subtotal' => $data['subtotal'],
                'tax' => $data['tax'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'total' => $data['total'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'payment_status' => 'completed',
                'notes' => $data['notes'] ?? '',
                'receipt_number' => $receipt_number,
            )
        );
        
        $transaction_id = $wpdb->insert_id;
        
        // Save transaction items
        $table_items = $wpdb->prefix . 'tekraerpos_transaction_items';
        foreach ($data['items'] as $item) {
            $product = wc_get_product($item['variant_id']);
            $wpdb->insert(
                $table_items,
                array(
                    'transaction_id' => $transaction_id,
                    'product_id' => $product->get_parent_id() ?: $item['variant_id'],
                    'variation_id' => $product->is_type('variation') ? $item['variant_id'] : 0,
                    'product_name' => $product->get_name(),
                    'sku' => $product->get_sku(),
                    'price' => $item['price'],
                    'quantity' => $item['qty'],
                    'subtotal' => $item['price'] * $item['qty'],
                )
            );
        }
        
        // Save payment
        if (!empty($data['payment_method'])) {
            $table_payments = $wpdb->prefix . 'erp_payments';
            $wpdb->insert(
                $table_payments,
                array(
                    'transaction_id' => $transaction_id,
                    'payment_method' => $data['payment_method'],
                    'amount' => $data['total'],
                    'status' => 'completed',
                )
            );
        }
        
        // Generate receipt HTML
        $receipt_html = $this->generate_receipt_html($transaction_id, $order);
        
        return new WP_REST_Response(array(
            'success' => true,
            'order_id' => $order->get_id(),
            'transaction_id' => $transaction_id,
            'receipt_number' => $receipt_number,
            'receipt_html' => $receipt_html,
        ), 200);
    }
    
    public function get_transactions($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'erp_transactions';
        
        $tenant_id = $request->get_param('tenant_id');
        $start_date = $request->get_param('start_date');
        $end_date = $request->get_param('end_date');
        $limit = $request->get_param('limit') ?? 50;
        $offset = $request->get_param('offset') ?? 0;
        
        $where = array('1=1');
        
        if ($tenant_id) {
            $where[] = $wpdb->prepare('tenant_id = %d', $tenant_id);
        }
        
        if ($start_date) {
            $where[] = $wpdb->prepare('DATE(created_at) >= %s', $start_date);
        }
        
        if ($end_date) {
            $where[] = $wpdb->prepare('DATE(created_at) <= %s', $end_date);
        }
        
        $where_sql = implode(' AND ', $where);
        
        $transactions = $wpdb->get_results(
            "SELECT * FROM $table WHERE $where_sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset"
        );
        
        return new WP_REST_Response($transactions, 200);
    }
    
    public function get_transaction($request) {
        global $wpdb;
        $id = (int) $request['id'];
        
        $table_transactions = $wpdb->prefix . 'erp_transactions';
        $table_items = $wpdb->prefix . 'erp_transaction_items';
        
        $transaction = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_transactions WHERE id = %d",
            $id
        ));
        
        if (!$transaction) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Transaction not found'
            ), 404);
        }
        
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_items WHERE transaction_id = %d",
            $id
        ));
        
        $transaction->items = $items;
        
        return new WP_REST_Response($transaction, 200);
    }
    
    public function get_tenants($request) {
        $tenants = ERP_POS_Tenant::get_all_tenants();
        return new WP_REST_Response($tenants, 200);
    }
    
    public function get_payment_methods($request) {
        $methods = array(
            array('id' => 'cash', 'name' => 'Cash', 'icon' => 'money-bill'),
            array('id' => 'card', 'name' => 'Credit/Debit Card', 'icon' => 'credit-card'),
            array('id' => 'qris', 'name' => 'QRIS', 'icon' => 'qrcode'),
            array('id' => 'transfer', 'name' => 'Bank Transfer', 'icon' => 'university'),
            array('id' => 'ewallet', 'name' => 'E-Wallet', 'icon' => 'wallet'),
        );
        
        return new WP_REST_Response($methods, 200);
    }
    
    public function get_receipt($request) {
        $id = (int) $request['id'];
        
        global $wpdb;
        $table = $wpdb->prefix . 'erp_transactions';
        $transaction = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
        
        if (!$transaction) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Transaction not found'
            ), 404);
        }
        
        $order = wc_get_order($transaction->order_id);
        $receipt_html = $this->generate_receipt_html($id, $order);
        
        return new WP_REST_Response(array(
            'success' => true,
            'html' => $receipt_html,
        ), 200);
    }
    
    public function get_sales_report($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'erp_transactions';
        
        $tenant_id = $request->get_param('tenant_id');
        $start_date = $request->get_param('start_date') ?? date('Y-m-d', strtotime('-30 days'));
        $end_date = $request->get_param('end_date') ?? date('Y-m-d');
        
        $where = array('1=1');
        
        if ($tenant_id) {
            $where[] = $wpdb->prepare('tenant_id = %d', $tenant_id);
        }
        
        $where[] = $wpdb->prepare('DATE(created_at) >= %s', $start_date);
        $where[] = $wpdb->prepare('DATE(created_at) <= %s', $end_date);
        $where_sql = implode(' AND ', $where);
        
        $stats = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total_transactions,
                SUM(total) as total_sales,
                AVG(total) as average_sale,
                SUM(subtotal) as total_subtotal,
                SUM(tax) as total_tax,
                SUM(discount) as total_discount
            FROM $table 
            WHERE $where_sql"
        );
        
        $daily_sales = $wpdb->get_results(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as transactions,
                SUM(total) as total
            FROM $table 
            WHERE $where_sql
            GROUP BY DATE(created_at)
            ORDER BY date ASC"
        );
        
        return new WP_REST_Response(array(
            'stats' => $stats,
            'daily_sales' => $daily_sales,
        ), 200);
    }
    
    private function generate_receipt_html($transaction_id, $order) {
        global $wpdb;
        
        $table_transactions = $wpdb->prefix . 'erp_transactions';
        $table_items = $wpdb->prefix . 'erp_transaction_items';
        
        $transaction = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_transactions WHERE id = %d",
            $transaction_id
        ));
        
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_items WHERE transaction_id = %d",
            $transaction_id
        ));
        
        $tenant = ERP_POS_Tenant::get_tenant($transaction->tenant_id);
        $user = get_userdata($transaction->user_id);
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Receipt #<?php echo esc_html($transaction->receipt_number); ?></title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; max-width: 300px; margin: 0 auto; padding: 10px; }
                h2, h3 { margin: 5px 0; text-align: center; }
                .info { text-align: center; margin-bottom: 15px; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { padding: 5px; text-align: left; }
                .text-right { text-align: right; }
                .border-top { border-top: 1px dashed #000; padding-top: 10px; }
                .total { font-weight: bold; font-size: 14px; }
                .footer { text-align: center; margin-top: 20px; font-size: 11px; }
            </style>
        </head>
        <body>
            <h2><?php echo esc_html($tenant->name ?? 'ERP POS'); ?></h2>
            <div class="info">
                <?php if (!empty($tenant->address)) : ?>
                    <div><?php echo esc_html($tenant->address); ?></div>
                <?php endif; ?>
                <?php if (!empty($tenant->phone)) : ?>
                    <div>Tel: <?php echo esc_html($tenant->phone); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="info">
                <strong>Receipt: <?php echo esc_html($transaction->receipt_number); ?></strong><br>
                Date: <?php echo date('d/m/Y H:i', strtotime($transaction->created_at)); ?><br>
                Cashier: <?php echo esc_html($user->display_name); ?>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) : ?>
                    <tr>
                        <td><?php echo esc_html($item->product_name); ?></td>
                        <td class="text-right"><?php echo esc_html($item->quantity); ?></td>
                        <td class="text-right"><?php echo number_format($item->price, 0, ',', '.'); ?></td>
                        <td class="text-right"><?php echo number_format($item->subtotal, 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <table class="border-top">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right"><?php echo number_format($transaction->subtotal, 0, ',', '.'); ?></td>
                </tr>
                <?php if ($transaction->discount > 0) : ?>
                <tr>
                    <td>Discount:</td>
                    <td class="text-right">-<?php echo number_format($transaction->discount, 0, ',', '.'); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($transaction->tax > 0) : ?>
                <tr>
                    <td>Tax:</td>
                    <td class="text-right"><?php echo number_format($transaction->tax, 0, ',', '.'); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total">
                    <td>Total:</td>
                    <td class="text-right"><?php echo number_format($transaction->total, 0, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>Payment:</td>
                    <td class="text-right"><?php echo ucfirst($transaction->payment_method); ?></td>
                </tr>
            </table>
            
            <div class="footer">
                Thank you for your purchase!<br>
                Powered by ERP POS System
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}