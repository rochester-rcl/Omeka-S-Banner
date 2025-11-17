<?php
define('BANNER_GLOBAL_DEFAULT', '<div class="maintenance-banner">
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

define('BANNER_SITE_DEFAULT', '<div class="maintenance-banner">
This site recently experienced a redesign as of __/__/____. Please provide any feedback on this redesign by clicking <a href="#">here.</a>
</div><style>
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
  }</style>
')


;
return [
    'controllers' => [
        'invokables' => [
            'Banner\Controller\SiteAdmin\Index' => 'Banner\Controller\SiteAdmin\IndexController',
            'Banner\Controller\Admin\Index' => 'Banner\Controller\Admin\IndexController'
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
        'AdminModule' =>[
            [
                'label' => 'Banner',
                'route' => 'admin/banner',
                'controller' => 'index',
                'action' => 'index',
                'useRouteMatch' => true,
                'resource' => 'Banner\Controller\Admin\Index'
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
                    'banner' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/banner',
                            'defaults' => [
                                '__NAMESPACE__' => 'Banner\Controller\Admin',
                                'controller' => 'Index',
                                'action' => 'index',
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

