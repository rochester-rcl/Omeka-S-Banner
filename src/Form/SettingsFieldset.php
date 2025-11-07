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
                    'value'=> '<div class="maintenance-banner">
  <p>⚠️ Omeka S will be down for maintenance on __/__/____, from __ to __. Thank you for your patience.</p>
</div>

<style>
  .maintenance-banner {
    width: 100%;
    height: 10vh;
    background-color: #28a745;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 9999;
    margin: 0;
    padding: 0;
  }
  
  .maintenance-banner p {
    color: white;
    font-weight: bold;
    font-size: 1.2em;
    margin: 0;
    padding: 0 20px;
    text-align: center;
  }
</style>'


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