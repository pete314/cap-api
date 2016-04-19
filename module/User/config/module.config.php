<?php

/*  * 
 * ===============================================================
 * Copyright (C) 2016 - Peter Nagy.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * ===============================================================
 * @author      Peter Nagy
 * @since       Jan 2016
 * @version     0.1
 * @description User module config
 */
return array(
    'router' => array(
        'routes' => array(
            'login' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/api/user[/:actiion[/:id]]',//action is default mapping to an *Action(), don't want that
                    'constraints' => [
                        'actiion' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'id'      => '[a-f0-9]{32}'
                    ],
                    'defaults' => array(
                        'controller' => 'User\Controller\User'
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'User\Controller\User' => 'User\Controller\UserController',
        )
    )
);

