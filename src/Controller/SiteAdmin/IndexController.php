<?php
namespace Banner\Controller\SiteAdmin;

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
            $siteSettings->set('site_banner_enabled', isset($data['site_banner_enabled']));
            $siteSettings->set('site_banner_text', $data['site_banner_text'] ?? '');
            $siteSettings->set('site_banner_bg_color', $data['site_banner_bg_color'] ?? BANNER_SITE_DEFAULT_BG_COLOR);
            $siteSettings->set('site_banner_text_color', $data['site_banner_text_color'] ?? BANNER_SITE_DEFAULT_TEXT_COLOR);
            return $this->redirect()->toRoute('admin/site/slug/banner', [], true);
        }

        return new ViewModel([
            'site' => $site,
            'settings' => [
                'site_banner_enabled'    => $siteSettings->get('site_banner_enabled', false),
                'site_banner_text'       => $siteSettings->get('site_banner_text', BANNER_SITE_DEFAULT_TEXT),
                'site_banner_bg_color'   => $siteSettings->get('site_banner_bg_color', BANNER_SITE_DEFAULT_BG_COLOR),
                'site_banner_text_color' => $siteSettings->get('site_banner_text_color', BANNER_SITE_DEFAULT_TEXT_COLOR),
            ],
        ]);
    }
}
