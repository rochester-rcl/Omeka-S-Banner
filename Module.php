<?php
namespace Banner;
use Laminas\Mvc\Controller\AbstractController;
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
        $form = $formElementManager->get(Form\ConfigForm::class, []);
        return $renderer->form($form);
    }
    public function handleConfigForm(AbstractController $controller)
    {
        parent::handleConfigForm($controller);
        //Grab properties and store them
        $controller->getResponse();
        echo "foo";
    }
}
