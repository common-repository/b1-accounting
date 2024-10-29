<?php

/**
 * @since 2.0.0
 * @package B1_Accounting
 * @subpackage B1_Accounting/includes
 * @author B1.lt <info@b1.lt>
 * @link https://www.b1.lt
 */
class B1_Accounting_Shop_Item
{

    public static function set_null_reference_id_product()
    {
        global $wpdb;
        $sql = "
        UPDATE {$wpdb->prefix}posts
        SET `b1_reference_id` = NULL
        WHERE `post_type` = 'product' OR `post_type` = 'product_variation'";
        $wpdb->query($sql);
    }

    public static function get_items_sku()
    {
        global $wpdb;
        $sql = "
        SELECT * FROM {$wpdb->prefix}postmeta
        WHERE `meta_key` = '_sku'";

        return $wpdb->get_results($sql);

    }

    /**
     * @param int $b1_item_id
     * @param string $code
     * @param null $quantity
     * @param string $price
     * @param null $name
     */
    public static function update_data_using_b1_item_id($b1_item_id, $code, $quantity = null, $price = null, $name = null)
    {
        $id = wc_get_product_id_by_sku($code);
        if ($id) {
            global $wpdb;
            $sql = "
       UPDATE {$wpdb->prefix}posts p
       LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = p.ID
       SET `b1_reference_id` = %d
       WHERE `meta_key` = '_sku' AND `meta_value` = %s";
            $query = $wpdb->prepare($sql, $b1_item_id, $code);
            $wpdb->query($query);

            if (!is_null($quantity )){
                if ($quantity >= 1) {
                    update_post_meta($id, '_stock', $quantity);
                    update_post_meta($id, '_stock_status', 'instock');
                } else if ($quantity == 0) {
                    // Don't change status if item in backorder
                    $currentStockStatus = get_post_meta($id, '_stock_status', true);
                    if($currentStockStatus != 'onbackorder') {
                        update_post_meta($id, '_stock', $quantity);
                        update_post_meta($id, '_stock_status', 'outofstock');
                    }

                    $product = wc_get_product($id);
                    if($product->backorders_allowed()) { // if allow backorders
                        update_post_meta($id, '_stock', $quantity);
                        update_post_meta($id, '_stock_status', 'onbackorder');
                    }
                }
            }

            if ($price) {
                update_post_meta($id, '_price', (float)$price);
                update_post_meta($id, '_regular_price', (float)$price);
            }
            if ($name) {
                wp_update_post([
                    'ID' => $id,
                    'post_title' => $name,
                ]);
            }
        }
    }

    /**
     * @param string $itemId
     * @param int $b1_item_id
     */
    public static function update_code_using_item_id($b1_item_id, $itemId)
    {
        global $wpdb;
        $sql = "
        UPDATE `{$wpdb->prefix}posts` p
        LEFT JOIN `{$wpdb->prefix}postmeta` pm ON pm.post_id = p.ID
        SET b1_reference_id = %d
        WHERE p.id = " . $itemId . " or pm.post_id = " . $itemId;
        $query = $wpdb->prepare($sql, $b1_item_id, (integer)$itemId, (integer)$itemId);
        $wpdb->query($query);
    }

    /**
     * @param int $quantity
     * @param int $b1_item_id
     */
    public static function update_quantity_using_b1_item_id($quantity, $b1_item_id)
    {
        global $wpdb;
        if (is_null($quantity)) $quantity = 0;
        $sql = "
        UPDATE {$wpdb->prefix}postmeta 
        LEFT JOIN {$wpdb->prefix}posts ON post_id = ID
        SET `meta_value` = %d
        WHERE `meta_key` = '_stock' AND `b1_reference_id` = %d";
        $query = $wpdb->prepare($sql, $quantity, $b1_item_id);
        $wpdb->get_results($query);
    }

    /**
     * @param int $post_id
     *
     * @return int
     */
    public static function get_b1_item_id($post_id)
    {
        global $wpdb;
        $sql = "SELECT b1_product_id FROM {$wpdb->prefix}b1_item_links WHERE `shop_product_id` = %d";
        $query = $wpdb->prepare($sql, $post_id);
        $data = $wpdb->get_var($query);
        if ($data === null) {
            return 0;
        } else {
            return intval($data);
        }
    }

    public static function get_product_reference_id($product_id)
    {
        global $wpdb;
        $sql = "SELECT b1_reference_id FROM {$wpdb->prefix}posts WHERE `ID` = %d";
        $query = $wpdb->prepare($sql, $product_id);

        return $wpdb->get_var($query);
    }

    public static function get_variation_reference_id($variationId)
    {
        global $wpdb;
        $sql = "SELECT b1_reference_id FROM {$wpdb->prefix}posts WHERE `ID` = %d";
        $query = $wpdb->prepare($sql, $variationId);

        return $wpdb->get_var($query);
    }

    public static function fetch_all_items($iteration)
    {
        global $wpdb;
        $sql = "SELECT * 
                FROM {$wpdb->prefix}posts p
                LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = p.ID
                WHERE b1_reference_id IS NULL AND `meta_key` = '_sku' AND `meta_value` is not null AND `meta_value` != '' LIMIT %d ";
        $query = $wpdb->prepare($sql, $iteration);
        return $wpdb->get_results($query);
    }

    public static function fetch_all_items_without_sku()
    {
        global $wpdb;
        $sql = "SELECT * 
                FROM {$wpdb->prefix}posts p
                WHERE b1_reference_id IS NULL and (`post_type` = 'product' OR `post_type` = 'product_variation')
                AND p.ID not in (SELECT p1.ID 
                FROM {$wpdb->prefix}posts p1
                LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = p1.ID
                WHERE p1.b1_reference_id IS NULL AND pm.`meta_key` = '_sku' AND pm.`meta_value` is not null AND pm.`meta_value` != '')";
        $query = $wpdb->prepare($sql);
        return $wpdb->get_results($query);
    }

    public static function get_data_by_sql($sql, $itemId)
    {
        if (isset($sql) && !empty($sql)) {
            global $wpdb;
            $query = $wpdb->prepare($sql, $itemId);
            return $wpdb->get_var($query);
        } else {
            return false;
        }
    }

}
