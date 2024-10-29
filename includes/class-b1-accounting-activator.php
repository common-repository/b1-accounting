<?php

/**
 * @since 2.0.0
 * @package B1_Accounting
 * @subpackage B1_Accounting/includes
 * @author B1.lt <info@b1.lt>
 * @link https://www.b1.lt
 */
class B1_Accounting_Activator extends B1_Accounting
{

    /**
     * @since 2.0.0
     */
    public static function activate()
    {
        static::create_tables();
        static::init_options();
        wp_clear_scheduled_hook('admin_post_b1_sync_orders');
        wp_clear_scheduled_hook('admin_post_b1_sync_items');
        wp_clear_scheduled_hook('admin_post_b1_clear_logs');
    }

    public static function create_tables()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        global $wpdb;

        $wpdb->query("ALTER TABLE {$wpdb->prefix}posts ADD COLUMN b1_reference_id INT NULL DEFAULT NULL");

        $sql = B1_Accounting_Logger::get_init_sql();
        dbDelta( $sql );
        update_option("b1_database_version", 1); // log table
    }

    public static function init_options()
    {
        add_option('b1-accounting', static::get_mappings_defaults());

    }


}
