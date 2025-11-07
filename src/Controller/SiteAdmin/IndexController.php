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
        $siteBannerEnabled = $siteSettings->get('site_banner_enabled', false); // Default to disabled

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();

            // Save basic settings
            $siteSettings->set('site_banner', $data['site_banner'] ?? BANNER_GLOBAL_DEFAULT);
            //Site Banner Enabled setting
            $siteBannerEnabled = isset($data['site_banner_enabled']) && $data['site_banner_enabled'];
            $siteSettings->set('site_banner_enabled', $siteBannerEnabled);

        }



        // Prepare settings for the view
        $settings = [
            'site_banner' => $siteSettings->get('site_banner', BANNER_GLOBAL_DEFAULT),
            'site_banner_enabled' => $siteBannerEnabled,
        ];
        return new ViewModel([
            'site' => $site,
            'settings' => $settings,
        ]);

    }
}