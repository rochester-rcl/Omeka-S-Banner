<?php
namespace Banner;

use Laminas\Form\Element;
use Laminas\Form\Form;
use Omeka\Form\Element as OmekaElement;

class SiteSettingsForm extends Form
{
    public function init()
    {
        $this
            ->add([
                'name' => 'show_more_mode',
                'type' => Element\Radio::class,
                'options' => [
                    'label' => 'Truncation Mode',
                    'value_options' => [
                        'words' => 'Words',
                        'characters' => 'Characters',
                    ],
                ],
            ])
            ->add([
                'name' => 'show_more_limit',
                'type' => Element\Number::class,
                'options' => [
                    'label' => 'Limit',
                ],
                'attributes' => [
                    'min' => 0,
                    'max' => 10000,
                ],
            ])
            ->add([
                'name' => 'show_more_excluded_properties',
                'type' => OmekaElement\PropertySelect::class,
                'options' => [
                    'label' => 'Excluded Properties',
                    'empty_option' => '',
                ],
                'attributes' => [
                    'multiple' => true,
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select properties to exclude…',
                ],
            ])
            ->add([
                'name' => 'site_banner_enabled',
                'type' => Element\Checkbox::class,
                'options' => [
                    'label' => 'Enabled?',
                    'info' => 'Is the banner currently visible?',
                ],
                'attributes' => [
                    'value' => '1',
                ],
            ]);
    }
}