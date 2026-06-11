(function (blocks, element, i18n) {
    if (!blocks || !element) {
        return;
    }

    var el = element.createElement;
    var __ = i18n && i18n.__ ? i18n.__ : function (text) { return text; };

    blocks.registerBlockType('dse/cta-banner', {
        edit: function () {
            return el(
                'div',
                {
                    className: 'dse-cta-banner-editor-placeholder',
                    style: {
                        border: '1px dashed #b8c2cc',
                        borderRadius: '12px',
                        padding: '20px',
                        background: '#f8fafc'
                    }
                },
                el('strong', null, __('CTA Banner', 'ds-enhance')),
                el(
                    'p',
                    { style: { marginTop: '8px', marginBottom: 0 } },
                    __('Tento blok zobrazí CTA banner na frontende. Ulož článok, a pozri si výsledok na stránke.', 'ds-enhance')
                )
            );
        },
        save: function () {
            return null;
        }
    });
})(window.wp && window.wp.blocks, window.wp && window.wp.element, window.wp && window.wp.i18n);
