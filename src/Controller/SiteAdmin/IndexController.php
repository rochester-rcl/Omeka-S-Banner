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
        error_log("Banner IndexController - Request method: " . $this->getRequest()->getMethod());
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $siteBannerEnabled = isset($data['site_banner_enabled']) ? (bool)$data['site_banner_enabled'] : false;
            $siteBannerConent = isset($data['site_banner_content']) ? (string)$data['site_banner_content'] : '';
            // Save basic settings
            $siteSettings->set('site_banner_content', $data['site_banner_content'] ?? BANNER_SITE_DEFAULT);
            //Site Banner Enabled setting
            $siteSettings->set('site_banner_enabled', $siteBannerEnabled);
            return $this->redirect()->toRoute('admin/site/slug/banner', [], true);

        }




        //error_log($siteBannerEnabled?"SBE: True":"SBE: False");

        // Prepare settings for the view
        $settings = [
            'site_banner_enabled' => $siteSettings->get('site_banner_enabled'),
            'site_banner_content' => $siteSettings->get('site_banner_content'),
        ];
        return new ViewModel([
            'site' => $site,
            'settings' => $settings,
        ]);

    }
}