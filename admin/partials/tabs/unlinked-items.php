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
        <button id="btn-link-all-items-by-code" type="button" class="btn btn-block btn-primary"><i class="fas fa-link"></i> <?php _e('Link all products by code', $this->plugin_name); ?></button>
    </div>
</div>
<form id="form-link" method="post">
    <input class="form-control" name="action" value="b1_link_item" type="hidden">
    <input type="hidden" name="b1_security" value="<?php echo $nonce ?>"/>
    <div class="row">
        <div class="col-sm-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php _e('E-shop items', $this->plugin_name); ?></h3>
                </div>
                <div class="panel-body">
                    <table id="table-shop-unlinked-items" class="table table-condensed table-hover table-bordered">
                        <thead>
                        <tr>
                            <th class="width-1"></th>
                            <th class="width-1"><?php _e('Is linked?', $this->plugin_name); ?></th>
                            <th class="width-1"><?php _e('ID', $this->plugin_name); ?></th>
                            <th><?php _e('Name', $this->plugin_name); ?></th>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <select class="form-control">
                                    <option value="">

                                    </option>
                                    <option value="1">
                                        <?php _e('Yes', $this->plugin_name); ?>
                                    </option>
                                    <option value="0">
                                        <?php _e('No', $this->plugin_name); ?>
                                    </option>
                                </select>
                            </td>
                            <td></td>
                            <td><input type="text" class="form-control"/></td>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-sm-2 text-center">
            <button type="submit" class="btn btn-block btn-primary btn-link-items">
                <i class="fas fa-link"></i> <?php _e('Link', $this->plugin_name); ?>
            </button>
        </div>
        <div class="col-sm-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php _e('B1.lt items', $this->plugin_name); ?></h3>
                </div>
                <div class="panel-body">
                    <table id="table-b1-unlinked-items" class="table table-condensed table-hover table-bordered">
                        <thead>
                        <tr>
                            <th class="width-1"></th>
                            <th class="width-1"><?php _e('Is linked?', $this->plugin_name); ?></th>
                            <th class="width-1"><?php _e('ID', $this->plugin_name); ?></th>
                            <th><?php _e('Name', $this->plugin_name); ?></th>
                            <th><?php _e('Code', $this->plugin_name); ?></th>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <select class="form-control">
                                    <option value="">

                                    </option>
                                    <option value="1">
                                        <?php _e('Yes', $this->plugin_name); ?>
                                    </option>
                                    <option value="0">
                                        <?php _e('No', $this->plugin_name); ?>
                                    </option>
                                </select>
                            </td>
                            <td></td>
                            <td><input type="text" class="form-control"/></td>
                            <td><input type="text" class="form-control"/></td>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-info" role="alert"><?php _e('Unlink manual', $this->plugin_name); ?></div>
        </div>
    </div>
</form>