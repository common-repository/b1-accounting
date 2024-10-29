<?php

/**
 * @since 2.0.0
 * @package B1_Accounting
 * @subpackage B1_Accounting/admin
 * @author B1.lt <info@b1.lt>
 * @link https://www.b1.lt
 */
class B1_Accounting_Admin extends B1_Accounting_Base
{

    public function enqueue_styles()
    {
        if (isset($_GET['page']) && $_GET['page'] == $this->plugin_name) {
            $plugin_dir_url = plugin_dir_url(__FILE__);
            wp_enqueue_style($this->plugin_name . '-bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . '-fa', 'https://use.fontawesome.com/releases/v5.0.6/css/all.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . '-data-table', 'https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . '-custom', $plugin_dir_url . 'css/b1-accounting-admin.css', array(), $this->version, 'all');
        }
    }

    public function enqueue_scripts()
    {
        if (isset($_GET['page']) && $_GET['page'] == $this->plugin_name) {
            $plugin_dir_url = plugin_dir_url(__FILE__);
            wp_enqueue_script($this->plugin_name . '-bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');
            wp_enqueue_script($this->plugin_name . '-data-table', 'https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js', array('jquery'), $this->version, false);
            wp_enqueue_script($this->plugin_name . '-data-table-bootstrap', 'https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js', array('jquery'), $this->version, false);
            wp_register_script($this->plugin_name . '-custom', $plugin_dir_url . 'js/b1-accounting-admin.js', array('jquery'), $this->version, false);
            $custom = array(
                'security' => wp_create_nonce('b1_security'),
                'base_url' => admin_url() . 'admin-ajax.php',
                'texts' => [
                    'internal_error' => __('Internal error.', $this->plugin_name),
                    'processing' => __('Processing...', $this->plugin_name),
                    'length_menu' => __('Show _MENU_ items', $this->plugin_name),
                    'zero_records' => __('Nothing found', $this->plugin_name),
                    'showing_records' => __('Showing _START_ to _END_ of _TOTAL_ entries', $this->plugin_name),
                    'showing_zero_records' => __('No data available in table', $this->plugin_name),
                    'first' => __('First', $this->plugin_name),
                    'previous' => __('Previous', $this->plugin_name),
                    'next' => __('Next', $this->plugin_name),
                    'last' => __('Last', $this->plugin_name),
                ],
            );
            wp_localize_script($this->plugin_name . '-custom', 'b1', $custom);
            wp_enqueue_script($this->plugin_name . '-custom');
        }
    }


    public function add_plugin_admin_menu()
    {
        add_submenu_page('woocommerce', 'B1.lt', 'B1.lt', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page'));
    }

    public function display_plugin_setup_page()
    {
        include_once('partials/b1-accounting-admin-dashboard.php');
    }

    public function add_action_links($links)
    {
        $settingsLink = array(
            '<a href="' . admin_url('admin.php?page=' . $this->plugin_name) . '">' . __('Settings', $this->plugin_name) . '</a>',
            '<a href="' . admin_url('admin.php?page=' . $this->plugin_name) . '">' . __('Mapping', $this->plugin_name) . '</a>',
            '<a href="' . admin_url('admin.php?page=' . $this->plugin_name) . '">' . __('Logs', $this->plugin_name) . '</a>',
        );
        return array_merge($settingsLink, $links);
    }

    public function update_handler()
    {
        if (isset($_POST['b1_security']) && wp_verify_nonce($_POST['b1_security'], 'b1_security')) {
            $keys = array('shop_id', 'sync_orders_from','sync_orders_to','sync_order_status', 'sync_quantities', 'write_off','sync_invoices', 'sync_item_name', 'sync_item_price', 'api_key', 'private_key', 'access_key','sync_error_ignore');
            $data = array();

            foreach ($keys as $key) {
                $data[$key] = isset($_POST[$key]) ? sanitize_text_field(trim(str_replace("\'","'",$_POST[$key]))) : null;
            }
            $data['sync_quantities'] = intval($data['sync_quantities']);
            $data['writeoff'] = intval($data['write_off']);
            $data['sync_invoices'] = intval($data['sync_invoices']);
            $data['sync_error_ignore'] = intval($data['sync_error_ignore']);
            $data['sync_item_name'] = intval($data['sync_item_name']);
            $data['sync_item_price'] = intval($data['sync_item_price']);
            $data['sync_orders_from'] = empty($data['sync_orders_from']) ? null : $data['sync_orders_from'];
            $data['sync_orders_to'] = empty($data['sync_orders_to']) ? null : $data['sync_orders_to'];
            $data['api_key'] = empty($data['api_key']) ? null : $data['api_key'];
            $data['private_key'] = empty($data['private_key']) ? null : $data['private_key'];
            $data['access_key'] = empty($data['access_key']) ? null : $data['access_key'];

            if (empty($data['shop_id'])) {
                B1_Accounting_Helper::sendErrorResponse(__('Shop ID cannot be empty.', $this->plugin_name));
            }
            if (!empty($data['sync_orders_from']) && DateTime::createFromFormat('Y-m-d', $data['sync_orders_from']) === false) {
                B1_Accounting_Helper::sendErrorResponse(__('Bad sync orders from date format. Should be "yyyy-mm-dd".', $this->plugin_name));
            }

            if (!empty($data['sync_orders_to']) && DateTime::createFromFormat('Y-m-d', $data['sync_orders_to']) === false) {
                B1_Accounting_Helper::sendErrorResponse(__('Bad sync orders to date format. Should be "yyyy-mm-dd".', $this->plugin_name));
            }
            if (empty($data['access_key'])) {
                B1_Accounting_Helper::sendErrorResponse(__('Access key cannot be empty.', $this->plugin_name));
            } else if (strlen($data['access_key']) < 16) {
                B1_Accounting_Helper::sendErrorResponse(__('Access key length cannot be less than 16.', $this->plugin_name));
            }

            $options = array_merge($this->get_options(), $data);

            $this->update_options($options);

            B1_Accounting_Helper::sendSuccessResponse(__('Settings saved successfully!', $this->plugin_name));
        } else {
            B1_Accounting_Helper::sendErrorResponse(__('Invalid secret key specified.', $this->plugin_name));
        }
    }

    public function mapping_update_handler()
    {
        if (isset($_POST['b1_security']) && wp_verify_nonce($_POST['b1_security'], 'b1_security')) {
            $keys = [
                'order_date',
                'order_no',
                'invoice_series',
                'invoice_number',
                'currency',
                'discount',
                'discountVat',
                'gift',
                'gift_vat',
                'total',
                'order_email',
                'billing_is_company',
                'billing_first_name',
                'billing_last_name',
                'billing_address',
                'billing_city',
                'billing_country',
                'billing_short_name',
                'billing_vat_code',
                'billing_code',
                'billing_postcode',
                'delivery_is_company',
                'delivery_first_name',
                'delivery_last_name',
                'delivery_address',
                'delivery_city',
                'delivery_country',
                'delivery_short_name',
                'delivery_vat_code',
                'delivery_code',
                'delivery_postcode',
                'payer_name',
                'payer_code',
                'payer_vat_code',
                'payer_address',
                'payer_country_code',
                'payment_code',
                'payment_id',
                'payment_payment_date',
                'payment_sum',
                'payment_tax',
                'payment_currency',
                'payment_payment',
                'shipping_amount',
                'shipping_amount_tax',
                'items_name',
                'items_vat_rate',
                'items_quantity',
                'items_price',
                'items_price_vat',
                'items_code'
            ];
            $data = array();
            foreach ($keys as $key) {
                $data[$key] = isset($_POST[$key]) ? sanitize_text_field(trim(str_replace("\'","'",$_POST[$key]))) : null;
            }


            $options = array_merge($this->get_options(), $data);

            $this->update_options($options);

            B1_Accounting_Helper::sendSuccessResponse(__('Settings saved successfully!', $this->plugin_name));
        } else {
            B1_Accounting_Helper::sendErrorResponse(__('Invalid secret key specified.', $this->plugin_name));
        }
    }

    public function item_update_handler()
    {
        if (isset($_POST['b1_security']) && wp_verify_nonce($_POST['b1_security'], 'b1_security')) {
            $keys = array('attribute_id', 'measurement_unit_id');
            $data = array();
            foreach ($keys as $key) {
                $data[$key] = isset($_POST[$key]) ? sanitize_text_field(trim($_POST[$key])) : null;
            }
            $data['attribute_id'] = intval($data['attribute_id']);
            $data['measurement_unit_id'] = intval($data['measurement_unit_id']);

            if (empty($data['attribute_id'])) {
                B1_Accounting_Helper::sendErrorResponse(__('Attribute id cannot be empty.', $this->plugin_name));
            }
            if (empty($data['measurement_unit_id'])) {
                B1_Accounting_Helper::sendErrorResponse(__('Attribute id cannot be empty.', $this->plugin_name));
            }

            $options = array_merge($this->get_options(), $data);
            $this->update_options($options);

            B1_Accounting_Helper::sendSuccessResponse(__('Settings saved successfully!', $this->plugin_name));
        } else {
            B1_Accounting_Helper::sendErrorResponse(__('Invalid secret key specified.', $this->plugin_name));
        }
    }

    public function reset_all_handler()
    {
        if (isset($_POST['b1_security']) && wp_verify_nonce($_POST['b1_security'], 'b1_security')) {
            if ($_POST['resetAll']) {
                $manager = new B1_Accounting_Manager($this->plugin_name, $this->version);
                $manager->reset_all();
            }
            B1_Accounting_Helper::sendSuccessResponse(__('Orders reseted successfully!', $this->plugin_name));
        } else {
            B1_Accounting_Helper::sendErrorResponse(__('Invalid secret key specified.', $this->plugin_name));
        }
    }

    public function get_import_dropdown_items_handler()
    {
        $manager = new B1_Accounting_Manager($this->plugin_name, $this->version);
        try {
            $response = $manager->get_import_dropdown_items();
            B1_Accounting_Helper::sendSuccessResponse($response);
        } catch (B1Exception $e) {
            B1_Accounting_Helper::sendErrorResponse(__($e->getMessage(), $this->plugin_name));
        }
    }

    public function sync_items_handler()
    {
        $manager = new B1_Accounting_Manager($this->plugin_name, $this->version);
        $manager->sync_items();
    }

    public function sync_orders_handler()
    {
        $manager = new B1_Accounting_Manager($this->plugin_name, $this->version);
        $manager->sync_orders();
    }

    public function import_items_to_b1_handler()
    {
        $manager = new B1_Accounting_Manager($this->plugin_name, $this->version);
        $manager->import_items_to_b1();
    }

    public function download_invoice_handler()
    {
        $key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : -1;
        $order_id = isset($_GET['order_id']) ? sanitize_text_field($_GET['order_id']) : -1;
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_die(__('Forbidden', $this->plugin_name));
        } else if ($order->post->post_password != $key) {
            wp_die(__('Forbidden', $this->plugin_name));
        } else {
            $manager = new B1_Accounting_Manager($this->plugin_name, $this->version);
            $manager->get_invoice($order);
        }
    }

    public function clear_logs_handler()
    {
        $logger = new B1_Accounting_Logger($this->plugin_name, $this->version);
        $logger->clear_old_logs();
    }

    public function get_all_logs()
    {
        $page = 1;
        if (isset($_GET['path'])) { // ajax pagination
            $array = [];
            $page = wp_parse_url($_GET['path'], PHP_URL_QUERY);
            if ($page) {
                wp_parse_str($page, $array);
                if (isset($array['pagenum'])) {
                    $page = intval($array['pagenum']);
                }
            }
        }
        $logger = new B1_Accounting_Logger($this->plugin_name, $this->version);
        echo $logger->fetch_all_logs_as_html($page);
        exit();
    }

    public function get_log_by_id()
    {
        if (isset($_POST['id']) ) {
            $id = $_POST['id'];
            $logger = new B1_Accounting_Logger($this->plugin_name, $this->version);
            echo $logger->fetch_log_by_id_as_html($id);
        }
        exit();
    }

    public function export_logs()
    {
        $logger = new B1_Accounting_Logger($this->plugin_name, $this->version);
        $logger->export_logs(isset($_POST['selected']) ? $_POST['selected'] : []);
    }

    public function get_all_validation_logs()
    {
        $page = 1;
        if (isset($_GET['path'])) { // ajax pagination
            $array = [];
            $page = wp_parse_url($_GET['path'], PHP_URL_QUERY);
            if ($page) {
                wp_parse_str($page, $array);
                if (isset($array['pagenum'])) {
                    $page = intval($array['pagenum']);
                }
            }
        }
        $logger = new B1_Accounting_Validation_Logger($this->plugin_name, $this->version);
        echo $logger->fetch_all_logs_as_html($page);
        exit();
    }

    public function get_validation_log_by_id()
    {
        if (isset($_POST['id']) ) {
            $id = $_POST['id'];
            $logger = new B1_Accounting_Validation_Logger($this->plugin_name, $this->version);
            echo $logger->fetch_log_by_id_as_html($id);
        }
        exit();
    }

    public function check_db_version()
    {
        $installed_version = get_option("b1_database_version", 0);

        if($installed_version < 1) { // log table
            $logger = new B1_Accounting_Logger($this->plugin_name, $this->version);
            $logger->updateDB();

            update_option("b1_database_version", 1);
        }

        if($installed_version < 2) { // sync_orders_to
            $data['sync_orders_to'] =  null;
            $options = array_merge($this->get_options(), $data);
            $this->update_options($options);

            update_option("b1_database_version", 2);
        }

        if($installed_version < 3) { // validation log table
            $logger = new B1_Accounting_Validation_Logger($this->plugin_name, $this->version);
            $logger->updateDB();

            update_option("b1_database_version", 3);
        }
    }

    public function reset_mappings_handler()
    {
        $keys = [
            'order_date',
            'order_no',
            'invoice_series',
            'invoice_number',
            'currency',
            'discount',
            'discountVat',
            'gift',
            'gift_vat',
            'total',
            'order_email',
            'billing_is_company',
            'billing_first_name',
            'billing_last_name',
            'billing_address',
            'billing_city',
            'billing_country',
            'billing_short_name',
            'billing_vat_code',
            'billing_code',
            'billing_postcode',
            'delivery_is_company',
            'delivery_first_name',
            'delivery_last_name',
            'delivery_address',
            'delivery_city',
            'delivery_country',
            'delivery_short_name',
            'delivery_vat_code',
            'delivery_code',
            'delivery_postcode',
            'payer_name',
            'payer_code',
            'payer_vat_code',
            'payer_address',
            'payer_country_code',
            'payment_code',
            'payment_id',
            'payment_payment_date',
            'payment_sum',
            'payment_tax',
            'payment_currency',
            'payment_payment',
            'shipping_amount',
            'shipping_amount_tax',
            'items_name',
            'items_vat_rate',
            'items_quantity',
            'items_price',
            'items_price_vat',
            'items_code'
        ];
        $data = array();
        $defaults = B1_Accounting::get_mappings_defaults();
        foreach ($keys as $key) {
            $data[$key] = isset($defaults[$key]) ? $defaults[$key] : null;
        }

        $options = array_merge($this->get_options(), $data);

        $this->update_options($options);

        B1_Accounting_Helper::sendSuccessResponse(__('Settings saved successfully!', $this->plugin_name));
    }

    public function reset_settings_handler()
    {
        $keys = [
            'sync_quantities',
            'writeoff',
            'sync_invoices',
            'sync_error_ignore',
            'sync_item_name',
            'sync_item_price',
            'sync_orders_from',
            'sync_orders_to'
        ];
        $data = array();
        $defaults = B1_Accounting::get_mappings_defaults();
        foreach ($keys as $key) {
            $data[$key] = isset($defaults[$key]) ? $defaults[$key] : null;
        }

        $options = array_merge($this->get_options(), $data);

        $this->update_options($options);

        B1_Accounting_Helper::sendSuccessResponse(__('Settings saved successfully!', $this->plugin_name));
    }

    function validation_notice()
    {
        if(B1_Accounting_Validation_Logger::has_records()) {
            echo '<div class="notice notice-warning is-dismissible">
          <p>Validation errors. Please refer <a href="admin.php?page=b1-accounting#validations">B1.lt accounting plugin</a> "Data validation" tab</p>
         </div>';
        }
    }
}
