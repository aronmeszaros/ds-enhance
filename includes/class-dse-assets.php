<?php

if (!defined('ABSPATH')) {
    exit;
}

class DSE_Assets
{
    private bool $move_single_thumbnail = false;
    private bool $template_thumbnail_suppressed = false;
    private bool $thumbnail_injected = false;

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('wp', [$this, 'setup_single_thumbnail_reorder']);
    }

    public function setup_single_thumbnail_reorder(): void
    {
        if (is_admin() || !is_singular('post')) {
            return;
        }

        $this->move_single_thumbnail = true;
        add_filter('has_post_thumbnail', [$this, 'suppress_template_thumbnail_block'], 10, 3);
        add_filter('the_content', [$this, 'inject_thumbnail_before_main_text'], 5);
    }

    /**
     * Skip the first has_post_thumbnail() check for the main post template
     * so the original top thumbnail block is not rendered.
     *
     * @param bool     $has_thumbnail Whether current post has a featured image.
     * @param WP_Post  $post          Post object passed by core.
     * @param int|bool $thumbnail_id  Thumbnail attachment ID or false.
     */
    public function suppress_template_thumbnail_block(bool $has_thumbnail, $post, $thumbnail_id): bool
    {
        if (!$this->move_single_thumbnail || $this->template_thumbnail_suppressed || !$has_thumbnail) {
            return $has_thumbnail;
        }

        if (!in_the_loop() || !is_main_query()) {
            return $has_thumbnail;
        }

        $current_post_id = is_object($post) && isset($post->ID) ? (int) $post->ID : 0;
        if ($current_post_id !== (int) get_queried_object_id()) {
            return $has_thumbnail;
        }

        $this->template_thumbnail_suppressed = true;
        return false;
    }

    public function inject_thumbnail_before_main_text(string $content): string
    {
        if (!$this->move_single_thumbnail || !$this->template_thumbnail_suppressed || $this->thumbnail_injected) {
            return $content;
        }

        if (!in_the_loop() || !is_main_query()) {
            return $content;
        }

        $post_id = get_the_ID();
        if (!$post_id || $post_id !== (int) get_queried_object_id() || !has_post_thumbnail($post_id)) {
            return $content;
        }

        $thumbnail_html = get_the_post_thumbnail($post_id, 'large', ['class' => 'img-fluid']);
        if ($thumbnail_html === '') {
            return $content;
        }

        $thumbnail_wrapper_class = 'post-thumbnail';
        $position = get_post_meta($post_id, 'umiestnenie_obrazka', true);
        if (in_array($position, ['Hore', 'Stred', 'Dole'], true)) {
            $thumbnail_wrapper_class .= ' position-' . sanitize_html_class($position);
        }

        $this->thumbnail_injected = true;

        return '<div class="' . esc_attr($thumbnail_wrapper_class) . '">' . $thumbnail_html . '</div>' . $content;
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
