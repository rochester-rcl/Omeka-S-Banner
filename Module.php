<?php
namespace Banner;

use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Module\AbstractModule;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null, ['Banner\Controller\SiteAdmin\Index']);

        // Inject admin global banner via MVC render event (covers all admin pages)
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER, [$this, 'injectAdminBannerScript'], -100);
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');

        $formElementManager = $services->get('FormElementManager');
        $form = $formElementManager->get(Form\ConfigForm::class);
        $form->setData([
            'global_banner_enabled'    => (bool) $settings->get('global_banner_enabled', false),
            'global_banner_text'       => $settings->get('global_banner_text', BANNER_GLOBAL_DEFAULT_TEXT),
            'global_banner_bg_color'   => $settings->get('global_banner_bg_color', BANNER_GLOBAL_DEFAULT_BG_COLOR),
            'global_banner_text_color' => $settings->get('global_banner_text_color', BANNER_GLOBAL_DEFAULT_TEXT_COLOR),
        ]);

        return $renderer->formCollection($form);
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $params = $controller->params()->fromPost();
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');

        $settings->set('global_banner_enabled', (bool) ($params['global_banner_enabled'] ?? false));
        $settings->set('global_banner_text', $params['global_banner_text'] ?? '');
        $settings->set('global_banner_bg_color', $params['global_banner_bg_color'] ?? BANNER_GLOBAL_DEFAULT_BG_COLOR);
        $settings->set('global_banner_text_color', $params['global_banner_text_color'] ?? BANNER_GLOBAL_DEFAULT_TEXT_COLOR);
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $siteControllers = [
            'Omeka\Controller\Site\Index',
            'Omeka\Controller\Site\Item',
            'Omeka\Controller\Site\ItemSet',
            'Omeka\Controller\Site\Media',
            'Omeka\Controller\Site\Page',
            'Omeka\Controller\Site\CrossSiteSearch',
        ];
        foreach ($siteControllers as $controller) {
            $sharedEventManager->attach($controller, 'view.layout', [$this, 'injectPublicBannerScript']);
        }
    }

    public function injectAdminBannerScript(MvcEvent $mvcEvent)
    {
        $routeMatch = $mvcEvent->getRouteMatch();
        if (!$routeMatch) {
            return;
        }

        // Only inject on admin pages — all admin namespaces contain 'Admin'
        $namespace = $routeMatch->getParam('__NAMESPACE__', '');
        if (strpos($namespace, 'Admin') === false) {
            return;
        }

        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');
        $enabled  = $settings->get('global_banner_enabled');
        $text     = (string) $settings->get('global_banner_text', '');

        if (!$enabled || $text === '') {
            return;
        }

        $bgColor   = (string) $settings->get('global_banner_bg_color', BANNER_GLOBAL_DEFAULT_BG_COLOR);
        $textColor = (string) $settings->get('global_banner_text_color', BANNER_GLOBAL_DEFAULT_TEXT_COLOR);

        $jsText      = json_encode($text);
        $jsBgColor   = json_encode($bgColor);
        $jsTextColor = json_encode($textColor);

        $script = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    var banner = document.createElement('div');
    banner.id = 'global-banner';
    banner.className = 'omeka-banner global-banner';
    banner.style.width = '100%';
    banner.style.backgroundColor = {$jsBgColor};
    banner.style.color = {$jsTextColor};
    banner.style.display = 'flex';
    banner.style.alignItems = 'center';
    banner.style.justifyContent = 'center';
    banner.style.padding = '0.75em 1em';
    banner.style.boxSizing = 'border-box';

    var p = document.createElement('p');
    p.style.margin = '0';
    p.style.textAlign = 'center';
    p.style.fontWeight = 'bold';
    p.style.fontSize = '1.1em';
    p.style.whiteSpace = 'pre-wrap';
    p.textContent = {$jsText};
    banner.appendChild(p);

    var flex = document.querySelector('div.flex');
    if (flex) {
        flex.parentNode.insertBefore(banner, flex);
    } else {
        document.body.insertBefore(banner, document.body.firstChild);
    }

    // Fixed elements (page title, action buttons, sidebar) are anchored to top: 0.
    // Push them down by the banner height so they don't overlap the banner.
    var offset = banner.offsetHeight;
    if (offset > 0) {
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
        document.querySelectorAll('.sidebar').forEach(function(sidebar) {
            var currentTop = parseInt(window.getComputedStyle(sidebar).top, 10) || 0;
            sidebar.style.top = (currentTop + offset) + 'px';
        });
    }
});
JS;

        $viewHelpers = $services->get('ViewHelperManager');
        $viewHelpers->get('headScript')->appendScript($script);
    }

    /**
     * Returns the HTML id of the main content element for a given theme.
     *
     * Most themes use id="content"; the freedom theme uses id="main-content".
     * Each installed theme has an explicit case so the mapping is auditable.
     */
    public function getContentSelector(string $themeId): string
    {
        switch ($themeId) {
            case 'bookshelf':
                return 'content';
            case 'centerrow':
                return 'content';
            case 'foundation':
                return 'content';
            case 'cozy':
                return 'content';
            case 'default':
                return 'content';
            case 'freedom':
                return 'main-content';
            case 'freedom-v1.1.0':
                return 'content';
            case 'lively':
                return 'content';
            case 'papers':
                return 'content';
            case 'thanksroy':
                return 'content';
            case 'thedaily':
                return 'content';
            default:
                return 'content';
        }
    }

    public function injectPublicBannerScript(Event $event)
    {
        $renderer = $event->getTarget();
        $services = $this->getServiceLocator();

        $themeId = '';
        try {
            $themeManager = $services->get('Omeka\Site\ThemeManager');
            $themeId = $themeManager->getCurrentTheme()->getId();
        } catch (\Exception $e) {
            // Falls back to 'content' via getContentSelector default case
        }
        $contentSelector = $this->getContentSelector($themeId);
        $settings = $services->get('Omeka\Settings');

        $globalEnabled   = (bool) $settings->get('global_banner_enabled');
        $globalText      = (string) $settings->get('global_banner_text', '');
        $globalBgColor   = (string) $settings->get('global_banner_bg_color', BANNER_GLOBAL_DEFAULT_BG_COLOR);
        $globalTextColor = (string) $settings->get('global_banner_text_color', BANNER_GLOBAL_DEFAULT_TEXT_COLOR);

        try {
            $siteEnabled   = (bool) $renderer->siteSetting('site_banner_enabled');
            $siteText      = (string) $renderer->siteSetting('site_banner_text', '');
            $siteBgColor   = (string) $renderer->siteSetting('site_banner_bg_color', BANNER_SITE_DEFAULT_BG_COLOR);
            $siteTextColor = (string) $renderer->siteSetting('site_banner_text_color', BANNER_SITE_DEFAULT_TEXT_COLOR);
        } catch (\Exception $e) {
            $siteEnabled   = false;
            $siteText      = '';
            $siteBgColor   = BANNER_SITE_DEFAULT_BG_COLOR;
            $siteTextColor = BANNER_SITE_DEFAULT_TEXT_COLOR;
        }

        $hasGlobal = $globalEnabled && $globalText !== '';
        $hasSite   = $siteEnabled && $siteText !== '';

        if (!$hasGlobal && !$hasSite) {
            return;
        }

        $jsContentSelector = json_encode($contentSelector);
        $script = "document.addEventListener('DOMContentLoaded', function() {\n";
        $script .= "    var content = document.getElementById({$jsContentSelector});\n";
        $script .= "    if (!content) return;\n";

        if ($hasGlobal) {
            $jsText      = json_encode($globalText);
            $jsBgColor   = json_encode($globalBgColor);
            $jsTextColor = json_encode($globalTextColor);
            $script .= "    var globalBanner = document.createElement('div');\n";
            $script .= "    globalBanner.id = 'global-banner';\n";
            $script .= "    globalBanner.className = 'omeka-banner global-banner';\n";
            $script .= "    globalBanner.style.width = '100%';\n";
            $script .= "    globalBanner.style.backgroundColor = {$jsBgColor};\n";
            $script .= "    globalBanner.style.color = {$jsTextColor};\n";
            $script .= "    globalBanner.style.display = 'flex';\n";
            $script .= "    globalBanner.style.alignItems = 'center';\n";
            $script .= "    globalBanner.style.justifyContent = 'center';\n";
            $script .= "    globalBanner.style.padding = '0.75em 1em';\n";
            $script .= "    globalBanner.style.boxSizing = 'border-box';\n";
            $script .= "    var globalP = document.createElement('p');\n";
            $script .= "    globalP.style.margin = '0';\n";
            $script .= "    globalP.style.textAlign = 'center';\n";
            $script .= "    globalP.style.fontWeight = 'bold';\n";
            $script .= "    globalP.style.fontSize = '1.1em';\n";
            $script .= "    globalP.style.whiteSpace = 'pre-wrap';\n";
            $script .= "    globalP.textContent = {$jsText};\n";
            $script .= "    globalBanner.appendChild(globalP);\n";
            $script .= "    content.parentNode.insertBefore(globalBanner, content);\n";
        }

        if ($hasSite) {
            $jsText      = json_encode($siteText);
            $jsBgColor   = json_encode($siteBgColor);
            $jsTextColor = json_encode($siteTextColor);
            $script .= "    var siteBanner = document.createElement('div');\n";
            $script .= "    siteBanner.id = 'site-banner';\n";
            $script .= "    siteBanner.className = 'omeka-banner site-banner';\n";
            $script .= "    siteBanner.style.width = '100%';\n";
            $script .= "    siteBanner.style.backgroundColor = {$jsBgColor};\n";
            $script .= "    siteBanner.style.color = {$jsTextColor};\n";
            $script .= "    siteBanner.style.display = 'flex';\n";
            $script .= "    siteBanner.style.alignItems = 'center';\n";
            $script .= "    siteBanner.style.justifyContent = 'center';\n";
            $script .= "    siteBanner.style.padding = '0.75em 1em';\n";
            $script .= "    siteBanner.style.boxSizing = 'border-box';\n";
            $script .= "    var siteP = document.createElement('p');\n";
            $script .= "    siteP.style.margin = '0';\n";
            $script .= "    siteP.style.textAlign = 'center';\n";
            $script .= "    siteP.style.fontWeight = 'bold';\n";
            $script .= "    siteP.style.fontSize = '1.1em';\n";
            $script .= "    siteP.style.whiteSpace = 'pre-wrap';\n";
            $script .= "    siteP.textContent = {$jsText};\n";
            $script .= "    siteBanner.appendChild(siteP);\n";
            $script .= "    content.parentNode.insertBefore(siteBanner, content);\n";
        }

        $script .= "});\n";

        $renderer->headScript()->appendScript($script);
    }
}
