<?php
namespace Banner;
use ConfigForm;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Module\AbstractModule;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    public function getConfigForm(PhpRenderer $renderer){
        $formElementManager = $this->getServiceLocator()->get('FormElementManager');
        #$form = $formElementManager->get(ConfigForm::class, []);
        #$renderer->formCollection($form,false);
        $html = '<div id="banner-global-config-form">';
        $html .= $renderer->partial('banner/admin/index');
        $html .= '</div>';
        return $html;
    }
}
