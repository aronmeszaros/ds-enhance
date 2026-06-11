<?php
/**
 * Plugin Name: DS Enhance
 * Description: Lightweight archive and single-post style enhancements for Digitalny Start.
 * Version: 1.4.1
 * Author: Aron Meszaros
 * License: GPL-2.0-or-later
 * Text Domain: ds-enhance
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DSE_VERSION', '1.4.1');
define('DSE_PLUGIN_FILE', __FILE__);
define('DSE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DSE_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once DSE_PLUGIN_PATH . 'includes/class-dse-plugin.php';

function dse_enhance(): DSE_Plugin
{
    return DSE_Plugin::instance();
}

dse_enhance();
