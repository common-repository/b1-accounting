<?php
if (!defined('WPINC')) {
    die;
}
/**
 * @var B1_Accounting_Admin $this
 */
$path = admin_url() . 'admin-post.php?action=';
$nonce = wp_create_nonce('b1_security');
?>


<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-7">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fas fa-columns"></i> <?php _e('Field mapping', $this->plugin_name); ?>
                </h3>
            </div>
            <div class="panel-body">
                <div class="update-nag notice notice-warning inline">
                    <i class="fas fa-exclamation"></i>&nbsp;<?php _e('Change these fields if you know exactly what you are doing. In case of problems, press the button', $this->plugin_name); ?>
                    <button type="submit" class="btn btn-primary" id="b1ResetMappingBtn">
                        <i class="fas fa-minus-circle"></i> <?php _e('Reset to defaults', $this->plugin_name); ?>
                    </button>

                </div>
                <form class="form" id="form-mapping">
                    <input class="form-control" name="action" value="b1_mapping_update" type="hidden">
                    <input type="hidden" name="b1_security" value="<?php echo $nonce ?>"/>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-cloud-upload-alt"></i> <?php _e('Update', $this->plugin_name); ?>
                        </button>
                    </div>
                    <div class="form-group">
                        <label class="help" for="order_date">
                            <?php _e('B1 field - orderDate , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="order_date" rows="5"
                                  type="text"><?php echo $this->get_option('order_date'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="order_no">
                            <?php _e('B1 field - orderNo , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="order_no" rows="5"
                                  type="text"><?php echo $this->get_option('order_no'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="invoice_series">
                            <?php _e('B1 field - invoiceSeries , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="invoice_series" rows="5"
                                  type="text"><?php echo $this->get_option('invoice_series'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="invoice_number">
                            <?php _e('B1 field - invoiceNumber , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="invoice_number" rows="5"
                                  type="text"><?php echo $this->get_option('invoice_number'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="currency">
                            <?php _e('B1 field - currency , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="currency" rows="5"
                                  type="text"><?php echo $this->get_option('currency'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="discount">
                            <?php _e('B1 field - discount , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="discount" rows="5"
                                  type="text"><?php echo $this->get_option('discount'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="discountVat">
                            <?php _e('B1 field - discountVat , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="discountVat" rows="5"
                                  type="text"><?php echo $this->get_option('discountVat'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="gift">
                            <?php _e('B1 field - gift , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="gift" rows="5"
                                  type="text"><?php echo $this->get_option('gift'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="gift_vat">
                            <?php _e('B1 field - giftVat , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="gift_vat" rows="5"
                                  type="text"><?php echo $this->get_option('gift_vat'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="total">
                            <?php _e('B1 field - total , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="total" rows="5"
                                  type="text"><?php echo $this->get_option('total'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="order_email">
                            <?php _e('B1 field - orderEmail , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="order_email" rows="5"
                                  type="text"><?php echo $this->get_option('order_email'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="billing_is_company">
                            <?php _e('B1 field - billingIsCompany , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="billing_is_company" rows="5"
                                  type="text"><?php echo $this->get_option('billing_is_company'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="billing_first_name">
                            <?php _e('B1 field - billingFirstName , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="billing_first_name" rows="5"
                                  type="text"><?php echo $this->get_option('billing_first_name'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="billing_last_name">
                            <?php _e('B1 field - billingLastName , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="billing_last_name" rows="5"
                                  type="text"><?php echo $this->get_option('billing_last_name'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="billing_address">
                            <?php _e('B1 field - billingAddress , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="billing_address" rows="5"
                                  type="text"><?php echo $this->get_option('billing_address'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="billing_city">
                            <?php _e('B1 field - billingCity , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="billing_city" rows="5"
                                  type="text"><?php echo $this->get_option('billing_city'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="billing_country">
                            <?php _e('B1 field - billingCountry , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="billing_country" rows="5"
                                  type="text"><?php echo $this->get_option('billing_country'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="billing_short_name">
                            <?php _e('B1 field - billingShortName , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="billing_short_name" rows="5"
                                  type="text"><?php echo $this->get_option('billing_short_name'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="billing_vat_code">
                            <?php _e('B1 field - billingVatCode , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="billing_vat_code" rows="5"
                                  type="text"><?php echo $this->get_option('billing_vat_code'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="billing_code">
                            <?php _e('B1 field - billingCode , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="billing_code" rows="5"
                                  type="text"><?php echo $this->get_option('billing_code'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="billing_postcode">
                            <?php _e('B1 field - billingPostcode , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="billing_postcode" rows="5"
                                  type="text"><?php echo $this->get_option('billing_postcode'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="delivery_is_company">
                            <?php _e('B1 field - deliveryIsCompany , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="delivery_is_company" rows="5"
                                  type="text"><?php echo $this->get_option('delivery_is_company'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="delivery_first_name">
                            <?php _e('B1 field - deliveryFirstName , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="delivery_first_name" rows="5"
                                  type="text"><?php echo $this->get_option('delivery_first_name'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="delivery_last_name">
                            <?php _e('B1 field - deliveryLastName , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="delivery_last_name" rows="5"
                                  type="text"><?php echo $this->get_option('delivery_last_name'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="delivery_address">
                            <?php _e('B1 field - deliveryAddress , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="delivery_address" rows="5"
                                  type="text"><?php echo $this->get_option('delivery_address'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="delivery_city">
                            <?php _e('B1 field - deliveryCity , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="delivery_city" rows="5"
                                  type="text"><?php echo $this->get_option('delivery_city'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="delivery_country">
                            <?php _e('B1 field - deliveryCountry , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="delivery_country" rows="5"
                                  type="text"><?php echo $this->get_option('delivery_country'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="delivery_short_name">
                            <?php _e('B1 field - deliveryShortName , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="delivery_short_name" rows="5"
                                  type="text"><?php echo $this->get_option('delivery_short_name'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="delivery_vat_code">
                            <?php _e('B1 field - deliveryVatCode , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="delivery_vat_code" rows="5"
                                  type="text"><?php echo $this->get_option('delivery_vat_code'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="delivery_code">
                            <?php _e('B1 field - deliveryCode , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="delivery_code" rows="5"
                                  type="text"><?php echo $this->get_option('delivery_code'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="delivery_postcode">
                            <?php _e('B1 field - deliveryPostCode , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="delivery_postcode" rows="5"
                                  type="text"><?php echo $this->get_option('delivery_postcode'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>


                    <div class="form-group">
                        <label class="help" for="items_name">
                            <?php _e('B1 field - itemsName , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="items_name" rows="5"
                                  type="text"><?php echo $this->get_option('items_name'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>
                    <div class="form-group">
                        <label class="help" for="items_code">
                            <?php _e('B1 field - itemsCode , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="items_code" rows="5"
                                  type="text"><?php echo $this->get_option('items_code'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(item_id)', $this->plugin_name); ?>
                        </h6>
                    </div>
                    <div class="form-group">
                        <label class="help" for="items_quantity">
                            <?php _e('B1 field - itemsQuantity , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="items_quantity" rows="5"
                                  type="text"><?php echo $this->get_option('items_quantity'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="items_price">
                            <?php _e('B1 field - itemsPrice , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="items_price" rows="5"
                                  type="text"><?php echo $this->get_option('items_price'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="items_price_vat">
                            <?php _e('B1 field - itemsPriceVat , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="items_price_vat" rows="5"
                                  type="text"><?php echo $this->get_option('items_price_vat'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="shipping_amount">
                            <?php _e('B1 field - shippingAmount , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="shipping_amount" rows="5"
                                  type="text"><?php echo $this->get_option('shipping_amount'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label class="help" for="shipping_amount_tax">
                            <?php _e('B1 field - shippingAmountTax , you can edit this sql to get your value.', $this->plugin_name); ?>
                        </label>
                        <textarea class="form-control widefat" name="shipping_amount_tax" rows="5"
                                  type="text"><?php echo $this->get_option('shipping_amount_tax'); ?>
                        </textarea>
                        <h6 class="form-help"><?php _e('%d  - is required , it is post_id(order_id)', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-cloud-upload-alt"></i> <?php _e('Update', $this->plugin_name); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



