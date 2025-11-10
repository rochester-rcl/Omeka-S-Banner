<?php
define('BANNER_GLOBAL_DEFAULT','<div class="maintenance-banner">
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
</style>');

define('BANNER_SITE_DEFAULT','<div class="maintenance-banner">
This site recently experienced a redesign as of __/__/____. Please provide any feedback on this redesign by clicking <a href="#">here.</a>
</div>
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
'


);
return [
    'controllers' => [
        'invokables' => [
            'Banner\Controller\SiteAdmin\Index' => 'Banner\Controller\SiteAdmin\IndexController',
        ],
    ],
    'navigation' => [
        'site' => [
            [
                'label' => 'Banner',
                'route' => 'admin/site/slug/banner',
                'controller' => 'index',
                'action' => 'index',
                'useRouteMatch' => true,
                'resource' => 'Banner\Controller\SiteAdmin\Index',
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'site' => [
                        'child_routes' => [
                            'slug' => [
                                'child_routes' => [
                                    'banner' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/banner',
                                            'defaults' => [
                                                '__NAMESPACE__' => 'Banner\Controller\SiteAdmin',
                                                'controller' => 'Index',
                                                'action' => 'index',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'banner' => 'banner\View\Helper\banner',
        ],
    ],
];



/*
<?php declare(strict_types=1);

namespace Banner;

return [
    'form_elements' => [
        'invokables' => [
            Form\SettingsFieldset::class => Form\SettingsFieldset::class,
            Form\SiteSettingsFieldset::class => Form\SiteSettingsFieldset::class,
        ],
    ],
    'banner' => [
        'settings' => [
            'global_banner' => '<div class="maintenance-banner">
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
</style>',
            'global_banner_enabled' => 'False'
        ],
        'site_settings' => [
            'site_banner' => '',
            'site_banner_enabled' => 'False'
        ],

    ],
];*/