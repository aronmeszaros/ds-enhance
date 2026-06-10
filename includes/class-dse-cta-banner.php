<?php

if (!defined('ABSPATH')) {
    exit;
}

class DSE_CTA_Banner
{
    public function __construct()
    {
        add_action('init', [$this, 'register_block']);
    }

    public function register_block(): void
    {
        register_block_type(
            DSE_PLUGIN_PATH . 'blocks/cta-banner',
            [
                'render_callback' => [$this, 'render'],
            ]
        );
    }

    public function render(array $attributes = [], string $content = '', ?WP_Block $block = null): string
    {
        if (!is_singular('post')) {
            return '';
        }

        $defaults = [
            'heading' => 'Objav, co v tebe je',
            'text' => 'Vypln niekolko kratkych testov zdarma a zistis, v com si dobry a kam sa mozes posunut',
            'button_label' => 'Chcem sa otestovat',
            'button_url' => home_url('/jrp/ds'),
            'image_url' => '',
            'image_alt' => 'Vyzva na otestovanie',
        ];

        $config = apply_filters('dse_cta_banner_config', $defaults);

        $heading = wp_kses_post((string) ($config['heading'] ?? $defaults['heading']));
        $text = wp_kses_post((string) ($config['text'] ?? $defaults['text']));
        $button_label = wp_kses_post((string) ($config['button_label'] ?? $defaults['button_label']));
        $button_url = esc_url((string) ($config['button_url'] ?? $defaults['button_url']));
        $image_url = esc_url((string) ($config['image_url'] ?? $defaults['image_url']));
        $image_alt = esc_attr((string) ($config['image_alt'] ?? $defaults['image_alt']));

        ob_start();
        ?>
        <section class="dse-cta-banner" aria-label="<?php echo esc_attr__('Vyzva na akciu', 'ds-enhance'); ?>">
            <div class="dse-cta-banner__inner">
                <div class="dse-cta-banner__content">
                    <h2 class="dse-cta-banner__heading"><?php echo $heading; ?></h2>
                    <p class="dse-cta-banner__text"><?php echo $text; ?></p>
                    <div class="materialize-button-wrapper">
                        <a href="<?php echo $button_url; ?>" class="materialize-button btn elevated btn-large waves-effect waves-light btn-rounded btn-dark">
                            <span class="button-text"><?php echo $button_label; ?></span>
                        </a>
                    </div>
                </div>
                <?php if (!empty($image_url)): ?>
                    <div class="dse-cta-banner__image">
                        <img src="<?php echo $image_url; ?>" alt="<?php echo $image_alt; ?>" loading="lazy" decoding="async" />
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
