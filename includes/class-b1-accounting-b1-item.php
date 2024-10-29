<?php

/**
 * @since 2.0.0
 * @package B1_Accounting
 * @subpackage B1_Accounting/includes
 * @author B1.lt <info@b1.lt>
 * @link https://www.b1.lt
 */
class B1_Accounting_B1_Item
{

    public static function import($name, $code, $id, $quantity)
    {
        global $wpdb;
        $query = $wpdb->prepare("UPDATE {$wpdb->prefix}b1_items SET `name` = %s, `code` = %s, `quantity` = %d, `is_ghost` = 0 WHERE `id` = %d", $name, $code, intval($quantity), intval($id));
        $wpdb->query($query);
        $query = $wpdb->prepare("INSERT IGNORE INTO {$wpdb->prefix}b1_items SET `name` = %s, `code` = %s, `quantity` = %d, `id` = %d, `is_ghost` = 0", $name, $code, intval($quantity), intval($id));
        $wpdb->query($query);
    }

    public static function mark_all_as_ghosts()
    {
        global $wpdb;
        $wpdb->query("UPDATE {$wpdb->prefix}b1_items SET `is_ghost` = 1 WHERE `is_ghost` = 0");
    }

    public static function delete_all_ghosts()
    {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->prefix}b1_items WHERE `is_ghost` = 1");
        $wpdb->query("DELETE FROM {$wpdb->prefix}b1_item_links WHERE `b1_product_id` NOT IN (SELECT id FROM {$wpdb->prefix}b1_items)");
    }

    public static function reset_all_links()
    {
        global $wpdb;
        $result = $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}b1_item_links");
        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }

    public static function count_all($search_link = null, $search_name = null, $search_code = null)
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) 
FROM {$wpdb->prefix}b1_items
WHERE name LIKE %s AND code LIKE %s";
        if ($search_link == '1') {
            $sql .= " AND id IN (SELECT DISTINCT b1_product_id FROM {$wpdb->prefix}b1_item_links)";
        } else if ($search_link == '0') {
            $sql .= " AND id NOT IN (SELECT DISTINCT b1_product_id FROM {$wpdb->prefix}b1_item_links)";
        }
        $search_name = '%' . $wpdb->esc_like($search_name) . '%';
        $search_code = '%' . $wpdb->esc_like($search_code) . '%';
        $data = $wpdb->get_var($wpdb->prepare($sql, $search_name, $search_code));
        if ($data === null) {
            return 0;
        } else {
            return intval($data);
        }
    }

    public static function fetch_all($from, $items, $search_link = null, $search_name = null, $search_code = null)
    {
        global $wpdb;
        $sql = "SELECT *, IF (EXISTS (SELECT 1 FROM {$wpdb->prefix}b1_item_links WHERE b1_product_id = id), 1,0) AS link 
FROM {$wpdb->prefix}b1_items
WHERE name LIKE %s AND code LIKE %s";
        if ($search_link == '1') {
            $sql .= " AND id IN (SELECT DISTINCT b1_product_id FROM {$wpdb->prefix}b1_item_links)";
        } else if ($search_link == '0') {
            $sql .= " AND id NOT IN (SELECT DISTINCT b1_product_id FROM {$wpdb->prefix}b1_item_links)";
        }
        $search_name = '%' . $wpdb->esc_like($search_name) . '%';
        $search_code = '%' . $wpdb->esc_like($search_code) . '%';
        $sql .= ' ORDER BY `name` ASC LIMIT %d, %d';
        $query = $wpdb->prepare($sql, $search_name, $search_code, intval($from), intval($items));
        return $wpdb->get_results($query);
    }

    public static function unlink($product_id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'b1_item_links', array('shop_product_id' => $product_id), array('%s'));
        if ($deleted) {
            return true;
        } else {
            return false;
        }
    }

    public static function link($shop_id, $b1_id)
    {
        global $wpdb;
        $shop_id = intval($shop_id);
        $b1_id = intval($b1_id);
        $data = array(
            'shop_product_id' => $shop_id,
            'b1_product_id' => $b1_id,
        );
        $result = $wpdb->insert($wpdb->prefix . 'b1_item_links', $data, array('%s', '%s'));
        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }

    public static function link_all_by_code()
    {
        global $wpdb;
        self::reset_all_links();
        $result = true;
        $map = self::fetch_id_map();
        foreach ($map as $item) {
            $result = $result && $wpdb->insert($wpdb->prefix . 'b1_item_links', array(
                    'shop_product_id' => $item->post_id,
                    'b1_product_id' => $item->id,
                ), array('%s', '%s'));
        }
        return $result;
    }

    private static function fetch_id_map()
    {
        global $wpdb;
        $sql = "
SELECT meta.post_id, item.id 
FROM {$wpdb->prefix}posts post
LEFT JOIN {$wpdb->prefix}postmeta meta ON post.ID = meta.post_id AND meta.`meta_key` = '_sku'
LEFT JOIN {$wpdb->prefix}posts parent_post ON parent_post.ID = post.post_parent
LEFT JOIN {$wpdb->prefix}postmeta parent_meta ON parent_meta.post_id = parent_post.ID AND parent_meta.`meta_key` = '_sku'
LEFT JOIN {$wpdb->prefix}b1_items item ON item.`code` = COALESCE(NULLIF(meta.`meta_value`, ''), parent_meta.`meta_value`)
WHERE post.`post_status` = 'publish' AND (post.`post_type` = 'product' OR post.`post_type` = 'product_variation')";
        return $wpdb->get_results($sql);
    }

}
