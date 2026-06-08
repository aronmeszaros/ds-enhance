<?php

if (!defined('ABSPATH')) {
    exit;
}

class DSE_CTA_Tracking
{
    private const DEFAULT_EVENT_NAME = 'cta_click';

    public function __construct()
    {
        add_filter('the_content', [$this, 'inject_cta_tracking_attributes'], 20);
        add_action('wp_enqueue_scripts', [$this, 'localize_frontend_config'], 20);
    }

    public function inject_cta_tracking_attributes(string $content): string
    {
        if (is_admin() || !is_singular() || trim($content) === '') {
            return $content;
        }

        if (stripos($content, 'wp-block-button__link') === false) {
            return $content;
        }

        if (!class_exists('DOMDocument')) {
            return $content;
        }

        $dom = new DOMDocument();
        $previous_errors = libxml_use_internal_errors(true);

        try {
            $wrapped_content = '<!DOCTYPE html><html><body><div id="dse-cta-root">' . $content . '</div></body></html>';
            $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped_content);

            if (!$loaded) {
                libxml_clear_errors();
                libxml_use_internal_errors($previous_errors);
                return $content;
            }

            $links = $dom->getElementsByTagName('a');
            $map = $this->get_cta_map();

            foreach ($links as $link) {
                if (!$this->is_cta_button_link($link)) {
                    continue;
                }

                if (!$link->hasAttribute('data-gtm-event')) {
                    $link->setAttribute('data-gtm-event', self::DEFAULT_EVENT_NAME);
                }

                if (!$link->hasAttribute('data-gtm-name')) {
                    $resolved_name = $this->resolve_cta_name(
                        $link->getAttribute('href'),
                        $map,
                        $link->textContent
                    );

                    if ($resolved_name !== '') {
                        $link->setAttribute('data-gtm-name', $resolved_name);
                    }
                }
            }

            $root = $dom->getElementById('dse-cta-root');
            if (!$root) {
                libxml_clear_errors();
                libxml_use_internal_errors($previous_errors);
                return $content;
            }

            $updated_content = '';
            foreach ($root->childNodes as $child) {
                $updated_content .= $dom->saveHTML($child);
            }

            libxml_clear_errors();
            libxml_use_internal_errors($previous_errors);

            return $updated_content;
        } catch (Throwable $throwable) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous_errors);
            return $content;
        }
    }

    public function localize_frontend_config(): void
    {
        $map = $this->get_cta_map();

        wp_localize_script(
            'dse-cta-tracking',
            'dseCtaTracking',
            [
                'eventName' => self::DEFAULT_EVENT_NAME,
                'ctaMap' => $map,
                'linkSelector' => 'a.wp-block-button__link',
            ]
        );
    }

    private function get_cta_map(): array
    {
        $map = [
            '/registracia' => 'registracia',
        ];

        $map = apply_filters('dse_cta_tracking_map', $map);

        if (!is_array($map)) {
            return [];
        }

        $normalized = [];
        foreach ($map as $href => $name) {
            if (!is_string($href) || !is_string($name)) {
                continue;
            }

            $normalized_href = $this->normalize_href($href);
            $normalized_name = $this->normalize_cta_name($name);

            if ($normalized_href === '' || $normalized_name === '') {
                continue;
            }

            $normalized[$normalized_href] = $normalized_name;
        }

        return $normalized;
    }

    private function is_cta_button_link(DOMElement $link): bool
    {
        $class_names = $link->getAttribute('class');
        return stripos($class_names, 'wp-block-button__link') !== false;
    }

    private function resolve_cta_name(string $href, array $map, string $text): string
    {
        $normalized_href = $this->normalize_href($href);

        if ($normalized_href !== '' && isset($map[$normalized_href])) {
            return $map[$normalized_href];
        }

        $from_text = $this->normalize_cta_name($text);
        if ($from_text !== '') {
            return $from_text;
        }

        if ($normalized_href === '' || $normalized_href === '/') {
            return '';
        }

        $path_tail = basename($normalized_href);
        return $this->normalize_cta_name($path_tail);
    }

    private function normalize_href(string $href): string
    {
        $href = trim($href);
        if ($href === '') {
            return '';
        }

        $path = wp_parse_url($href, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return '';
        }

        $path = '/' . ltrim($path, '/');
        if (strlen($path) > 1) {
            $path = rtrim($path, '/');
        }

        return strtolower($path);
    }

    private function normalize_cta_name(string $value): string
    {
        $value = sanitize_title($value);
        $value = str_replace('-', '_', $value);
        return trim($value, '_');
    }
}
