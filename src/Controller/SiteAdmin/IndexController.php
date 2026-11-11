<?php
namespace Banner\Controller\SiteAdmin;

use Banner\Stdlib\ColorContrast;
use Banner\Stdlib\HtmlSanitizer;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $site = $this->currentSite();
        $siteSettings = $this->siteSettings();

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $sanitizer = new HtmlSanitizer();

            $bgColor = $this->validColor(
                $data['site_banner_bg_color'] ?? null,
                BANNER_SITE_DEFAULT_BG_COLOR
            );
            $textColor = $this->validColor(
                $data['site_banner_text_color'] ?? null,
                BANNER_SITE_DEFAULT_TEXT_COLOR
            );

            $siteSettings->set('site_banner_enabled', isset($data['site_banner_enabled']));
            $siteSettings->set(
                'site_banner_text',
                $sanitizer->sanitize((string) ($data['site_banner_text'] ?? ''))
            );
            $siteSettings->set('site_banner_bg_color', $bgColor);
            $siteSettings->set('site_banner_text_color', $textColor);

            $message = ColorContrast::warningMessage($bgColor, $textColor);
            if (null !== $message) {
                $this->messenger()->addWarning($message);
            }

            return $this->redirect()->toRoute('admin/site/slug/banner', [], true);
        }

        $bgColor = $this->validColor(
            $siteSettings->get('site_banner_bg_color'),
            BANNER_SITE_DEFAULT_BG_COLOR
        );
        $textColor = $this->validColor(
            $siteSettings->get('site_banner_text_color'),
            BANNER_SITE_DEFAULT_TEXT_COLOR
        );

        return new ViewModel([
            'site' => $site,
            'settings' => [
                'site_banner_enabled'    => $siteSettings->get('site_banner_enabled', false),
                'site_banner_text'       => $siteSettings->get('site_banner_text', BANNER_SITE_DEFAULT_TEXT),
                'site_banner_bg_color'   => $bgColor,
                'site_banner_text_color' => $textColor,
            ],
            'contrast_ratio' => ColorContrast::ratio($bgColor, $textColor),
        ]);
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
}
