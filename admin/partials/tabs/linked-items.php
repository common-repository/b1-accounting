<?php
if (!defined('WPINC')) {
    die;
}
/**
 * @var B1_Accounting_Admin $this
 */
?>
<div class="row">
    <div class="col-sm-12 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4 margin-bottom-15">
        <button id="btn-reset-all-item-links" type="button" class="btn btn-block btn-primary">
            <i class="fas fa-unlink"></i> <?php _e('Reset all item links', $this->plugin_name); ?>
        </button>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fas fa-link"></i> <?php _e('Linked items', $this->plugin_name); ?>
                </h3>
            </div>
            <div class="panel-body">
                <table id="table-linked-items" class="table table-condensed table-hover table-bordered">
                    <thead>
                    <tr>
                        <th class="width-1"><?php _e('ID (E-shop)', $this->plugin_name); ?></th>
                        <th><?php _e('Name (E-shop)', $this->plugin_name); ?></th>
                        <th class="width-1"><?php _e('ID (B1.lt)', $this->plugin_name); ?></th>
                        <th><?php _e('Name (B1.lt)', $this->plugin_name); ?></th>
                        <th class="width-1"><?php _e('Code (B1.lt)', $this->plugin_name); ?></th>
                        <th class="width-1"></th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div class="alert alert-info" role="alert">
            <?php _e('Link manual', $this->plugin_name); ?>
        </div>
    </div>
</div>