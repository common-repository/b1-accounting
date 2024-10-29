<?php

/**
 * @since 2.0.0
 * @package B1_Accounting
 * @subpackage B1_Accounting/includes
 * @author B1.lt <info@b1.lt>
 * @link https://www.b1.lt
 */

class B1_Accounting_Order
{

    public static function assignB1OrderId($b1OrderId, $orderId)
    {
        global $wpdb;
        if (!$b1OrderId) {
            $sql = "UPDATE {$wpdb->prefix}posts SET b1_reference_id = NULL WHERE ID = %d";
            $query = $wpdb->prepare($sql, $orderId);
        } else {
            $sql = "UPDATE {$wpdb->prefix}posts SET b1_reference_id = %d WHERE ID = %d";
            $query = $wpdb->prepare($sql, $b1OrderId, $orderId);
        }
        $wpdb->query($query);
    }

    public static function fetch_all_orders($sync_from, $sync_order_status, $iteration, $sync_to = null)
    {
        global $wpdb;
        if(!empty($sync_to) && $sync_to !== '') {
            $sql = "SELECT p.* FROM {$wpdb->prefix}posts p inner join {$wpdb->prefix}postmeta pm on pm.post_id=p.ID and pm.meta_key='_date_completed' WHERE p.b1_reference_id IS NULL AND p.post_type = 'shop_order' AND p.post_status = '$sync_order_status'  AND p.post_date >= %s AND p.post_date <= %s order by pm.meta_value LIMIT %d";
            $query = $wpdb->prepare($sql, $sync_from, $sync_to, $iteration);
        } else {
            $sql = "SELECT p.* FROM {$wpdb->prefix}posts p inner join {$wpdb->prefix}postmeta pm on pm.post_id=p.ID and pm.meta_key='_date_completed' WHERE p.b1_reference_id IS NULL AND p.post_type = 'shop_order' AND p.post_status = '$sync_order_status'  AND p.post_date >= %s order by pm.meta_value LIMIT %d";
            $query = $wpdb->prepare($sql, $sync_from, $iteration);
        }
        return $wpdb->get_results($query);
    }

    public static function reset_order_b1_reference_id()
    {
        global $wpdb;
        $sql = "
            UPDATE {$wpdb->prefix}posts
            SET `b1_reference_id` = NULL
            WHERE `post_type` = 'shop_order'";
        $wpdb->query($sql);
    }

    public static function get_data_by_sql($sql, $orderId)
    {
        if (isset($sql) && !empty($sql)) {
            global $wpdb;
            $query = $wpdb->prepare($sql, $orderId);
            return $wpdb->get_var($query);
        } else {
            return false;
        }
    }

    public static function get_product_order_id($orderId, $productId)
    {
        global $wpdb;
        $sql = "SELECT woi.order_item_id 
                FROM {$wpdb->prefix}woocommerce_order_items woi
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta woim on woi.order_item_id = woim.order_item_id
                WHERE woi.`order_id` = %d AND (woim.`meta_key` = '_product_id'  AND woim.`meta_value` = %d)";
        $query = $wpdb->prepare($sql, $orderId, $productId);
        return $wpdb->get_var($query);
    }

    public static function get_product_variation_order_id($orderId, $variationId)
    {
        global $wpdb;
        $sql = "SELECT woi.order_item_id 
                FROM {$wpdb->prefix}woocommerce_order_items woi
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta woim on woi.order_item_id = woim.order_item_id
                WHERE woi.`order_id` = %d AND (woim.`meta_key` = '_variation_id'  AND woim.`meta_value` = %d)";
        $query = $wpdb->prepare($sql, $orderId, $variationId);
        return $wpdb->get_var($query);
    }
}
