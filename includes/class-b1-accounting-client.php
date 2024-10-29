<?php

/**
 * @since 2.0.0
 * @package B1_Accounting
 * @subpackage B1_Accounting/includes
 * @author B1.lt <info@b1.lt>
 * @link https://www.b1.lt
 */
class B1_Accounting_Client
{

    public static function add($b1_client_id, $shop_client_id)
    {
        global $wpdb;
        $wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$wpdb->prefix}b1_clients (`b1_client_id`, `shop_client_id`) VALUES (%d, %d)", $b1_client_id, $shop_client_id));
    }

    public static function fetch_by_shop_client_id($shop_client_id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}b1_clients WHERE `shop_client_id` = %d", $shop_client_id));
    }

}
