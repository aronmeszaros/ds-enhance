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
            linkSelector: globalConfig.linkSelector || 'a.wp-block-button__link, a[data-dse-cta-banner="1"]'
        };
    }

    function getSafeLinkElements(selector) {
        if (!selector || !document.querySelectorAll) {
            return [];
        }

        try {
            return document.querySelectorAll(selector);
        } catch (error) {
            return [];
        }
    }

    function getClosestElement(target, selector) {
        if (!target || !selector) {
            return null;
        }

        var element = target.nodeType === Node.ELEMENT_NODE
            ? target
            : target.parentElement;

        if (!element || typeof element.closest !== 'function') {
            return null;
        }

        return element.closest(selector);
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

    function isTrackableCtaLink(link) {
        if (!link || typeof link.matches !== 'function') {
            return false;
        }

        if (link.getAttribute('data-dse-cta-banner') === '1') {
            return true;
        }

        return link.matches('a.wp-block-button__link') && !!link.closest('article');
    }

    function annotateCtaLinks() {
        var config = getCtaTrackingConfig();
        var links = getSafeLinkElements(config.linkSelector);

        if (!links || typeof links.forEach !== 'function') {
            return;
        }

        links.forEach(function (link) {
            if (!isTrackableCtaLink(link)) {
                return;
            }

            try {
                if (!link.dataset.gtmEvent) {
                    link.dataset.gtmEvent = config.eventName;
                }

                if (!link.dataset.gtmName) {
                    var ctaName = resolveCtaName(link, config);
                    if (ctaName) {
                        link.dataset.gtmName = ctaName;
                    }
                }
            } catch (error) {
                // Never break page interaction if one CTA cannot be annotated.
            }
        });
    }

    function bindGtmTracking() {
        document.removeEventListener('pointerdown', window.dseCtaClickHandler, true);
        document.removeEventListener('click', window.dseCtaClickHandler, true);

        var lastTrackedHref = '';
        var lastTrackedAt = 0;

        function shouldSkipDuplicate(el) {
            var href = el && typeof el.getAttribute === 'function'
                ? el.getAttribute('href') || ''
                : '';
            var now = Date.now();

            if (href === lastTrackedHref && now - lastTrackedAt < 1000) {
                return true;
            }

            lastTrackedHref = href;
            lastTrackedAt = now;
            return false;
        }

        window.dseCtaClickHandler = function (event) {
            var target = event && event.target;
            if (!target) {
                return;
            }

            var el = getClosestElement(target, '[data-gtm-event]');
            if (!isTrackableCtaLink(el)) {
                return;
            }

            if (shouldSkipDuplicate(el)) {
                return;
            }

            try {
                var config = getCtaTrackingConfig();
                if (!el.dataset.gtmName) {
                    el.dataset.gtmName = resolveCtaName(el, config);
                }

                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    event: el.dataset.gtmEvent,
                    cta_name: el.dataset.gtmName || ''
                });
            } catch (error) {
                // Do not block the click if tracking fails.
            }
        };

        document.addEventListener('pointerdown', window.dseCtaClickHandler, true);
        document.addEventListener('click', window.dseCtaClickHandler, true);
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
