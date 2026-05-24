<?php

if (!defined('ABSPATH')) {
    exit;
}

class DSE_Assets
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('wp_head', [$this, 'print_thumbnail_position_script']);
    }

    public function print_thumbnail_position_script(): void
    {
        if (!is_single() || get_post_type() !== 'post') {
            return;
        }

        $post_id = get_the_ID();
        if (!$post_id) {
            return;
        }

        $position = get_post_meta($post_id, 'umiestnenie_obrazka', true);

        if (!in_array($position, ['Hore', 'Stred', 'Dole'], true)) {
            return;
        }

        $class = esc_js('position-' . $position);

        echo '<script>document.addEventListener("DOMContentLoaded",function(){var t=document.querySelector(".post-thumbnail");if(t)t.classList.add("' . $class . '");});</script>' . "\n";
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
