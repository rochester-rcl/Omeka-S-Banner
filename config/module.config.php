<?php
define('BANNER_GLOBAL_DEFAULT_TEXT', "⚠️ Omeka S will be down for maintenance on __/__/____, from __ to __. Thank you for your patience.");
define('BANNER_GLOBAL_DEFAULT_BG_COLOR', '#28a745');
define('BANNER_GLOBAL_DEFAULT_TEXT_COLOR', '#ffffff');

define('BANNER_SITE_DEFAULT_TEXT', "This site recently experienced a redesign as of __/__/____. Please provide any feedback.");
define('BANNER_SITE_DEFAULT_BG_COLOR', '#17a2b8');
define('BANNER_SITE_DEFAULT_TEXT_COLOR', '#ffffff');

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
    'form_elements' => [
        'invokables' => [
            'ConfigForm' => 'Banner\Form\ConfigForm',
        ],
    ],
];
