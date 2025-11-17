<?php
namespace Banner;
use Banner\Form\ConfigForm;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Module\AbstractModule;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    public function getConfigForm(PhpRenderer $renderer){
        return include __DIR__ . '/src/Form/ConfigForm.php';
    }
}
/*
namespace Banner;
define ('NAMESPACE', 'Banner');
use Common\TraitModule;
use Laminas\EventManager\SharedEventManagerInterface;
use Omeka\Module\AbstractModule;
use Omeka\Module\Exception\ModuleCannotInstallException;
use Omeka\Stdlib\Message;

if (!class_exists(TraitModule::class)) {
    require_once dirname(__DIR__) . '/Common/TraitModule.php';
}

class Module extends AbstractModule
{
    use TraitModule;


    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    protected function preInstall(): void
    {
        if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.65')) {
            $message = new Message('The module Common is a prerequisite for this module. Version 3.4.73 is required.');
            throw new ModuleCannotInstallException((string)$message);
        }
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            SettingForm::class,
            'form.add_elements',
            [$this, 'handleMainSettings']
        );
    }
}*/