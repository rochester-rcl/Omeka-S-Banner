<?php
namespace Banner\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        //$settings = $this->;
        error_log("Banner IndexController - Request method: " . $this->getRequest()->getMethod());
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $globalBannerEnabled = isset($data['global_banner_enabled']) ? (bool)$data['site_banner_enabled'] : false;
            $globalBannerConent = isset($data['global_banner_content']) ? (string)$data['site_banner_content'] : '';
            // Save basic settings
            //Site Banner Enabled settin
            return $this->redirect()->toRoute('admin/banner', [], true);

        }




        //error_log($siteBannerEnabled?"SBE: True":"SBE: False");

        // Prepare settings for the view
        $settings = [
            //'global_banner_enabled' => $data->get('site_banner_enabled'),
            //'global_banner_content' => $data->get('site_banner_content'),
        ];
        return new ViewModel([
            'settings' => $settings,
        ]);

    }
}