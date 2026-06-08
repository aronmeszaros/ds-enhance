(function () {
    'use strict';

    function normalizeCtaName(value) {
        if (!value) {
            return '';
        }

        return value
            .toString()
            .trim()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }

    function normalizeHrefPath(href) {
        if (!href) {
            return '';
        }

        try {
            var url = new URL(href, window.location.origin);
            var path = (url.pathname || '').toLowerCase();

            if (!path) {
                return '';
            }

            if (path.length > 1) {
                path = path.replace(/\/+$/, '');
            }

            return path;
        } catch (error) {
            return '';
        }
    }

    function getCtaTrackingConfig() {
        var globalConfig = window.dseCtaTracking || {};

        return {
            eventName: normalizeCtaName(globalConfig.eventName || 'cta_click') || 'cta_click',
            ctaMap: globalConfig.ctaMap || {},
            linkSelector: globalConfig.linkSelector || 'a.wp-block-button__link'
        };
    }

    function resolveCtaName(el, config) {
        if (!el) {
            return '';
        }

        if (el.dataset && el.dataset.gtmName) {
            return normalizeCtaName(el.dataset.gtmName);
        }

        var hrefPath = normalizeHrefPath(el.getAttribute('href'));
        if (hrefPath && config.ctaMap[hrefPath]) {
            return normalizeCtaName(config.ctaMap[hrefPath]);
        }

        var textName = normalizeCtaName(el.textContent || '');
        if (textName) {
            return textName;
        }

        if (hrefPath && hrefPath !== '/') {
            var chunks = hrefPath.split('/').filter(Boolean);
            return normalizeCtaName(chunks[chunks.length - 1] || '');
        }

        return '';
    }

    function annotateCtaLinks() {
        var config = getCtaTrackingConfig();

        document.querySelectorAll(config.linkSelector).forEach(function (link) {
            if (!link.closest('article')) {
                return;
            }

            if (!link.dataset.gtmEvent) {
                link.dataset.gtmEvent = config.eventName;
            }

            if (!link.dataset.gtmName) {
                var ctaName = resolveCtaName(link, config);
                if (ctaName) {
                    link.dataset.gtmName = ctaName;
                }
            }
        });
    }

    function bindGtmTracking() {
        document.removeEventListener('click', window.dseCtaClickHandler);

        window.dseCtaClickHandler = function (event) {
            var el = event.target.closest('[data-gtm-event]');
            if (!el) {
                return;
            }

            var config = getCtaTrackingConfig();
            if (!el.dataset.gtmName) {
                el.dataset.gtmName = resolveCtaName(el, config);
            }

            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                event: el.dataset.gtmEvent,
                cta_name: el.dataset.gtmName || ''
            });
        };

        document.addEventListener('click', window.dseCtaClickHandler);
    }

    function initCtaTracking() {
        annotateCtaLinks();
        bindGtmTracking();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCtaTracking);
    } else {
        initCtaTracking();
    }

    window.addEventListener('load', annotateCtaLinks);
})();
