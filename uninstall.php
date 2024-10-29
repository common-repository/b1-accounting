<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package B1_Accounting
 * @author B1.lt <info@b1.lt>
 * @license GPL-2.0+
 * @link https://www.b1.lt
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Treat uninstall as deactivation
require_once plugin_dir_path(__FILE__) . 'includes/class-b1-accounting-deactivator.php';
B1_Accounting_Deactivator::deactivate();