<?php

/*
  Plugin Name: Giveaway
  Plugin URI: http://www.satollo.net/plugins/giveaway
  Description: Giveaway
  Version: 1.0.1
  Author: Satollo
  Author URI: http://www.satollo.net
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

/*
  Copyright 2010 Satollo  (email : satollo@gmail.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$giveaway = new Giveaway();

/** Every WordPress hook managed by this class has a corresponding method prefixed
 * "hook_".
 */
class Giveaway {

    var $title = 'Giveaway';
    var $uid; // Will be the plugin folder name and used as key for plugin options
    var $options = null;
    var $time_limit = 0;

    /** The constructor is called on this file load, so we keep it as light as possible. Other initializations
     * are delegated to "hook_init" called when WordPress initializes the plugins.
     */
    function __construct() {
        // Unique string id for this plugin (its folder name... it's clearly unique)
        $this->uid = basename(dirname(__FILE__));

        // Tries to compute this session time limit, but some time has been already consumed before. 
        // Time limit is the 90% of PHP max execution time.
        $max_time = (int) (ini_get('max_execution_time') * 0.9);
        if ($max_time == 0) $max_time = 3600;
        $this->time_limit = time() + $max_time;

        add_action('init', array(&$this, 'hook_init'));

        // Activation and deactivation hooks (cannot be placed on "hook_init", that
        // function is not called on activation
        register_activation_hook(__FILE__, array(&$this, 'hook_activate'));
        register_deactivation_hook(__FILE__, array(&$this, 'hook_deactivate'));
    }

    function hook_init() {
        add_action('admin_menu', array(&$this, 'hook_admin_menu'));
    }

    function hook_admin_menu() {
        add_options_page($this->title, $this->title, 'manage_options', basename(dirname(__FILE__)) . '/options.php');
    }

    function log($text, $level) {
        if ($this->get_option('log') < $level) return;
        $db = debug_backtrace(false);
        $time = date('d-m-Y H:i:s ');
        switch ($level) {
            case 1: $time .= '- ERROR - '; break;
            case 2: $time .= '- INFO  - '; break;
            case 3: $time .= '- DEBUG - '; break;
        }
        if (is_array($text) || is_object($text)) $text = print_r($text, true);
        file_put_contents(dirname(__FILE__) . '/log.txt', $time . $db[1]['function'] . '() - ' . $text . "\n", FILE_APPEND | FILE_TEXT);
    }
    
    function log_error($text) {
        $this->log($text, 1);
    }

    function log_info($text) {
        $this->log($text, 2);
    }

    function log_debug($text) {
        $this->log($text, 3);
    }

    /** Returns plugin default options. Modify here adding values or loading from file when language dependant. */
    function get_default_options() {
        return array('log'=>0, 'tag'=>'giveaway');
    }

    /** Returns the plugin options. */
    function get_options() {
        if ($this->options == null) $this->options = get_option($this->uid, array());
        return $this->options;
    }

    function set_options($options) {
        update_option($this->uid, $options);
        $this->options = $options;
    }

    /** Returns a plugin option by name */
    function get_option($name, $default=null) {
        if ($this->options == null) $this->options = get_option($this->uid, array());
        $value = $this->options[$name];
        return isset($value)?$value:$default;
    }

    function hook_activate() {
        update_option($this->uid, array_merge($this->get_default_options(), get_option($this->uid, array())));
    }

    function hook_deactivate() {
    }

}
