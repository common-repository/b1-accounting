<?php

/**
 * @link https://www.b1.lt
 * @since 2.0.0
 * @package B1_Accounting
 *
 * @wordpress-plugin
 * Plugin Name: B1.lt for WooCommerce
 * Plugin URI: https://www.b1.lt
 * Description: Integration with B1.lt
 * Version: 2.2.55
 * Author: B1.lt
 * Author URI: https://www.b1.lt
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: b1-accounting
 * Domain Path: /languages
 * Requires at least: 4.9.4
 * Tested up to: 6.0.3
 */
if (!defined('WPINC')) {
    die;
}

define('B1_ACCOUNTING_VERSION', '2.2.55');
require_once plugin_dir_path(__FILE__) . 'includes/class-b1-accounting.php';

function activate_b1_accounting()
{
    if (!defined('PHP_VERSION_ID') || (PHP_VERSION_ID < 70000)) {
        deactivate_plugins(__FILE__);
        $error_message = __('The B1.lt For WooCommerce plugin requires min PHP version 7.0!', 'woocommerce');
        wp_die($error_message);
    }
    require_once plugin_dir_path(__FILE__) . 'includes/class-b1-accounting-activator.php';
    B1_Accounting_Activator::activate();
}

function deactivate_b1_accounting()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-b1-accounting-deactivator.php';
    B1_Accounting_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_b1_accounting');
register_deactivation_hook(__FILE__, 'deactivate_b1_accounting');
register_uninstall_hook(__FILE__, 'deactivate_b1_accounting');


/**
 * @since 2.0.0
 */
function run_b1_accounting()
{
    $plugin = new B1_Accounting();
    $plugin->run();
}

run_b1_accounting();
