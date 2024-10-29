<?php
if (!defined('WPINC')) {
    die;
}
/**
 * @since 2.0.0
 * @package B1_Accounting
 * @subpackage B1_Accounting/admin/partials
 * @author B1.lt <info@b1.lt>
 * @link https://www.b1.lt
 * @var B1_Accounting_Admin $this
 */
$nonce = wp_create_nonce('b1_security');
?>
<div class="wrap bootstrap-wrapper">
    <h1><?php _e('B1.lt accounting', $this->plugin_name); ?>
        <a href="mailto:<?php echo $this->get_option('contact_email'); ?>" data-toggle="tooltip" title="<?php _e('Contacts', $this->plugin_name); ?>" class="btn btn-default">
            <i class="fas fa-envelope"></i>
            <span class="hidden-xs"><?php _e('Contacts', $this->plugin_name); ?></span>
        </a>
        <a href="<?php echo $this->get_option('documentation_url'); ?>" data-toggle="tooltip" title="<?php _e('Documentation', $this->plugin_name); ?>" target="_blank" class="btn btn-default">
            <i class="fas fa-book"></i>
            <span class="hidden-xs"><?php _e('Documentation', 'b1-accounting'); ?></span>
        </a>
        <a href="<?php echo $this->get_option('help_page_url'); ?>" data-toggle="tooltip" title="<?php _e('Help', $this->plugin_name); ?>" target="_blank" class="btn btn-default">
            <i class="fas fa-book"></i>
            <span class="hidden-xs"><?php _e('Help', $this->plugin_name); ?></span>
        </a>
    </h1>
    <div class="error notice hidden" id="b1-error-alert">
        <p></p>
    </div>
    <div class="updated notice hidden" id="b1-success-alert">
        <p></p>
    </div>
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="#settings" data-toggle="tab">
                <i class="fas fa-cogs"></i>
                <?php _e('Settings', $this->plugin_name); ?>
            </a>
        </li>
        <li>
            <a href="#mapping" data-toggle="tab">
                <i class="fas fa-columns"></i>
                <?php _e('Field mapping', $this->plugin_name); ?>
            </a>
        </li>
        <li>
            <a href="#logs" data-toggle="tab">
                <i class="fas fa-list"></i>
                <?php _e('Logs', $this->plugin_name); ?>
            </a>
        </li>
        <li>
            <a href="#validations" data-toggle="tab">
                <i class="fas fa-file-alt"></i>
                <?php _e('Data validation', $this->plugin_name); ?>
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active padding-top-10" id="settings">
            <?php include_once 'tabs/settings.php'; ?>
        </div>
        <div class="tab-pane padding-top-10" id="mapping">
            <?php include_once 'tabs/mapping.php'; ?>
        </div>
        <div class="tab-pane padding-top-10" id="logs">
            <?php include_once 'tabs/logs.php'; ?>
        </div>
        <div class="tab-pane padding-top-10" id="validations">
            <?php include_once 'tabs/validations.php'; ?>
        </div>
    </div>

</div>
