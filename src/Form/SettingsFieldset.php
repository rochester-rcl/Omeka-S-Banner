<?php declare(strict_types=1);

namespace Banner;


use Laminas\Form\Element;
use Laminas\Form\Fieldset;

class SettingsFieldset extends Fieldset
{
    protected $label = 'Banner';
    protected $elementGroups = ['banner' => 'Banner'];

    public function init(): void
    {
        $this->setAttribute('id', 'banner')->setOption('element_groups', $this->elementGroups)
            ->add([
                'name' => 'global_banner',
                'type' => Element\Textarea::class,
                'options' =>
                    ['element_group' => 'banner',
                        'label' => 'Global Banner content (HTML)',
                        'info' => 'This content will appear at the top of the body tag on all pages, and above site level banners.'
                    ],
                'attributes'=>[ 'id' =>  'global_banner',
                'rows' => 3,
                    'value'=> BANNER_GLOBAL_DEFAULT,


                ]

        ])->add([
            'name' => 'global_banner_enabled',
            'type' => Element\Checkbox::class,
                'options' => [
                    'element_group'=>'banner',
                    'label'=> 'Enabled',
                    'info' => 'Is the banner currently visible?']]);



    }
}