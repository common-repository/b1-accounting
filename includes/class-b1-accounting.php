<?php

/**
 * @since 2.0.0
 * @package B1_Accounting
 * @subpackage B1_Accounting/includes
 * @author B1.lt <info@b1.lt>
 * @link https://www.b1.lt
 */
class B1_Accounting
{

    /**
     * @var B1_Accounting_Loader
     */
    protected $loader;
    /**
     * @var string
     */
    protected $plugin_name;
    /**
     * @var string
     */
    protected $version;

    public function __construct()
    {
        if (defined('B1_ACCOUNTING_VERSION')) {
            $this->version = B1_ACCOUNTING_VERSION;
        } else {
            $this->version = '2.0.0';
        }
        $this->plugin_name = 'b1-accounting';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
    }

    public function run()
    {
        $this->loader->run();
    }

    public function get_loader()
    {
        return $this->loader;
    }

    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    public function get_version()
    {
        return $this->version;
    }

    private function load_dependencies()
    {
        $path = plugin_dir_path(dirname(__FILE__));
        require_once $path . 'includes/lib/B1.php';
        require_once $path . 'includes/class-b1-accounting-loader.php';
        require_once $path . 'includes/class-b1-accounting-i18n.php';
        require_once $path . 'includes/class-b1-accounting-exception.php';
        require_once $path . 'includes/class-b1-accounting-helper.php';
        require_once $path . 'includes/class-b1-accounting-shop-item.php';
        require_once $path . 'includes/class-b1-accounting-order.php';
        require_once $path . 'includes/class-b1-accounting-base.php';
        require_once $path . 'includes/class-b1-accounting-manager.php';
        require_once $path . 'includes/class-b1-accounting-logger.php';
        require_once $path . 'includes/class-b1-accounting-validation-logger.php';
        require_once $path . 'admin/class-b1-accounting-admin.php';
        $this->loader = new B1_Accounting_Loader();

    }

    private function set_locale()
    {
        $plugin_i18n = new B1_Accounting_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    public function custom_cron_schedule($schedules)
    {
        $schedules['minutes'] = array(
            'interval' => 600,
            'display' => __('Once per 10 minutes')
        );
        $schedules['daily'] = array(
            'interval' => 43200,
            'display' => __('Twice daily')
        );
        $schedules['week'] = array(
            'interval' => 604800,
            'display' => __('Once weekly')
        );

        return $schedules;

    }

    private function define_admin_hooks()
    {
        add_filter('cron_schedules', array($this, 'custom_cron_schedule'));

        $plugin_admin = new B1_Accounting_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');

        if (!wp_next_scheduled('admin_post_b1_sync_orders')) {
            wp_schedule_event(time(), 'minutes', 'admin_post_b1_sync_orders');
        }
        $this->loader->add_action('admin_post_b1_sync_orders', $plugin_admin, 'sync_orders_handler');

        if (!wp_next_scheduled('admin_post_b1_sync_items')) {
            wp_schedule_event(time(), 'daily', 'admin_post_b1_sync_items');
        }
        $this->loader->add_action('admin_post_b1_sync_items', $plugin_admin, 'sync_items_handler');

        if (!wp_next_scheduled('admin_post_b1_clear_logs')) {
            wp_schedule_event(time(), 'minutes', 'admin_post_b1_clear_logs');
        }
        $this->loader->add_action('admin_post_b1_clear_logs', $plugin_admin, 'clear_logs_handler');

        $plugin_basename = plugin_basename(plugin_dir_path(__DIR__) . $this->get_plugin_name() . '.php');
        $this->loader->add_filter('plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links');
        $this->loader->add_action('wp_ajax_b1_options_update', $plugin_admin, 'update_handler');
        $this->loader->add_action('wp_ajax_b1_mapping_update', $plugin_admin, 'mapping_update_handler');
        $this->loader->add_action('wp_ajax_get_import_dropdown_items', $plugin_admin, 'get_import_dropdown_items_handler');
        $this->loader->add_action('wp_ajax_b1_item_options_update', $plugin_admin, 'item_update_handler');
        $this->loader->add_action('admin_post_b1_download_invoice', $plugin_admin, 'download_invoice_handler');
        $this->loader->add_action('wp_ajax_reset_all', $plugin_admin, 'reset_all_handler');
        $this->loader->add_action('admin_post_import_items_to_b1', $plugin_admin, 'import_items_to_b1_handler');
        $this->loader->add_action('wp_ajax_b1_load_logs', $plugin_admin, 'get_all_logs');
        $this->loader->add_action('wp_ajax_b1_view_detail_log', $plugin_admin, 'get_log_by_id');
        $this->loader->add_action('wp_ajax_b1_export_logs', $plugin_admin, 'export_logs');
        $this->loader->add_action('wp_ajax_b1_reset_mappings', $plugin_admin, 'reset_mappings_handler');
        $this->loader->add_action('wp_ajax_b1_reset_settings', $plugin_admin, 'reset_settings_handler');

        $this->loader->add_action('wp_ajax_b1_load_validation_logs', $plugin_admin, 'get_all_validation_logs');
        $this->loader->add_action('wp_ajax_b1_view_detail_validation_log', $plugin_admin, 'get_validation_log_by_id');

        $this->loader->add_action('plugins_loaded',  $plugin_admin,'check_db_version');
        $this->loader->add_action( 'admin_notices', $plugin_admin,'validation_notice' );
    }

    public static function get_mappings_defaults()
    {
        global $wpdb;

        $access_key = hash_hmac('sha256', uniqid(rand(), true), microtime() . rand());
        return [
            'access_key' => $access_key,
            'api_key' => '',
            'private_key' => '',
            'shop_id' => base_convert(rand(), 10, 36),
            'documentation_url' => 'https://www.b1.lt/doc/api',
            'help_page_url' => 'https://www.b1.lt/help/e-commerce-orders#woocomerce-nustatymai',
            'contact_email' => 'info@b1.lt',
            'items_per_request' => 100,
            'write_off' => 0,
            'sync_error_ignore' => 0,
            'sync_item_price' => 0,
            'sync_item_name' => 0,
            'sync_invoices' => 0,
            'b1_initial_sync' => 0,
            'initial_product_sync_done' => 0,
            'latest_product_sync_date' => date('Y-m-d'),
            'sync_quantities' => 1,
            'sync_orders_from' => date('Y-m-d'),
            'sync_orders_to' => null,
            'sync_order_status' => 'wc-completed',

            'order_date' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='".'_date_completed'."' and p0.ID = %d",
            'order_no' =>  "SELECT ID \nFROM {$wpdb->prefix}posts \nWHERE ID = %d",
            'invoice_series' => "SELECT 'INV';",
            'invoice_number' => "SELECT LPAD(o1.ID,6,0) \nFROM {$wpdb->prefix}posts o1 \nwhere o1.ID = %d",
            'currency' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_order_currency' and p0.ID = %d",
            'discount' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_cart_discount' and p0.ID = %d",
            'discountVat' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_cart_discount_tax' and p0.ID = %d",
            'gift'=>"SELECT meta_value \nFROM {$wpdb->prefix}woocommerce_order_itemmeta p0 \nLEFT JOIN {$wpdb->prefix}woocommerce_order_items pm1 on pm1.order_item_id=p0.order_item_id AND order_item_type='coupon' WHERE p0.meta_key='discount_amount' and pm1.order_id= %d",
            'gift_vat'=>"SELECT meta_value \nFROM {$wpdb->prefix}woocommerce_order_itemmeta p0 \nLEFT JOIN {$wpdb->prefix}woocommerce_order_items pm1 on pm1.order_item_id=p0.order_item_id AND order_item_type='coupon' WHERE p0.meta_key='discount_amount_tax' and pm1.order_id= %d",
            'total' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_order_total' and p0.ID = %d",
            'order_email' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_billing_email' and p0.ID = %d",
            'billing_is_company' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_billing_company' and p0.ID = %d",
            'billing_first_name' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_billing_first_name' and p0.ID = %d",//
            'billing_last_name' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_billing_last_name' and p0.ID = %d",//
            'billing_code' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_billing_code' and p0.ID = %d",
            'billing_address' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_billing_address_1' and p0.ID = %d",
            'billing_city' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_billing_city' and p0.ID = %d",
            'billing_country' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_billing_country' and p0.ID = %d",
            'billing_short_name' => "",
            'billing_vat_code' => "",
            'billing_postcode' => "",
            'delivery_is_company' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_shipping_company' and p0.ID = %d",
            'delivery_first_name' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_shipping_first_name' and p0.ID = %d",
            'delivery_last_name' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_shipping_last_name' and p0.ID = %d",
            'delivery_code' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_shipping_code' and p0.ID = %d",
            'delivery_address' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_shipping_address_1' and p0.ID = %d",
            'delivery_city' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_shipping_city' and p0.ID = %d",
            'delivery_country' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_shipping_country' and p0.ID = %d",
            'delivery_short_name' => "",
            'delivery_vat_code' => "",
            'delivery_postcode' => "",
            'payer_name' => "",
            'payer_code' => "",
            'payer_vat_code' => "",
            'payer_address' => "",
            'payer_country_code' => "",
            'payment_code' => "",
            'payment_id' => "",
            'payment_payment_date' => "",
            'payment_sum' => "",
            'payment_tax' => "",
            'payment_currency' => "",
            'payment_payment' => "",
            'shipping_amount' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_order_shipping' and p0.ID = %d",
            'shipping_amount_tax' => "SELECT meta_value \nFROM {$wpdb->prefix}posts p0 \nLEFT JOIN {$wpdb->prefix}postmeta pm1 on pm1.post_id=p0.ID \nWHERE meta_key='_order_shipping_tax' and p0.ID = %d",
            'items_name' => "SELECT order_item_name \nFROM {$wpdb->prefix}woocommerce_order_items pm1 \nWHERE pm1.order_item_id = %d",
            'items_quantity' => "SELECT meta_value \nFROM {$wpdb->prefix}woocommerce_order_itemmeta pm1 \nWHERE meta_key ='_qty' AND pm1.order_item_id = %d",
            'items_price' => "SELECT meta_value \nFROM {$wpdb->prefix}woocommerce_order_itemmeta pm1 \nWHERE meta_key ='_line_subtotal' AND pm1.order_item_id = %d",
            'items_price_vat' => "SELECT meta_value \nFROM {$wpdb->prefix}woocommerce_order_itemmeta pm1 \nWHERE meta_key ='_line_subtotal_tax' AND pm1.order_item_id = %d",
            'items_code' => "",
        ];

    }
}
