<?php
/*
Plugin Name: Cart66 Cloud
Plugin URI: http://cart66.com
Description: Securely Hosted Ecommerce For WordPress
Version: 1.7.3
Author: Reality66
Author URI: http://www.reality66.com

-------------------------------------------------------------------------
Cart66 Cloud
Copyright 2014  Reality66

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if(!defined('CC_PATH')) {
  $plugin_file = __FILE__;
  if(isset($plugin)) { $plugin_file = $plugin; }
  elseif (isset($mu_plugin)) { $plugin_file = $mu_plugin; }
  elseif (isset($network_plugin)) { $plugin_file = $network_plugin; }

  define('CC_PATH', WP_PLUGIN_DIR . '/' . basename(dirname($plugin_file)) . '/');
  define('CC_URL',  WP_PLUGIN_URL . '/' . basename(dirname($plugin_file)) . '/');
}

if(!class_exists('CC_Loader')) {
  require 'autoloader.php';

  // IS_ADMIN is true when the dashboard or the administration panels are displayed
  if(!defined('IS_ADMIN')) {
    define("IS_ADMIN",  is_admin());
  }

  if(!defined("CC_CURRENT_PAGE")) {
    define("CC_CURRENT_PAGE", basename($_SERVER['PHP_SELF']));
  }

  $cart66 = new CC_Cart66Cloud();
}
