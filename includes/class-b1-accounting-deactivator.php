<?php

/**
 * @since 2.0.0
 * @package B1_Accounting
 * @subpackage B1_Accounting/includes
 * @author B1.lt <info@b1.lt>
 * @link https://www.b1.lt
 */
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-b1-accounting.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-b1-accounting-logger.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-b1-accounting-validation-logger.php';

class B1_Accounting_Deactivator extends B1_Accounting
{

    public static function deactivate()
    {
        static::drop_tables();
        static::delete_options();
        wp_clear_scheduled_hook('admin_post_b1_sync_orders');
        wp_clear_scheduled_hook('admin_post_b1_sync_items');
        wp_clear_scheduled_hook('admin_post_b1_clear_logs');
    }

    public static function drop_tables()
    {
        global $wpdb;
        $sql = "ALTER TABLE {$wpdb->prefix}posts DROP COLUMN b1_reference_id";
        $wpdb->query($sql);

        $query = B1_Accounting_Logger::get_drop_sql();
        $wpdb->query($query);

        $query = B1_Accounting_Validation_Logger::get_drop_sql();
        $wpdb->query($query);
    }

    public static function delete_options()
    {
        delete_option('b1-accounting');
    }

}
