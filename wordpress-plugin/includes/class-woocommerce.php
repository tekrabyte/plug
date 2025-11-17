<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce integration class
 */
class TEKRAERPOS_WooCommerce {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Hooks
    }
    
    /**
     * Get products for POS
     */
    public static function get_products($args = array()) {
        $defaults = array(
            'status' => 'publish',
            'limit' => -1,
            'orderby' => 'name',
            'order' => 'ASC',
        );
        
        $args = wp_parse_args($args, $defaults);
        $products = wc_get_products($args);
        $result = array();
        
        foreach ($products as $product) {
            if ($product->is_type('variable')) {
                // Get all variations
                $variations = $product->get_available_variations();
                foreach ($variations as $variation) {
                    $variation_obj = wc_get_product($variation['variation_id']);
                    $result[] = self::format_product_data($variation_obj, $product);
                }
            } else {
                $result[] = self::format_product_data($product);
            }
        }
        
        return $result;
    }
    
    /**
     * Format product data for POS
     */
    private static function format_product_data($product, $parent = null) {
        $data = array(
            'id' => $product->get_id(),
            'parent_id' => $parent ? $parent->get_id() : 0,
            'name' => $product->get_name(),
            'sku' => $product->get_sku(),
            'price' => (float) $product->get_price(),
            'regular_price' => (float) $product->get_regular_price(),
            'sale_price' => $product->get_sale_price() ? (float) $product->get_sale_price() : null,
            'stock_quantity' => $product->get_stock_quantity(),
            'stock_status' => $product->get_stock_status(),
            'manage_stock' => $product->get_manage_stock(),
            'backorders' => $product->get_backorders(),
            'type' => $product->get_type(),
            'image' => wp_get_attachment_url($product->get_image_id()),
            'categories' => array(),
            'attributes' => array(),
        );
        
        // Get categories
        $category_ids = $parent ? $parent->get_category_ids() : $product->get_category_ids();
        foreach ($category_ids as $cat_id) {
            $term = get_term($cat_id, 'product_cat');
            if ($term && !is_wp_error($term)) {
                $data['categories'][] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                );
            }
        }
        
        // Get variation attributes
        if ($product->is_type('variation')) {
            $attributes = $product->get_variation_attributes();
            foreach ($attributes as $key => $value) {
                $data['attributes'][] = array(
                    'name' => str_replace('attribute_', '', $key),
                    'value' => $value,
                );
            }
            $data['variant_sku'] = $product->get_sku() ?: $parent->get_sku() . '-' . $product->get_id();
        } else {
            $data['variant_sku'] = $product->get_sku() ?: 'product-' . $product->get_id();
        }
        
        return $data;
    }
    
    /**
     * Get product categories
     */
    public static function get_categories() {
        $terms = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
        ));
        
        $result = array();
        foreach ($terms as $term) {
            $result[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'count' => $term->count,
                'parent' => $term->parent,
            );
        }
        
        return $result;
    }
    
    /**
     * Create WooCommerce order from POS
     */
    public static function create_order($data) {
        try {
            // Create order
            $order = wc_create_order();
            
            if (is_wp_error($order)) {
                throw new Exception($order->get_error_message());
            }
            
            // Add items
            foreach ($data['items'] as $item) {
                $product = wc_get_product($item['variant_id']);
                if (!$product) {
                    throw new Exception('Product not found: ' . $item['variant_id']);
                }
                
                // Check stock
                if ($product->managing_stock() && !$product->has_enough_stock($item['qty'])) {
                    throw new Exception('Insufficient stock for: ' . $product->get_name());
                }
                
                $order->add_product($product, $item['qty'], array(
                    'subtotal' => $item['price'] * $item['qty'],
                    'total' => $item['price'] * $item['qty'],
                ));
            }
            
            // Set order details
            $order->set_customer_id(get_current_user_id());
            $order->set_created_via('tekraerpos');
            
            // Add meta data
            $order->add_meta_data('_tekraerpos_transaction', true);
            $order->add_meta_data('_tekraerpos_tenant_id', $data['tenant_id']);
            $order->add_meta_data('_tekraerpos_transaction_type', $data['type'] ?? 'pos');
            if (!empty($data['payment_method'])) {
                $order->set_payment_method($data['payment_method']);
            }
            
            // Calculate totals
            $order->calculate_totals();
            
            // Set status
            $order->set_status('completed');
            
            // Save order
            $order->save();
            
            // Reduce stock
            wc_reduce_stock_levels($order->get_id());
            
            return array(
                'success' => true,
                'order_id' => $order->get_id(),
                'order' => $order,
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage(),
            );
        }
    }
    
    /**
     * Update product stock
     */
    public static function update_stock($product_id, $quantity, $operation = 'reduce') {
        $product = wc_get_product($product_id);
        if (!$product || !$product->managing_stock()) {
            return false;
        }
        
        if ($operation === 'reduce') {
            $new_stock = $product->get_stock_quantity() - $quantity;
        } else {
            $new_stock = $product->get_stock_quantity() + $quantity;
        }
        
        $product->set_stock_quantity($new_stock);
        $product->save();
        
        return true;
    }
    
    /**
     * Get product by barcode/SKU
     */
    public static function get_product_by_barcode($barcode) {
        // Try to find by SKU
        $product_id = wc_get_product_id_by_sku($barcode);
        
        if ($product_id) {
            $product = wc_get_product($product_id);
            return self::format_product_data($product);
        }
        
        // Try to find by custom barcode meta
        $args = array(
            'post_type' => array('product', 'product_variation'),
            'meta_key' => '_barcode',
            'meta_value' => $barcode,
            'posts_per_page' => 1,
        );
        
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            $post = $query->posts[0];
            $product = wc_get_product($post->ID);
            return self::format_product_data($product);
        }
        
        return null;
    }
}
