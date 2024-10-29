<?php

/**
 * @since 2.0.0
 * @package B1_Accounting
 * @subpackage B1_Accounting/includes
 * @author B1.lt <info@b1.lt>
 * @link https://www.b1.lt
 */
class B1_Accounting_i18n
{

    public function load_plugin_textdomain()
    {
        load_plugin_textdomain(
            'b1-accounting',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

}
