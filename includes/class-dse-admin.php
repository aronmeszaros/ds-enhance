<?php

if (!defined('ABSPATH')) {
    exit;
}

class DSE_Admin
{
    private string $readme_path;

    public function __construct()
    {
        $this->readme_path = DSE_PLUGIN_PATH . 'README.md';
        add_action('admin_menu', [$this, 'register_menu']);
    }

    public function register_menu(): void
    {
        add_menu_page(
            'DS Enhance',
            'DS Enhance',
            'manage_options',
            'ds-enhance',
            [$this, 'render_page'],
            'dashicons-admin-plugins',
            80
        );
    }

    public function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $content = file_exists($this->readme_path)
            ? file_get_contents($this->readme_path)
            : 'README.md not found.';

        echo '<div class="wrap">';
        echo '<h1>DS Enhance</h1>';
        echo '<div id="dse-readme" style="background:#fff;border:1px solid #c3c4c7;padding:24px 32px;max-width:900px;margin-top:16px;">';
        echo $this->render_markdown($content);
        echo '</div>';
        echo '</div>';
    }

    /**
     * Converts a subset of Markdown to HTML sufficient for a README.
     * Handles: headings, bold, inline code, code blocks, tables, horizontal rules,
     * unordered/ordered lists, and paragraphs.
     */
    private function render_markdown(string $md): string
    {
        $md = esc_html($md);

        // Fenced code blocks (``` ... ```)
        $md = preg_replace_callback(
            '/```[^\n]*\n(.*?)```/s',
            fn($m) => '<pre style="background:#f6f7f7;padding:12px 16px;overflow:auto;border:1px solid #dcdcde;border-radius:3px;"><code>' . $m[1] . '</code></pre>',
            $md
        );

        // Horizontal rules
        $md = preg_replace('/^-{3,}$/m', '<hr>', $md);

        // Headings
        $md = preg_replace('/^#### (.+)$/m', '<h4>$1</h4>', $md);
        $md = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $md);
        $md = preg_replace('/^## (.+)$/m', '<h2 style="border-bottom:1px solid #dcdcde;padding-bottom:6px;margin-top:28px;">$1</h2>', $md);
        $md = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $md);

        // Bold
        $md = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $md);

        // Inline code
        $md = preg_replace('/`([^`]+)`/', '<code style="background:#f6f7f7;padding:2px 5px;border-radius:3px;font-size:13px;">$1</code>', $md);

        // Tables — header row + separator + data rows
        $md = preg_replace_callback(
            '/(\|.+\|\n)\|[-| :]+\|\n((?:\|.+\|\n?)*)/m',
            function ($m) {
                $header_cells = array_map('trim', explode('|', trim($m[1], "|\n")));
                $header_html = '<tr>' . implode('', array_map(fn($c) => '<th style="text-align:left;padding:6px 12px;border-bottom:2px solid #dcdcde;">' . $c . '</th>', $header_cells)) . '</tr>';

                $body_html = '';
                foreach (explode("\n", trim($m[2])) as $row) {
                    if ($row === '') continue;
                    $cells = array_map('trim', explode('|', trim($row, '|')));
                    $body_html .= '<tr>' . implode('', array_map(fn($c) => '<td style="padding:6px 12px;border-bottom:1px solid #f0f0f1;">' . $c . '</td>', $cells)) . '</tr>';
                }

                return '<table style="border-collapse:collapse;width:100%;margin:16px 0;">' . $header_html . $body_html . '</table>';
            },
            $md
        );

        // Unordered lists
        $md = preg_replace_callback(
            '/((?:^- .+\n?)+)/m',
            fn($m) => '<ul>' . preg_replace('/^- (.+)$/m', '<li>$1</li>', $m[1]) . '</ul>',
            $md
        );

        // Ordered lists
        $md = preg_replace_callback(
            '/((?:^\d+\. .+\n?)+)/m',
            fn($m) => '<ol>' . preg_replace('/^\d+\. (.+)$/m', '<li>$1</li>', $m[1]) . '</ol>',
            $md
        );

        // Paragraphs — blank-line separated blocks not already wrapped in a block element
        $blocks = preg_split('/\n{2,}/', $md);
        $block_tags = ['<h1', '<h2', '<h3', '<h4', '<ul', '<ol', '<pre', '<table', '<hr'];
        $html = '';
        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') continue;
            $is_block = false;
            foreach ($block_tags as $tag) {
                if (str_starts_with($block, $tag)) {
                    $is_block = true;
                    break;
                }
            }
            $html .= $is_block ? $block . "\n" : '<p>' . nl2br($block) . "</p>\n";
        }

        return $html;
    }
}
