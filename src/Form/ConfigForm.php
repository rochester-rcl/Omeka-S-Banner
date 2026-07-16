<?php
namespace Banner\Form;

use Laminas\Form\Form;

class ConfigForm extends Form
{
    public function init()
    {
        $this->add([
            'type' => 'checkbox',
            'name' => 'global_banner_enabled',
            'options' => [
                'label' => 'Is the global banner currently visible?',
            ],
            'attributes' => [
                'id' => 'global_banner_enabled',
            ],
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'global_banner_text',
            'options' => [
                'label' => 'Banner text. The global banner appears above all content on public pages and at the top of admin pages.',
            ],
            'attributes' => [
                'id' => 'global_banner_text',
                'rows' => 4,
                'cols' => 64,
            ],
        ]);

        $this->add([
            'type' => 'Laminas\Form\Element\Color',
            'name' => 'global_banner_bg_color',
            'options' => [
                'label' => 'Background color',
            ],
            'attributes' => [
                'id' => 'global_banner_bg_color',
            ],
        ]);

        $this->add([
            'type' => 'Laminas\Form\Element\Color',
            'name' => 'global_banner_text_color',
            'options' => [
                'label' => 'Text color',
            ],
            'attributes' => [
                'id' => 'global_banner_text_color',
            ],
        ]);
    }
}
