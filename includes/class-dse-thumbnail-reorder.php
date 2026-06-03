<?php

if (!defined('ABSPATH')) {
    exit;
}

class DSE_Thumbnail_Reorder
{
    private bool $thumbnail_injected = false;

    public function __construct()
    {
        add_filter('the_content', [$this, 'inject_thumbnail_before_main_text'], 5);
    }

    public function inject_thumbnail_before_main_text(string $content): string
    {
        if (is_admin() || !is_singular('post') || $this->thumbnail_injected) {
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
}