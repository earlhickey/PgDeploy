<?php
/**
 * PgDeploy Configuration
 */
return array(
    'controllers' => array(
        'invokables' => array(
            'PgDeploy\Controller\Index' => 'PgDeploy\Controller\IndexController'
        ),
    ),
    'router' => array(
        'routes' => array(
            'deploy' => array(
                'type'    => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/deploy[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'PgDeploy\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'PgDeploy' => __DIR__ . '/../view',
        ),
    ),
);