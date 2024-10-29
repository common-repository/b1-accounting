<?php
if (!defined('WPINC')) {
    die;
}
/**
 * @var B1_Accounting_Admin $this
 */
$path = admin_url() . 'admin-post.php?action=';
$manager_urls = array(
    array(
        'url' => $path . 'b1_sync_items&key=' . $this->get_option('access_key'),
    ),
    array(
        'url' => $path . 'b1_sync_orders&key=' . $this->get_option('access_key'),
    ),
);
$item_url = array(
    'url' => $path . 'import_items_to_b1&key=' . $this->get_option('access_key'),
);
$nonce = wp_create_nonce('b1_security');
?>
<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fas fa-cogs"></i> <?php _e('Configuration', $this->plugin_name); ?>
                </h3>
            </div>
            <div class="panel-body">
                <form class="form" id="form-settings">
                    <input class="form-control" name="action" value="b1_options_update" type="hidden">
                    <input type="hidden" name="b1_security" value="<?php echo $nonce ?>"/>
                    <div class="form-group">
                        <label class="help" for="shop_id">
                            <?php _e('E-shop ID', $this->plugin_name); ?>
                        </label>
                        <input class="form-control" name="shop_id" value="<?php echo $this->get_option('shop_id'); ?>" type="text">
                        <h6 class="form-help"><?php _e('Random and unique shop identifier. This will be passed when syncing orders. ', $this->plugin_name); ?>
                        </h6>
                    </div>
                    <div class="form-group">
                        <label for="sync_orders_from"><?php _e('Sync orders from', $this->plugin_name); ?></label>
                        <input class="form-control" name="sync_orders_from" value="<?php echo $this->get_option('sync_orders_from'); ?>" type="text">
                        <h6 class="form-help"><?php _e('Set Order sync start date', $this->plugin_name); ?>
                        </h6>
                    </div>
                    <div class="form-group">
                        <label for="sync_orders_to"><?php _e('Sync orders to', $this->plugin_name); ?></label>
                        <input class="form-control" name="sync_orders_to" value="<?php echo $this->get_option('sync_orders_to'); ?>" type="text">
                        <h6 class="form-help"><?php _e('Set Order sync end date. If empty- orders will be synced until today', $this->plugin_name); ?>
                        </h6>
                    </div>
                    <div class="form-group">
                        <label for="sync_order_status"><?php _e('Status of orders to sync', $this->plugin_name); ?></label>
                        <select class="form-control" name="sync_order_status">
                            <option value="wc-completed" <?php echo ($this->get_option('sync_order_status') == 'wc-completed') ? 'selected' : ''; ?>>
                                <?php _e('Completed', $this->plugin_name); ?>
                            </option>
                            <option value="wc-processing" <?php echo ($this->get_option('sync_order_status') == 'wc-processing') ? 'selected' : ''; ?>>
                                <?php _e('Processing', $this->plugin_name); ?>
                            </option>
                            <option value="wc-on-hold" <?php echo ($this->get_option('sync_order_status') == 'wc-on-hold') ? 'selected' : ''; ?>>
                                <?php _e('On hold', $this->plugin_name); ?>
                            </option>
                            <option value="wc-pending" <?php echo ($this->get_option('sync_order_status') == 'wc-pending') ? 'selected' : ''; ?>>
                                <?php _e('Pending', $this->plugin_name); ?>
                            </option>
                            <option value="wc-cancelled" <?php echo ($this->get_option('sync_order_status') == 'wc-cancelled') ? 'selected' : ''; ?>>
                                <?php _e('Cancelled', $this->plugin_name); ?>
                            </option>
                            <option value="wc-refunded" <?php echo ($this->get_option('sync_order_status') == 'wc-refunded') ? 'selected' : ''; ?>>
                                <?php _e('Refunded', $this->plugin_name); ?>
                            </option>
                            <option value="wc-failed" <?php echo ($this->get_option('sync_order_status') == 'wc-failed') ? 'selected' : ''; ?>>
                                <?php _e('Failed', $this->plugin_name); ?>
                            </option>
                        </select>
                        <h6 class="form-help"><?php _e('Only orders with this status will be sent to  B1.', $this->plugin_name); ?>
                        </h6>
                    </div>
                    <div class="form-group">
                        <label for="sync_quantities"><?php _e('Sync B1.lt quantities with e-shop', $this->plugin_name); ?></label>
                        <select class="form-control" name="sync_quantities">
                            <option value="1" <?php echo ($this->get_option('sync_quantities')) ? 'selected' : ''; ?>>
                                <?php _e('Yes', $this->plugin_name); ?>
                            </option>
                            <option value="0" <?php echo (!$this->get_option('sync_quantities')) ? 'selected' : ''; ?>>
                                <?php _e('No', $this->plugin_name); ?>
                            </option>
                        </select>
                        <h6 class="form-help"><?php _e('Whenever to enable quantity sync with B1.', $this->plugin_name); ?>
                        </h6>
                    </div>
                    <div class="form-group">
                        <label for="sync_error_ignore"><?php _e('Ignore errors and sync remaining orders', $this->plugin_name); ?></label>
                        <select class="form-control" name="sync_error_ignore">
                            <option value="1" <?php echo ($this->get_option('sync_error_ignore')) ? 'selected' : ''; ?>>
                                <?php _e('Yes', $this->plugin_name); ?>
                            </option>
                            <option value="0" <?php echo (!$this->get_option('sync_error_ignore')) ? 'selected' : ''; ?>>
                                <?php _e('No', $this->plugin_name); ?>
                            </option>
                        </select>
                        <h6 class="form-help"><?php _e('Whenever ignore errors and sync remaining orders.', $this->plugin_name); ?>
                        </h6>
                    </div>
                    <div class="form-group">
                        <label for="write_off"><?php _e('Try to write-off', $this->plugin_name); ?></label>
                        <select class="form-control" name="write_off">
                            <option value="1" <?php echo ($this->get_option('write_off')) ? 'selected' : ''; ?>>
                                <?php _e('Yes', $this->plugin_name); ?>
                            </option>
                            <option value="0" <?php echo (!$this->get_option('write_off')) ? 'selected' : ''; ?>>
                                <?php _e('No', $this->plugin_name); ?>
                            </option>
                        </select>
                        <h6 class="form-help"><?php _e('Whenever to enable write-off with B1.', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label for="write_off"><?php _e('Try to sync invoices ', $this->plugin_name); ?></label>
                        <select class="form-control" name="sync_invoices">
                            <option value="1" <?php echo ($this->get_option('sync_invoices')) ? 'selected' : ''; ?>>
                                <?php _e('Yes', $this->plugin_name); ?>
                            </option>
                            <option value="0" <?php echo (!$this->get_option('sync_invoices')) ? 'selected' : ''; ?>>
                                <?php _e('No', $this->plugin_name); ?>
                            </option>
                        </select>
                        <h6 class="form-help"><?php _e('Whenever to enable invoices series and number sync  with B1.', $this->plugin_name); ?>
                        </h6>
                    </div>

                    <div class="form-group">
                        <label for="write_off"><?php _e('Try to sync product price ', $this->plugin_name); ?></label>
                        <select class="form-control" name="sync_item_price">
                            <option value="1" <?php echo ($this->get_option('sync_item_price')) ? 'selected' : ''; ?>>
                                <?php _e('Yes', $this->plugin_name); ?>
                            </option>
                            <option value="0" <?php echo (!$this->get_option('sync_item_price')) ? 'selected' : ''; ?>>
                                <?php _e('No', $this->plugin_name); ?>
                            </option>
                        </select>
                        <h6 class="form-help"><?php _e('Whenever to enable product price sync with B1.', $this->plugin_name); ?>
                        </h6>
                    </div>
                    <div class="form-group">
                        <label for="write_off"><?php _e('Try to sync product name ', $this->plugin_name); ?></label>
                        <select class="form-control" name="sync_item_name">
                            <option value="1" <?php echo ($this->get_option('sync_item_name')) ? 'selected' : ''; ?>>
                                <?php _e('Yes', $this->plugin_name); ?>
                            </option>
                            <option value="0" <?php echo (!$this->get_option('sync_item_name')) ? 'selected' : ''; ?>>
                                <?php _e('No', $this->plugin_name); ?>
                            </option>
                        </select>
                        <h6 class="form-help"><?php _e('Whenever to enable product name sync with B1.', $this->plugin_name); ?>
                        </h6>
                    </div>
                    <div class="form-group">
                        <label for="api_key"><?php _e('API key', $this->plugin_name); ?></label>
                        <input class="form-control" name="api_key" value="<?php echo $this->get_option('api_key'); ?>" type="text">
                        <h6 class="form-help"><?php _e('API key associated with the company.', $this->plugin_name); ?>
                        </h6>
                    </div>
                    <div class="form-group">
                        <label for="private_key"><?php _e('Private key', $this->plugin_name); ?></label>
                        <input class="form-control" name="private_key" value="<?php echo $this->get_option('private_key'); ?>" type="text">
                        <h6 class="form-help"><?php _e('Private key associated with the company.', $this->plugin_name); ?>
                        </h6>
                    </div>
                    <div class="form-group">
                        <label for="access_key"><?php _e('Access key', $this->plugin_name); ?></label>
                        <input class="form-control" name="access_key" value="<?php echo $this->get_option('access_key'); ?>" type="text">
                        <h6 class="form-help"><?php _e('Access key is generated randomly.', $this->plugin_name); ?>
                        </h6>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-cloud-upload-alt"></i> <?php _e('Update', $this->plugin_name); ?>
                        </button>
                        <button class="btn btn-primary" id="b1ResetSettingsBtn">
                            <i class="fas fa-minus-circle"></i> <?php _e('Reset to defaults', $this->plugin_name); ?>
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-12 col-lg-6">
        <div class="margin-bottom-15">
            <button class="btn btn-primary" id="resetB1ReferenceId" data-b1_security="<?php echo $nonce ?>" data-action="reset_all">
                <i class="fa fa-minus-circle" aria-hidden="true"></i> <?php _e(' Reset orders', $this->plugin_name); ?>
            </button>
        </div>
        <form>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fas fa-redo"></i> <?php _e('Commands', $this->plugin_name); ?></h3>
                </div>
                <div class="list-group">
                    <?php foreach ($manager_urls as $data) { ?>
                        <div class="list-group-item">
                            <div class="input-group">
                                <input type="text" class="form-control" value="<?php echo $data['url'] ?>">
                                <div class="input-group-btn">
                                    <a href="<?php echo $data['url'] ?>" type="button " target="_blank" class="btn btn-primary">
                                        <i class="fas fa-play-circle" aria-hidden="true"></i> <?php _e('Run', $this->plugin_name); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </form>
        <form id="importItemsToB1">
            <input class="form-control" name="action" value="b1_item_options_update" type="hidden">
            <input type="hidden" name="b1_security" value="<?php echo $nonce ?>"/>
            <button class="btn btn-primary" role="button" id="getImportDropdownItems" data-toggle="collapse" data-parent="#accordion" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne" data-b1_security="<?php echo $nonce ?>" data-action="get_import_dropdown_items">
                <i class="fas fa-cloud-upload-alt"></i>  <?php _e('Import products to B1.lt (BETA) ', $this->plugin_name); ?>
            </button>
            <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fas fa-cloud-upload-alt"></i> <?php _e('Import products to B1.lt (BETA) ', $this->plugin_name); ?></h3>
                    </div>
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="form-group">
                                <label for="attribute_id"><?php _e('Attribute', $this->plugin_name); ?></label>
                                <select class="form-control" name="attribute_id" id="attribute_id">
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="measurement_unit_id"><?php _e('Measurment', $this->plugin_name); ?></label>
                                <select class="form-control" name="measurement_unit_id" id="measurement_unit_id">
                                </select>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-cloud-upload-alt"></i> <?php _e('Update', $this->plugin_name); ?>
                                </button>
                                <a href="<?php echo $item_url['url'] ?>" type="button " target="_blank" class="btn btn-primary">
                                    <i class="fas fa-play-circle" aria-hidden="true"></i> <?php _e('Run', $this->plugin_name); ?>
                                </a>
                            </div>
                            <div class="alert alert-info">
                                <?php _e('Only products that have SKU will be imported to B1 system.', $this->plugin_name);?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>



