# DS Enhance

Lightweight front-end enhancements for the Digitalny Start WordPress site.

---

## Features

### 1. Frontend CSS
Enqueues `assets/css/frontend.css` on every public page.  
Contains styles for:
- **Category post cards** – square `1:1` card images with `object-fit: cover`
- **Single post thumbnail** – full-width hero image capped at `466px` height
- **Thumbnail position classes** – ACF-controlled `object-position` via CSS classes (see below)

### 2. ACF Thumbnail Position (single posts)
An ACF Radio Button field named `umiestnenie_obrazka` controls the vertical focal point of the post hero image.

| ACF value | CSS class added          | Effect                      |
|-----------|--------------------------|-----------------------------|
| Hore      | `position-Hore`          | `object-position: top`      |
| Stred     | `position-Stred`         | `object-position: center`   |
| Dole      | `position-Dole`          | `object-position: bottom`   |

The plugin reads the field on every single-post page and injects a small inline script in `<head>` that adds the appropriate class to the `.post-thumbnail` wrapper div. No theme template changes are required.

**ACF field setup:**
- Field group: *Blog*
- Field label: *Umiestnenie obrázka*
- Field name: `umiestnenie_obrazka`
- Field type: Radio Button
- Choices: `Hore`, `Stred`, `Dole`

### 3. Related Posts Carousel (single posts)
The plugin now initializes the related-posts carousel (`.slick-carousel`) on single-post pages and wires the custom arrow controls.

**What it does:**
- Reads carousel settings from HTML data attributes:
    - `data-slides-to-show`
    - `data-slides-to-scroll`
    - `data-autoplay`
    - `data-autoplay-speed`
    - `data-infinite`
- Binds controls in `.digi-posts-arrows[data-carousel-id="..."]`
- Uses `.slick-prev-custom` and `.slick-next-custom` buttons to navigate
- Updates button disabled states at carousel boundaries
- Preserves GA tracking via delegated click handling on `[data-ga-event]`

**Notes:**
- `assets/js/frontend.js` is enqueued in footer with `jquery` dependency.
- If Slick is already registered by theme or another plugin (`slick` or `jquery-slick`), DS Enhance reuses that handle.
- If Slick is not loaded on the page, initialization safely skips.

### 4. CTA GTM Tracking (single posts)
The plugin automatically adds GTM attributes to article CTA button links and pushes events to `dataLayer` on click.

**Supported CTA markup (example):**
```html
<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
    <div class="wp-block-button">
        <a class="wp-block-button__link wp-element-button" href="/registracia">Sprav si test</a>
    </div>
</div>
```

**What it does automatically:**
- Targets CTA links matching `a.wp-block-button__link` inside article content
- Sets `data-gtm-event="cta_click"` when missing
- Sets `data-gtm-name` dynamically:
    - from a link-to-name dictionary (URL path map), or
    - from link text, or
    - from the URL path tail as fallback
- Sends click event to `window.dataLayer` with payload:
    - `event` (from `data-gtm-event`)
    - `cta_name` (from `data-gtm-name`)

**Default mapping:**
- `/registracia` => `sprav_si_test`

**How to extend mapping in WordPress (no plugin edits required):**
```php
add_filter('dse_cta_tracking_map', function ($map) {
        $map['/registracia'] = 'sprav_si_test';
        $map['/ine-kroky'] = 'ine_kroky';
        return $map;
});
```

**Implementation details:**
- `includes/class-dse-cta-tracking.php`
    - enriches rendered post content (`the_content`) with missing GTM attributes
    - localizes runtime config (`dseCtaTracking`) for frontend JS
- `assets/js/cta-tracking.js`
    - annotates CTA links client-side (safety net)
    - binds delegated click listener and pushes GTM events

---

## File Structure

```
ds-enhance/
├── ds-enhance.php              # Plugin bootstrap (constants, loader)
├── README.md                   # This file
├── assets/
│   └── css/
│       └── frontend.css        # All front-end styles
│   └── js/
│       ├── frontend.js         # Carousel init + arrow controls + GA click tracking
│       └── cta-tracking.js     # CTA GTM attribute enrichment + click tracking
└── includes/
    ├── class-dse-plugin.php    # Singleton, hooks registration
    ├── class-dse-assets.php    # Enqueues frontend CSS/JS
    ├── class-dse-admin.php     # Admin menu page (shows this README)
    └── class-dse-cta-tracking.php # CTA GTM tracking integration
```

---

## Requirements

- WordPress 6.0+
- PHP 8.0+
- Advanced Custom Fields (ACF) plugin — required for the thumbnail position feature
- Slick Carousel JS — required on pages where related-posts carousel should run

---

## Changelog

### 1.0.0
- Initial release
- Frontend CSS enqueue
- ACF-based thumbnail position class injection
- Related-posts Slick carousel initialization with custom arrow controls
### 1.3.0
- Dynamically add CTA button GTM attributes and Javascript events
- Add Styling adjustemnts to Archive page
