<?php

class B1_Accounting_Manager extends B1_Accounting_Base
{

    const TTL = 3600;
    const MAX_ITERATIONS = 100;
    const ORDERS_PER_ITERATION = 100;
    const ITEMS_PER_ITERATION = 100;

    /**
     * @var $wpdb
     */
    private $db;
    /**
     * @var B1
     */
    private $b1;

    public function __construct($plugin_name, $version)
    {
        parent::__construct($plugin_name, $version);

        set_time_limit(self::TTL);
        ini_set('max_execution_time', self::TTL);

        global $wpdb;
        $this->db = $wpdb;
    }

    public function reset_all()
    {
        try {
            B1_Accounting_Order::reset_order_b1_reference_id();
        } catch (B1Exception $e) {
            B1_Accounting_Helper::debug($e);
            wp_die('Error');
        }
    }

    public function print_error_and_die($errHandle)
    {
        echo "<pre style='color:#ff0000'>";
        $content = $errHandle->getResponse()->getContent();
        print_r($errHandle->getResponse());
        B1_Accounting_Helper::debug($content['message']);
        echo "</pre>";
        wp_die('Error');
    }

    public function print_error_content($errHandle)
    {
        echo "<pre style='color:#ff0000'>";
        $content = $errHandle->getResponse()->getContent();
        print_r($content);
        echo "</pre>";
    }

    public function print_error_message($errHandle)
    {
        echo "<pre style='color:#ff0000'>";
        echo $errHandle->getMessage();
        echo "</pre>";
    }

    public function delete_order($internalId, $prefix)
    {
        $data = [
            'internalId' => $internalId,
            'shopId' => $prefix
        ];
        $response = $this->b1->request('e-commerce/orders/delete', $data);
    }

    public function sync_items()
    {
        $includeTax = get_option('woocommerce_prices_include_tax') == 'no';
        try {
            $this->b1 = new B1(
                [
                    'apiKey' => $this->get_option('api_key'),
                    'privateKey' => $this->get_option('private_key'),
                ]
            );
            $i = 0;
            B1_Accounting_Shop_Item::set_null_reference_id_product();
            $page = 0;

            $response = $this->b1->request('e-commerce/config/get');
            $content = $response->getContent();
            $warehouseId = $content['data']['warehouseId'];

            do {
                try {

                    $i++;
                    $response = $this->b1->request('e-commerce/items/stock', [
                        'warehouseId' => $warehouseId,
                        'page' => $page++,
                        'pageSize' => 500,
                        'filters' => [
                            'groupOp' => 'AND',
                            'rules' => [
                            ],
                        ],
                    ]);
                    $content = $response->getContent();
                    $data = $content['data'];
                    foreach ($data as $item) {
                        if ($item['code'] != null) {
                            if ($includeTax) {
                                $price = $item['priceWithoutVat'];
                            } else {
                                $price = $item['priceWithVat'];
                            }
                            B1_Accounting_Shop_Item::update_data_using_b1_item_id($item['id'], $item['code'],
                                $this->get_option('sync_quantities') ? (is_null($item['quantity']) ? 0 : $item['quantity']) : null,
                                $this->get_option('sync_item_price') ? $price : null,
                                $this->get_option('sync_item_name') ? $item['name'] : null);
                        }
                    }
                } catch (B1DuplicateException $e) {
                    $this->print_error_and_die($e);
                } catch (B1ValidationException $e) {
                    $this->print_error_and_die($e);
                } catch (B1ResourceNotFoundException $e) {
                    $this->print_error_and_die($e);
                } catch (B1InternalErrorException $e) {
                    $this->print_error_and_die($e);
                }
            } while ($page <= $content['pages'] && $i < self::MAX_ITERATIONS);
            $options = array_merge($this->get_options(), [
                'initial_product_sync_done' => 1,
            ]);
            $this->update_options($options);
            die('OK');
        } catch (B1Exception $e) {
            B1_Accounting_Helper::debug($e);
            wp_die('Error');
        }
    }

    public function sync_orders()
    {
        try {
            $this->b1 = new B1(
                [
                    'apiKey' => $this->get_option('api_key'),
                    'privateKey' => $this->get_option('private_key'),
                ]
            );
            $sync_orders_from = $this->get_option('sync_orders_from', '2000-01-01');
            if (!$this->get_option('order_date') || trim($this->get_option('order_date')) == "")
                throw new B1Exception("Empty field mapping values (order_date). You must again enable B1 plugin (disable first, if it is enabled).");

            $sync_orders_to = $this->get_option('sync_orders_to', null);
            if($sync_orders_to !== null && (strtotime($sync_orders_from) > strtotime($sync_orders_to))) {
                throw new B1Exception("Wrong sync order to date.");
            }
            $sync_order_status = $this->get_option('sync_order_status', 'wc-completed');
            if (!$sync_order_status) throw new B1Exception("Not set status of orders to sync");

            if ($this->get_option('sync_quantities')) {
                $response = $this->b1->request('e-commerce/config/get');
                $content = $response->getContent();
                $warehouseId = $content['data']['warehouseId'];
            }

            $i = 0;
            do {
                $i++;
                $orders = B1_Accounting_Order::fetch_all_orders($sync_orders_from, $sync_order_status, self::ORDERS_PER_ITERATION, $sync_orders_to);
                $this->check_orders($orders);
                foreach ($orders as $order) {

                    //------------ Order ------------
                    $order_data = $this->generate_order_data($order->ID);
                    try {
                        $response = $this->b1->request('e-commerce/orders/create', $order_data);
                        $content = $response->getContent();
                        if (!isset($content['data']['id'])) {
                            print_r($content);
                            throw new B1Exception("Bad response from B1");
                        }
                        $orderId = $content['data']['id'];
                        B1_Accounting_Order::assignB1OrderId($orderId, $order->ID);
                    } catch (B1DuplicateException $e) {
                        $content = $e->getResponse()->getContent();
                        echo $content['message'];
                        print_r($content['data']);
                        echo "<br>";
                        B1_Accounting_Order::assignB1OrderId($content['data']['id'], $order->ID);
                        continue;
                    } catch (B1ValidationException $e) {
                        echo "order id - " . $order->ID . "<br>";
                        $this->print_error_content($e);
                        if(!$this->get_option('sync_error_ignore')) {
                            wp_die("Error");
                        }
                    } catch (Exception $e) {
                        echo "order id - " . $order->ID . "<br>";
                        echo $e->getMessage();
                        if(!$this->get_option('sync_error_ignore')) {
                            wp_die("Error");
                        }
                    }

                    try {
                        if (isset($orderId)) {

                            //------------ Order items ------------
                            $ids = []; // Gathering for quantities update
                            $wcOrder = wc_get_order($order->ID);
                            foreach ($wcOrder->get_items(['line_item']) as $item) {

                                $orderItemData = $this->generate_order_item_data($item, $order->ID);
                                // Bad item object
                                if ($orderItemData == "skip") {
                                    throw new Exception("WrongOrderItemObject");
                                }
                                $orderItemData['orderId'] = $orderId;
                                $response = $this->b1->request('e-commerce/order-items/create', $orderItemData);
                                $ids[] = $orderItemData['itemId'];
                            }
                            //------------ Write off ------------
                            $data = [
                                'orderId' => $orderId,
                            ];
                            if ($this->get_option('write_off')) {
                                $response = $this->b1->request('e-commerce/orders/create-write-off', $data);
                            }

                            //------------ Invoice ------------
                            $data = [
                                'orderId' => $orderId,
                                'series' => (isset($order_data['customSeries']) ? $order_data['customSeries'] : null),
                                'number' => (isset($order_data['customNumber']) ? $order_data['customNumber'] : null),
                            ];
                            $response = $this->b1->request('e-commerce/orders/create-sale', $data);


                            //------------ Updating quantities ------------
                            if ($this->get_option('sync_quantities') && isset($warehouseId)) {
                                $page = 0;
                                do {
                                    $page++;
                                    $data = [
                                        'warehouseId' => $warehouseId,
                                        'page' => $page,
                                        'pageSize' => 500,
                                        'filters' => [
                                            'groupOp' => 'AND',
                                            'rules' => [
                                                [
                                                    'field' => 'id',
                                                    'op' => 'in',
                                                    'data' => $ids
                                                ]
                                            ],
                                        ],
                                    ];
                                    $response = $this->b1->request('e-commerce/items/stock', $data);
                                    $retStocks = $response->getContent()['data'];
                                    foreach ($retStocks as $stock) {
                                        B1_Accounting_Shop_Item::update_quantity_using_b1_item_id($stock['quantity'], $stock['id']);
                                    }

                                } while ($retStocks['data']['pages'] > $page && self::MAX_ITERATIONS > $page);
                            }
                        }
                    }
                    catch (B1ValidationException $e) {

                        B1_Accounting_Order::assignB1OrderId(null, $order->ID);
                        echo "order id - " . $order->ID . "<br>";
                        $this->print_error_content($e);
                        $this->delete_order($order->ID, $order_data['shopId']);
                        if(!$this->get_option('sync_error_ignore')) {
                            wp_die('Error');
                        }
                    } catch (B1ResourceNotFoundException $e) {
                        B1_Accounting_Order::assignB1OrderId(null, $order->ID);
                        echo "order id - " . $order->ID . "<br>";
                        $this->print_error_content($e);
                        $this->delete_order($order->ID, $order_data['shopId']);
                        if(!$this->get_option('sync_error_ignore')) {
                            wp_die('Error');
                        }
                    } catch (B1InternalErrorException $e) {
                        B1_Accounting_Order::assignB1OrderId(null, $order->ID);
                        echo "order id - " . $order->ID . "<br>";
                        $this->print_error_content($e);
                        $this->delete_order($order->ID, $order_data['shopId']);
                        if(!$this->get_option('sync_error_ignore')) {
                            wp_die('Error');
                        }
                    } catch (Exception $e) {
                        B1_Accounting_Order::assignB1OrderId(null, $order->ID);
                        echo "order id - " . $order->ID . "<br>";
                        echo $e->getMessage();
                        $this->delete_order($order->ID, $order_data['shopId']);
                        if(!$this->get_option('sync_error_ignore')) {
                            wp_die('Error');
                        }
                    }
                }
            } while ((count($orders) == self::ORDERS_PER_ITERATION) && ($i < self::MAX_ITERATIONS));
            die('OK');
        } catch (B1Exception $e) {
            $this->print_error_message($e);
            wp_die('Final Error');
        }
    }

    private function generate_order_data($order_id)
    {
        try {


            $order_data = array();
            $order_data['shopId'] = $this->get_option('shop_id');
            $order_data['internalId'] = $order_id;
            if ($this->get_option('sync_invoices')) {
                $invoiceSeries = B1_Accounting_Order::get_data_by_sql($this->get_option('invoice_series'), $order_id);
                $invoiceNumber = B1_Accounting_Order::get_data_by_sql($this->get_option('invoice_number'), $order_id);
                if (isset($invoiceSeries) && isset($invoiceNumber)) {
                    $order_data['customSeries'] = $invoiceSeries;
                    $order_data['customNumber'] = $invoiceNumber;
                }
            }
            $dateValue = B1_Accounting_Order::get_data_by_sql($this->get_option('order_date'), $order_id);
            if ($dateValue) {
                if (is_numeric($dateValue)) {
                    $order_data['date'] = date('Y-m-d', (int)$dateValue);
                } else {
                    $order_data['date'] = date('Y-m-d', strtotime($dateValue));
                }
            } else {
                throw new Exception("Cannot get order date for order $order_id");
            }
            $order_data['number'] = (string)B1_Accounting_Order::get_data_by_sql($this->get_option('order_no'), $order_id);
            $order_data['currencyCode'] = B1_Accounting_Order::get_data_by_sql($this->get_option('currency'), $order_id);
            $order_data['discount'] = round(B1_Accounting_Order::get_data_by_sql($this->get_option('discount'), $order_id) * 1
                + B1_Accounting_Order::get_data_by_sql($this->get_option('discountVat'), $order_id) * 1, 2);

//        if ($gift = B1_Accounting_Order::get_data_by_sql($this->get_option('gift'), $order_id)) {
//            $order_data['gift'] = intval(($gift + B1_Accounting_Order::get_data_by_sql($this->get_option('gift_vat'), $order_id)) * 100);
//        }

            $order_data['cod'] = 0;
            $wcOrder = wc_get_order($order_id);
            foreach ($wcOrder->get_items(['fee']) as $item) {
                $order_data['cod'] += $item['total'] + $item['total_tax'];
            }
            $order_data['cod'] = round($order_data['cod'], 2);

            $order_data['total'] = round(B1_Accounting_Order::get_data_by_sql($this->get_option('total'), $order_id) * 1, 2);
            $order_data['email'] = B1_Accounting_Order::get_data_by_sql($this->get_option('order_email'), $order_id);

            $order_data['billing']['isJuridical'] = B1_Accounting_Order::get_data_by_sql($this->get_option('billing_is_company'), $order_id) == '' ? 0 : 1;
            $order_data['billing']['name'] = B1_Accounting_Order::get_data_by_sql(
                $this->get_option('billing_is_company'), $order_id) == '' ?
                trim(B1_Accounting_Order::get_data_by_sql($this->get_option('billing_first_name'), $order_id) . ' ' . B1_Accounting_Order::get_data_by_sql($this->get_option('billing_last_name'), $order_id))
                : B1_Accounting_Order::get_data_by_sql($this->get_option('billing_is_company'), $order_id);
            $order_data['billing']['address'] = B1_Accounting_Order::get_data_by_sql($this->get_option('billing_address'), $order_id);
            $order_data['billing']['cityName'] = B1_Accounting_Order::get_data_by_sql($this->get_option('billing_city'), $order_id);
            $order_data['billing']['countryCode'] = B1_Accounting_Order::get_data_by_sql($this->get_option('billing_country'), $order_id);

            if ($billingShortName = B1_Accounting_Order::get_data_by_sql($this->get_option('billing_short_name'), $order_id)) {
                $order_data['billing']['shortName'] = $billingShortName;
            }
            if ($billingVatCode = B1_Accounting_Order::get_data_by_sql($this->get_option('billing_vat_code'), $order_id)) {
                $order_data['billing']['vatCode'] = $billingVatCode;
            }
            if ($billingCode = B1_Accounting_Order::get_data_by_sql($this->get_option('billing_code'), $order_id)) {
                $order_data['billing']['code'] = $billingCode;
            }
            if ($billingPostcode = B1_Accounting_Order::get_data_by_sql($this->get_option('billing_postcode'), $order_id)) {
                $order_data['billing']['postcode'] = $billingPostcode;
            }

            $order_data['delivery']['isJuridical'] = B1_Accounting_Order::get_data_by_sql($this->get_option('delivery_is_company'), $order_id) == '' ? 0 : 1;
            $order_data['delivery']['name'] = B1_Accounting_Order::get_data_by_sql(
                $this->get_option('delivery_is_company'), $order_id) == '' ?
                trim(B1_Accounting_Order::get_data_by_sql($this->get_option('delivery_first_name'), $order_id) . ' ' . B1_Accounting_Order::get_data_by_sql($this->get_option('delivery_last_name'), $order_id))
                : B1_Accounting_Order::get_data_by_sql($this->get_option('delivery_is_company'), $order_id);
            $order_data['delivery']['address'] = B1_Accounting_Order::get_data_by_sql($this->get_option('delivery_address'), $order_id);
            $order_data['delivery']['cityName'] = B1_Accounting_Order::get_data_by_sql($this->get_option('delivery_city'), $order_id);
            $order_data['delivery']['countryCode'] = B1_Accounting_Order::get_data_by_sql($this->get_option('delivery_country'), $order_id);
            if ($deliveryShortName = B1_Accounting_Order::get_data_by_sql($this->get_option('delivery_short_name'), $order_id)) {
                $order_data['delivery']['shortName'] = $deliveryShortName;
            }
            if ($deliveryVatCode = B1_Accounting_Order::get_data_by_sql($this->get_option('delivery_vat_code'), $order_id)) {
                $order_data['delivery']['vatCode'] = $deliveryVatCode;
            }
            if ($deliveryCode = B1_Accounting_Order::get_data_by_sql($this->get_option('delivery_code'), $order_id)) {
                $order_data['delivery']['code'] = $deliveryCode;
            }
            if ($deliveryPostcode = B1_Accounting_Order::get_data_by_sql($this->get_option('delivery_postcode'), $order_id)) {
                $order_data['delivery']['postcode'] = $deliveryPostcode;
            }

            if ($order_data['billing']['name'] == '') {
                $order_data['billing'] = $order_data['delivery'];
            }

            if ($order_data['delivery']['name'] == '') {
                $order_data['delivery'] = $order_data['billing'];
            }
            $order_data['shipping'] =
                round(B1_Accounting_Order::get_data_by_sql($this->get_option('shipping_amount'), $order_id) * 1
                    + B1_Accounting_Order::get_data_by_sql($this->get_option('shipping_amount_tax'), $order_id) * 1, 2);

            return $order_data;
        } catch (Exception $e) {
            $this->print_error_message($e);
            wp_die('Error');
        }
    }

    public function generate_order_item_data($item, $order_id)
    {
        $itemData = [];
        if ($item->get_variation_id() == 0) {

            $referenceId = B1_Accounting_Shop_Item::get_product_reference_id($item->get_product_id());
            $itemOrderId = B1_Accounting_Order::get_product_order_id($order_id, $item->get_product_id());
        } else {
            $referenceId = B1_Accounting_Shop_Item::get_variation_reference_id($item->get_variation_id());
            $itemOrderId = B1_Accounting_Order::get_product_variation_order_id($order_id, $item->get_variation_id());
        }
        if (!$itemOrderId){
//            Product is deleted
            $itemOrderId = $item->get_id();
            $itemData['itemId'] = null;
            $itemData['name'] = $item->get_name();
            $itemData['quantity'] = $item->get_quantity();
            $taxes = $item->get_taxes();
            foreach ($taxes['subtotal'] as $rate_id => $tax) {
                if (!$tax) {
                    if(get_option('woocommerce_calc_taxes') == 'yes') {
                        $itemData['vatRate'] = 0;
                    }
                    continue;
                }
                $rate = WC_Tax::_get_tax_rate($rate_id);
                if (!empty($rate)) {
                    $itemData['vatRate'] = $rate['tax_rate'];
                }
                break;
            }
            $itemData['price'] = round(((B1_Accounting_Order::get_data_by_sql($this->get_option('items_price'), $itemOrderId) * 1
                    + B1_Accounting_Order::get_data_by_sql($this->get_option('items_price_vat'), $itemOrderId) * 1)
                / B1_Accounting_Order::get_data_by_sql($this->get_option('items_quantity'), $itemOrderId)), 4);
            $itemData['sum'] = round((B1_Accounting_Order::get_data_by_sql($this->get_option('items_price'), $itemOrderId) * 1
                + B1_Accounting_Order::get_data_by_sql($this->get_option('items_price_vat'), $itemOrderId) * 1), 2);
            return $itemData;
        }

        $itemData['itemId'] = $referenceId;
        $itemData['name'] = B1_Accounting_Order::get_data_by_sql($this->get_option('items_name'), $itemOrderId);
        $itemData['quantity'] = round(B1_Accounting_Order::get_data_by_sql($this->get_option('items_quantity'), $itemOrderId) * 1, 3);
        $product = wc_get_product($item->get_product_id());
        if (!$product) return "skip";
        if ($product->is_taxable()) {
            $taxes = $item->get_taxes();
            foreach ($taxes['subtotal'] as $rate_id => $tax) {
                if (!$tax) {
                    if(get_option('woocommerce_calc_taxes') == 'yes') {
                        $itemData['vatRate'] = 0;
                    }
                    continue;
                }
                $rate = WC_Tax::_get_tax_rate($rate_id);
                if (!empty($rate)) {
                    $itemData['vatRate'] = $rate['tax_rate'];
                }
                break;
            }
            $itemData['price'] = round(((B1_Accounting_Order::get_data_by_sql($this->get_option('items_price'), $itemOrderId) * 1
                    + B1_Accounting_Order::get_data_by_sql($this->get_option('items_price_vat'), $itemOrderId) * 1)
                / B1_Accounting_Order::get_data_by_sql($this->get_option('items_quantity'), $itemOrderId)), 4);
            $itemData['sum'] = round((B1_Accounting_Order::get_data_by_sql($this->get_option('items_price'), $itemOrderId) * 1
                + B1_Accounting_Order::get_data_by_sql($this->get_option('items_price_vat'), $itemOrderId) * 1), 2);
        } else {
            if (get_option('woocommerce_calc_taxes') == 'yes'){
                $itemData['vatRate'] = 0;
            }
            $itemData['price'] = round((B1_Accounting_Order::get_data_by_sql($this->get_option('items_price'), $itemOrderId) * 1
                / B1_Accounting_Order::get_data_by_sql($this->get_option('items_quantity'), $itemOrderId)), 4);
            $itemData['sum'] = round(B1_Accounting_Order::get_data_by_sql($this->get_option('items_price'), $itemOrderId) * 1, 2);
        }
        return $itemData;
    }

    public function import_items_to_b1()
    {

        $attribute = $this->get_option('attribute_id');
        if (!$attribute) {
            B1_Accounting_Helper::debug("Product attribute is not set");
            wp_die('Error');
        }
        $measurement = $this->get_option('measurement_unit_id');
        if (!$measurement) {
            B1_Accounting_Helper::debug("Measurement unit is not set");
            wp_die('Error');
        }
        $this->check_items();
        try {
            $this->b1 = new B1(
                [
                    'apiKey' => $this->get_option('api_key'),
                    'privateKey' => $this->get_option('private_key'),
                ]
            );
            $lid = null;
            $i = 0;
            do {
                $i++;
                $items = B1_Accounting_Shop_Item::fetch_all_items(self::ITEMS_PER_ITERATION);
                foreach ($items as $item) {
                    $item_data = $this->generate_item_data($item->ID);
                    try {
                        $response = $this->b1->request('reference-book/items/create', $item_data['data']);
                        $content = $response->getContent();
                        B1_Accounting_Shop_Item::update_code_using_item_id($content['data']['id'], $item->ID);
                    } catch (B1DuplicateException $e) {
                        $content = $e->getResponse()->getContent();
                        echo 'Item name - ' . $item->post_name . ' sku -' . $item->meta_value . ' is already in b1 system ' . '<br>';
                        B1_Accounting_Shop_Item::update_code_using_item_id(0, $item->ID);
                    }
                }
            } while (count($items) == self::ITEMS_PER_ITERATION && $i < self::MAX_ITERATIONS);
            die('OK');
        } catch (B1Exception $e) {
            $this->print_error_and_die($e);
        }
    }

    private function generate_item_data($item_id)
    {
        $item = wc_get_product($item_id);
        $item_data = array();
        $code = B1_Accounting_Shop_Item::get_data_by_sql($this->get_option('items_code'), $item_id);
            if(!$code) {
                $item_data['code'] = $item->get_sku();
            } else {
                $item_data['code'] = $code;
            }
        $item_data['name'] = $item->get_name();
        $item_data['attributeId'] = $this->get_option('attribute_id');
        $item_data['measurementUnitId'] = $this->get_option('measurement_unit_id');
        $item_data['description'] = $item->get_description();
        $item_data['priceWithoutVat'] = $item->get_price_excluding_tax();
        $item_data['priceWithVat'] = $item->get_price_including_tax();
        return [
            'data' => $item_data
        ];
    }

    public function get_import_dropdown_items()
    {
        $this->b1 = new B1(
            [
                'apiKey' => $this->get_option('api_key'),
                'privateKey' => $this->get_option('private_key'),
            ]
        );
        $data = [
            'page' => 1,
            'rows' => 100,
            'filters' => [
                'groupOp' => 'AND',
            ],
        ];
        $resultAttributeId = $this->b1->request('reference-book/item-attributes/list', $data);
        $resultMeasurementId = $this->b1->request('reference-book/measurement-units/list', $data);
        $AttributeId = $resultAttributeId->getContent();
        $MeasurementId = $resultMeasurementId->getContent();
        return [
            'attributeId' => $AttributeId,
            'measurementId' => $MeasurementId,
        ];
    }

    /**
     * @param WC_Order $order
     */
    public function get_invoice($order)
    {
        try {
            $this->b1 = new B1(array(
                'apiKey' => $this->get_option('api_key'),
                'privateKey' => $this->get_option('private_key'),
            ));
            $data = [
                'prefix' => $this->get_option('shop_id'),
                'orderId' => $order->get_id(),
            ];
            $response = $this->b1->request('shop/invoices/get', $data);
            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename=' . $order->get_id() . '.pdf');
            echo $response->getContent();
        } catch (B1Exception $e) {
//            wp_die(__('Internal error.', $this->plugin_name));
            wp_die($e->getMessage());
        }
    }

    /**
     * Check items before sending to B1
     */
    private function check_items()
    {
        B1_Accounting_Validation_Logger::clear_old_logs();
        $items = B1_Accounting_Shop_Item::fetch_all_items(999999);
        $items_no_sku = B1_Accounting_Shop_Item::fetch_all_items_without_sku();
        $hasErrors = false;
        $codes = [];
        $requiredItemProperties = ['code', 'name', 'attributeId', 'measurementUnitId'];
        foreach ($items_no_sku as $item) {
            $message = "Item id {$item->ID} have no SKU and will not be imported to B1";
            B1_Accounting_Validation_Logger::save($message);
        }
        foreach ($items as $item) {
            $item_data = $this->generate_item_data($item->ID);
            $errors = [];
            if(isset($item_data['data']['code']) && $item_data['data']['code'] !== '') {
                if (in_array($item_data['data']['code'], $codes)) {
                    $errors[] = "Has duplicate 'code' {$item_data['data']['code']}";
                } else {
                    $codes[] = $item_data['data']['code'];
                }
            }
            foreach ($requiredItemProperties as $property) {
                if(!isset($item_data['data'][$property]) || $item_data['data'][$property] === '') {
                    $errors[] = "Have empty '{$property}' property";
                }
            }

            if(count($errors) > 0) { // save to validation logs
                $hasErrors = true;
                $name = "id {$item->ID}";
                if(isset($item_data['data']['code']) && $item_data['data']['code'] !== '') {
                    $name = "SKU '{$item_data['data']['code']}'";
                } else if (isset($item_data['data']['name']) && $item_data['data']['name'] !== '') {
                    $name = "title '{$item_data['data']['name']}'";
                }
                $message = "Item {$name} errors: ".implode('. ', $errors);
                B1_Accounting_Validation_Logger::save($message);
            }
        }
        if($hasErrors) {
            do_action( 'validation_notice' );
            wp_die('Validation errors. Please refer to B1.lt accounting plugin "Data validation" tab');
        }
    }

    /**
     * Check orders before sending to B1
     * @param $orders
     */
    private function check_orders($orders)
    {
        B1_Accounting_Validation_Logger::clear_old_logs();
        $hasErrors = false;
        $requiredOrderProperties = ['shopId','internalId','date','number','currencyCode','discount','total','billing.name','billing.countryCode', 'delivery.name','delivery.countryCode'];
        $requiredItemProperties = ['price','quantity','sum','name',];
        // check for HPOS plugin
        if(count($orders) == 0 && get_option('woocommerce_custom_orders_table_enabled') == 'yes') {
            $hasErrors = true;
            $message = "Please check if the option 'Enable compatibility mode' is selected on Woocommerce plugin Advanced->Features tab";
            B1_Accounting_Validation_Logger::save($message);
        }
        foreach ($orders as $order) {
            $errors = [];
            $order_data = $this->generate_order_data($order->ID);

            foreach ($requiredOrderProperties as $property) {
                if(strpos($property,'.') !== false) { // nested property
                    $arr = explode('.',$property);
                    $parent = $arr[0];
                    $sub = $arr[1];

                    if (!isset($order_data[$parent][$sub]) || $order_data[$parent][$sub] === '') {
                        $errors[] = "Has empty '{$property}' property";
                    }

                } else {
                    if (!isset($order_data[$property]) || $order_data[$property] === '') {
                        $errors[] = "Has empty '{$property}' property";
                    }
                }
            }

            $wcOrder = wc_get_order($order->ID);
            foreach ($wcOrder->get_items(['line_item']) as $item) {
                $orderItemData = $this->generate_order_item_data($item, $order->ID);

                foreach ($requiredItemProperties as $property) {
                    if(!isset($orderItemData[$property]) || $orderItemData[$property] === '') {
                        $errors[] = "Order item has empty '{$property}' property";
                    }
                }
            }

            if(count($errors) > 0) { // save to validation logs
                $hasErrors = true;
                $message = "Order id {$order->ID} errors: ".implode('. ', $errors);
                B1_Accounting_Validation_Logger::save($message);
            }

        }
        if($hasErrors) {
            do_action( 'validation_notice' );
            wp_die('Validation errors. Please refer to B1.lt accounting plugin "Data validation" tab');
        }
    }

}
