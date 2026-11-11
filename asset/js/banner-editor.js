/* global window, document, CKEDITOR, jQuery */
/**
 * Banner settings editor.
 *
 * - Turns the banner textarea into a CKEditor instance with only the four
 *   supported formatting features: bold, italic, underline, and links.
 * - Warns, in the form and on screen-reader-visible status text, when the
 *   chosen text/background colors fall below the WCAG AA contrast ratio.
 *
 * The administrator's color choice is never changed; the warning is
 * informational only.
 */
(function ($) {
    'use strict';

    var AA_NORMAL_TEXT = 4.5;

    function parseHex(color) {
        var value = String(color || '').replace('#', '');
        if (3 === value.length) {
            value = value[0] + value[0] + value[1] + value[1] + value[2] + value[2];
        }
        if (!/^[0-9a-f]{6}$/i.test(value)) {
            return null;
        }
        return [
            parseInt(value.substr(0, 2), 16) / 255,
            parseInt(value.substr(2, 2), 16) / 255,
            parseInt(value.substr(4, 2), 16) / 255
        ];
    }

    function linearize(channel) {
        return channel <= 0.03928
            ? channel / 12.92
            : Math.pow((channel + 0.055) / 1.055, 2.4);
    }

    function luminance(color) {
        var channels = parseHex(color);
        if (!channels) {
            return null;
        }
        return 0.2126 * linearize(channels[0])
            + 0.7152 * linearize(channels[1])
            + 0.0722 * linearize(channels[2]);
    }

    function contrastRatio(colorA, colorB) {
        var luminanceA = luminance(colorA);
        var luminanceB = luminance(colorB);
        if (null === luminanceA || null === luminanceB) {
            return null;
        }
        var lighter = Math.max(luminanceA, luminanceB);
        var darker = Math.min(luminanceA, luminanceB);
        return (lighter + 0.05) / (darker + 0.05);
    }

    function contrastStatusText(ratio) {
        if (null === ratio) {
            return '';
        }
        if (ratio >= AA_NORMAL_TEXT) {
            return 'Contrast ratio: ' + ratio.toFixed(2) + ':1. Meets WCAG AA for normal-size text (4.5:1).';
        }
        return 'Contrast ratio: ' + ratio.toFixed(2) + ':1. Below the WCAG AA minimum of 4.5:1 for normal-size text. '
            + 'The banner will still use these colors, but some visitors may find it hard to read.';
    }

    function setupContrastStatus() {
        var pairs = [
            ['global_banner_bg_color', 'global_banner_text_color'],
            ['site_banner_bg_color', 'site_banner_text_color']
        ];

        pairs.forEach(function (ids) {
            var background = document.getElementById(ids[0]);
            var text = document.getElementById(ids[1]);
            if (!background || !text) {
                return;
            }

            var inputs = text.closest('.inputs') || text.parentNode;
            var status = inputs.querySelector('[data-banner-contrast-status]');
            if (!status) {
                status = document.createElement('div');
                status.className = 'field-description banner-contrast-status';
                status.setAttribute('aria-live', 'polite');
                status.setAttribute('data-banner-contrast-status', '');
                inputs.appendChild(status);
            }

            function update() {
                var ratio = contrastRatio(background.value, text.value);
                var fails = null !== ratio && ratio < AA_NORMAL_TEXT;
                status.textContent = contrastStatusText(ratio);
                status.classList.toggle('banner-contrast-fail', fails);
                status.style.color = fails ? '#b32d2e' : '';
                status.style.fontWeight = fails ? '600' : '';
            }

            background.addEventListener('input', update);
            background.addEventListener('change', update);
            text.addEventListener('input', update);
            text.addEventListener('change', update);
            update();
        });
    }

    // CKEditor loads this event for every configured instance. Keep only the
    // formatting the banner can safely store and display.
    $(document).on('o:ckeditor-config', function (event, config) {
        config.toolbar = [
            ['Bold', 'Italic', 'Underline', '-', 'Link', 'Unlink', '-', 'RemoveFormat']
        ];
        config.allowedContent = 'p br strong b em i u a[!href,target,title]; ul ol li';
        config.enterMode = CKEDITOR.ENTER_P;
        config.shiftEnterMode = CKEDITOR.ENTER_BR;

        var title = $('textarea[data-banner-editor]').first().attr('data-editor-title');
        config.title = title || 'Banner text';
    });

    $(function () {
        $('textarea[data-banner-editor]').each(function () {
            $(this).ckeditor();

            var editor = CKEDITOR.instances[this.id];
            if (!editor) {
                return;
            }

            function labelToolbar() {
                $(editor.container.$).find('.cke_toolbar').attr('aria-label', 'Formatting toolbar');
            }

            if ('ready' === editor.status) {
                labelToolbar();
            } else {
                editor.on('instanceReady', labelToolbar);
            }
        });
        setupContrastStatus();
    });
})(window.jQuery);
