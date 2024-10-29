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
                    <i class="fas fa-file-alt"></i> <?php _e('Data validation', $this->plugin_name); ?>
                </h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive" id="b1ValidationTableLogs">
                </div>

            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-12 col-lg-5">
        <div>
            <div class="margin-bottom-15">
            </div>
            <div id="b1ValidationLogDetailView"></div>
        </div>
    </div>
</div>



