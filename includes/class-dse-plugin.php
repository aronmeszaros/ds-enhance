<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once DSE_PLUGIN_PATH . 'includes/class-dse-assets.php';
require_once DSE_PLUGIN_PATH . 'includes/class-dse-admin.php';

class DSE_Plugin
{
    private static ?DSE_Plugin $instance = null;

    private DSE_Assets $assets;
    private DSE_Admin $admin;

    private function __construct()
    {
        $this->assets = new DSE_Assets();
        $this->admin = new DSE_Admin();
    }

    public static function instance(): DSE_Plugin
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
