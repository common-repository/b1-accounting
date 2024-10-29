<?php

/**
 * @since 2.0.0
 * @package B1_Accounting
 * @subpackage B1_Accounting/includes
 * @author B1.lt <info@b1.lt>
 * @link https://www.b1.lt
 */
class B1_Accounting_Base
{

    protected $plugin_name;
    protected $version;
    protected $plugin_options = null;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function get_option($key, $default = null)
    {
        $options = $this->get_options();
        if (isset($options[$key])) {
            return $options[$key];
        } else {
            return $default;
        }
    }

    public function has_option($key, $default = false)
    {
        return (bool)$this->get_option($key, $default);
    }

    public function reset_options()
    {
        return $this->plugin_options = get_option($this->plugin_name);
    }

    public function update_options($options)
    {
        update_option($this->plugin_name, $options);
    }

    public function get_options()
    {
        if (empty($this->plugin_options)) {
            $this->plugin_options = get_option($this->plugin_name);
        }
        return is_array($this->plugin_options) ? $this->plugin_options : array();
    }

}