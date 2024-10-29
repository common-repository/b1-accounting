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
                    <i class="fas fa-list"></i> <?php _e('Logs', $this->plugin_name); ?>
                </h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive" id="b1TableLogs">
                </div>

            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-12 col-lg-5">
        <div>
            <div class="margin-bottom-15">
                <button type="submit" class="btn btn-primary" id="b1DownloadBtn">
                    <i class="fas fa-cloud-download-alt"></i> <?php _e('Export', $this->plugin_name); ?>
                </button>
                <span id="b1DownloadLink" class="margin-left-15"></span>
            </div>
            <div id="b1LogDetailView"></div>
        </div>
    </div>
</div>



