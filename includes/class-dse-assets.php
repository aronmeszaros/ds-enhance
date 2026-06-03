<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once DSE_PLUGIN_PATH . 'includes/class-dse-thumbnail-reorder.php';

class DSE_Assets
{
    private DSE_Thumbnail_Reorder $thumbnail_reorder;

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        $this->thumbnail_reorder = new DSE_Thumbnail_Reorder();
    }

    public function enqueue_frontend_assets(): void
    {
        wp_enqueue_style(
            'dse-frontend',
            DSE_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            DSE_VERSION
        );

        $script_dependencies = ['jquery'];

        // Reuse slick handles if the active theme/plugin already registers them.
        if (wp_script_is('slick', 'registered')) {
            $script_dependencies[] = 'slick';
        } elseif (wp_script_is('jquery-slick', 'registered')) {
            $script_dependencies[] = 'jquery-slick';
        }

        wp_enqueue_script(
            'dse-frontend',
            DSE_PLUGIN_URL . 'assets/js/frontend.js',
            $script_dependencies,
            DSE_VERSION,
            true
        );
    }
}
