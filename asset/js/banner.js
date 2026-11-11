/* global window, document */
/**
 * Renders announcement banners.
 *
 * The banner text is HTML so the settings editor can add bold, italic,
 * underline, and links. It is sanitized on the server by
 * Banner\Stdlib\HtmlSanitizer before it reaches this script; do not call
 * render() with untrusted HTML.
 */
(function () {
    'use strict';

    function createBanner(options) {
        var banner = document.createElement('div');
        banner.id = options.id;
        banner.className = 'omeka-banner ' + options.className;
        banner.setAttribute('role', 'region');
        banner.setAttribute('aria-label', options.label);
        banner.style.width = '100%';
        banner.style.backgroundColor = options.bgColor;
        banner.style.color = options.textColor;
        banner.style.display = 'flex';
        banner.style.alignItems = 'center';
        banner.style.justifyContent = 'center';
        banner.style.padding = '0.75em 1em';
        banner.style.boxSizing = 'border-box';

        var content = document.createElement('div');
        content.className = 'omeka-banner__content';
        content.style.margin = '0';
        content.style.textAlign = 'center';
        content.style.fontSize = '1.1em';
        content.style.lineHeight = '1.5';
        content.style.maxWidth = '100%';
        // Sanitized server-side; see Banner\Stdlib\HtmlSanitizer.
        content.innerHTML = options.html;

        Array.prototype.forEach.call(content.querySelectorAll('a'), function (link) {
            // Keep links readable: they inherit the administrator's text color
            // and are underlined so color is not the only distinguishing cue.
            link.style.color = 'inherit';
            link.style.textDecoration = 'underline';
            if ('_blank' === link.getAttribute('target')) {
                link.setAttribute('rel', 'noopener noreferrer');
            }
        });

        banner.appendChild(content);
        return banner;
    }

    function insertBeforeContent(banner, contentSelector) {
        var content = document.getElementById(contentSelector);
        if (!content || !content.parentNode) {
            return false;
        }
        content.parentNode.insertBefore(banner, content);
        return true;
    }

    function insertAdminBanner(banner) {
        var flex = document.querySelector('div.flex');
        if (flex && flex.parentNode) {
            flex.parentNode.insertBefore(banner, flex);
        } else if (document.body) {
            document.body.insertBefore(banner, document.body.firstChild);
        }
    }

    /**
     * Push the admin layout's fixed elements below the banner(s).
     */
    function adjustAdminOffsets(banners) {
        var offset = 0;
        banners.forEach(function (banner) {
            offset += banner.offsetHeight;
        });
        if (offset <= 0) {
            return;
        }

        var h1 = document.querySelector('div[role="main"] > h1:first-of-type');
        if (h1) {
            h1.style.top = offset + 'px';
        }
        var pageActions = document.getElementById('page-actions');
        if (pageActions) {
            pageActions.style.top = offset + 'px';
        }
        var main = document.querySelector('div[role="main"]');
        if (main) {
            var currentPadding = parseInt(window.getComputedStyle(main).paddingTop, 10) || 0;
            main.style.paddingTop = (currentPadding + offset) + 'px';
        }
        document.querySelectorAll('.sidebar').forEach(function (sidebar) {
            var currentTop = parseInt(window.getComputedStyle(sidebar).top, 10) || 0;
            sidebar.style.top = (currentTop + offset) + 'px';
        });
    }

    /**
     * Insert one or more banners.
     *
     * @param {Object} options
     * @param {string} options.contentSelector Public theme content element id.
     * @param {boolean} options.admin Whether to use the admin placement.
     * @param {Array} options.banners Banner definitions.
     */
    function render(options) {
        var banners = [];
        (options.banners || []).forEach(function (config) {
            var banner = createBanner(config);
            if (options.admin) {
                insertAdminBanner(banner);
            } else {
                insertBeforeContent(banner, options.contentSelector || 'content');
            }
            banners.push(banner);
        });

        if (options.admin) {
            adjustAdminOffsets(banners);
        }
    }

    window.OmekaBanner = {
        createBanner: createBanner,
        render: render
    };
})();
