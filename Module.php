<?php
namespace Banner;

use Banner\Stdlib\ColorContrast;
use Banner\Stdlib\HtmlSanitizer;
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

        $bgColor = $this->validColor(
            $settings->get('global_banner_bg_color'),
            BANNER_GLOBAL_DEFAULT_BG_COLOR
        );
        $textColor = $this->validColor(
            $settings->get('global_banner_text_color'),
            BANNER_GLOBAL_DEFAULT_TEXT_COLOR
        );

        $form->setData([
            'global_banner_enabled'    => (bool) $settings->get('global_banner_enabled', false),
            'global_banner_text'       => $settings->get('global_banner_text', BANNER_GLOBAL_DEFAULT_TEXT),
            'global_banner_bg_color'   => $bgColor,
            'global_banner_text_color' => $textColor,
        ]);

        $form->get('global_banner_text_color')->setOption(
            'info',
            sprintf(
                'Current contrast ratio: %.2f:1. WCAG AA requires at least 4.5:1 between text and background for normal-size text. The banner keeps the colors you choose and warns when the pair falls below that ratio.',
                ColorContrast::ratio($bgColor, $textColor)
            )
        );

        // Load the rich-text editor (CKEditor) and the banner-specific
        // configuration/contrast warnings for the global settings form.
        $renderer->ckEditor();
        $renderer->headScript()->appendFile(
            self::assetUrlFor($renderer, 'js/banner-editor.js')
        );

        return $renderer->formCollection($form);
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $params = $controller->params()->fromPost();
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');

        $sanitizer = new HtmlSanitizer();

        $settings->set(
            'global_banner_enabled',
            (bool) ($params['global_banner_enabled'] ?? false)
        );
        $settings->set(
            'global_banner_text',
            $sanitizer->sanitize((string) ($params['global_banner_text'] ?? ''))
        );
        $settings->set(
            'global_banner_bg_color',
            $this->validColor(
                $params['global_banner_bg_color'] ?? null,
                BANNER_GLOBAL_DEFAULT_BG_COLOR
            )
        );
        $settings->set(
            'global_banner_text_color',
            $this->validColor(
                $params['global_banner_text_color'] ?? null,
                BANNER_GLOBAL_DEFAULT_TEXT_COLOR
            )
        );

        $this->warnOnLowContrast(
            $controller,
            $settings->get('global_banner_bg_color'),
            $settings->get('global_banner_text_color')
        );
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

        if (!$enabled || !$this->hasVisibleText($text)) {
            return;
        }

        $renderer = $services->get('ViewRenderer');
        $html = (new HtmlSanitizer())->toDisplayHtml($text);

        $banners = [[
            'id' => 'global-banner',
            'className' => 'global-banner',
            'label' => $renderer->translate('Global announcement'),
            'html' => $html,
            'bgColor' => $this->validColor(
                $settings->get('global_banner_bg_color'),
                BANNER_GLOBAL_DEFAULT_BG_COLOR
            ),
            'textColor' => $this->validColor(
                $settings->get('global_banner_text_color'),
                BANNER_GLOBAL_DEFAULT_TEXT_COLOR
            ),
        ]];

        $this->loadBannerAssets($renderer);
        $this->appendRenderScript($renderer, [
            'contentSelector' => 'content',
            'admin' => true,
            'banners' => $banners,
        ]);
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

        $globalText = (string) $settings->get('global_banner_text', '');
        $hasGlobal = (bool) $settings->get('global_banner_enabled')
            && $this->hasVisibleText($globalText);

        try {
            $siteText = (string) $renderer->siteSetting('site_banner_text', '');
            $hasSite = (bool) $renderer->siteSetting('site_banner_enabled')
                && $this->hasVisibleText($siteText);
            $siteBgColor = $this->validColor(
                $renderer->siteSetting('site_banner_bg_color'),
                BANNER_SITE_DEFAULT_BG_COLOR
            );
            $siteTextColor = $this->validColor(
                $renderer->siteSetting('site_banner_text_color'),
                BANNER_SITE_DEFAULT_TEXT_COLOR
            );
        } catch (\Exception $e) {
            $hasSite = false;
            $siteText = '';
            $siteBgColor = BANNER_SITE_DEFAULT_BG_COLOR;
            $siteTextColor = BANNER_SITE_DEFAULT_TEXT_COLOR;
        }

        if (!$hasGlobal && !$hasSite) {
            return;
        }

        $sanitizer = new HtmlSanitizer();
        $banners = [];

        if ($hasGlobal) {
            $banners[] = [
                'id' => 'global-banner',
                'className' => 'global-banner',
                'label' => $renderer->translate('Global announcement'),
                'html' => $sanitizer->toDisplayHtml($globalText),
                'bgColor' => $this->validColor(
                    $settings->get('global_banner_bg_color'),
                    BANNER_GLOBAL_DEFAULT_BG_COLOR
                ),
                'textColor' => $this->validColor(
                    $settings->get('global_banner_text_color'),
                    BANNER_GLOBAL_DEFAULT_TEXT_COLOR
                ),
            ];
        }

        if ($hasSite) {
            $banners[] = [
                'id' => 'site-banner',
                'className' => 'site-banner',
                'label' => $renderer->translate('Site announcement'),
                'html' => $sanitizer->toDisplayHtml($siteText),
                'bgColor' => $siteBgColor,
                'textColor' => $siteTextColor,
            ];
        }

        $this->loadBannerAssets($renderer);
        $this->appendRenderScript($renderer, [
            'contentSelector' => $contentSelector,
            'admin' => false,
            'banners' => $banners,
        ]);
    }

    /**
     * Add the banner script and stylesheet to a page.
     */
    private function loadBannerAssets(PhpRenderer $renderer): void
    {
        $renderer->headLink()->appendStylesheet(
            self::assetUrlFor($renderer, 'css/banner.css')
        );
        $renderer->headScript()->appendFile(
            self::assetUrlFor($renderer, 'js/banner.js')
        );
    }

    /**
     * URL for a module asset, versioned by file mtime so browsers pick up
     * module updates even though the module version may not change.
     */
    public static function assetUrlFor(PhpRenderer $renderer, string $file): string
    {
        $url = (string) $renderer->assetUrl($file, 'Banner', false, false);
        $path = __DIR__ . '/asset/' . $file;
        $version = is_file($path) ? filemtime($path) : false;

        return $version ? $url . '?v=' . $version : $url;
    }

    /**
     * Append the inline script that hands the banner configuration to the
     * shared renderer. JSON_HEX_TAG prevents banner text from closing the
     * inline script element.
     */
    private function appendRenderScript(PhpRenderer $renderer, array $options): void
    {
        $json = json_encode(
            $options,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                | JSON_INVALID_UTF8_SUBSTITUTE
        );

        $renderer->headScript()->appendScript(
            'document.addEventListener("DOMContentLoaded", function () {'
            . ' OmekaBanner.render(' . $json . ');'
            . ' });'
        );
    }

    /**
     * Whether sanitized/stored banner text has anything visible in it.
     */
    private function hasVisibleText(string $text): bool
    {
        $text = html_entity_decode(
            strip_tags($text),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
        // Treat non-breaking spaces left by the editor as empty too.
        return '' !== trim($text, " \t\n\r\0\x0B\xC2\xA0");
    }

    /**
     * Validate a posted/stored color, falling back to a known default.
     */
    private function validColor($color, string $fallback): string
    {
        if (is_string($color)) {
            $color = trim($color);
            if (ColorContrast::isValidColor($color)) {
                return $color;
            }
        }
        return $fallback;
    }

    /**
     * Emit a warning when the administrator's color choice is below WCAG AA.
     *
     * The colors are deliberately kept: the module warns but does not override
     * the administrator's decision.
     */
    private function warnOnLowContrast(
        AbstractController $controller,
        string $bgColor,
        string $textColor
    ): void {
        $message = ColorContrast::warningMessage($bgColor, $textColor);
        if (null !== $message) {
            $controller->messenger()->addWarning($message);
        }
    }
}
