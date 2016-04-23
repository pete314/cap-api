<?php

/** 
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
 * @description Crawl - Crawl module config
 */
return array(
    'router' => array(
        'routes' => array(
            'crawl-job' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/api/crawl/job[/:actiion[/:id[/:jobid]]]',//action is default mapping to an *Action(), don't want that
                    'constraints' => [
                        'actiion' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'id'      => '[a-f0-9]{32}',
                        'jobid'      => '[a-f0-9]+',
                    ],
                    'defaults' => array(
                        'controller' => 'Crawl\Controller\CrawlJob'
                    ),
                ),
            ),
            'crawl-job-result' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/api/crawl/result[/:actiion[/:id[/:jobid]]]',//action is default mapping to an *Action(), don't want that
                    'constraints' => [
                        'actiion' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'id'      => '[a-f0-9]{32}',
                        'jobid'      => '[a-f0-9]+',
                    ],
                    'defaults' => array(
                        'controller' => 'Crawl\Controller\CrawlResult'
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Crawl\Controller\CrawlJob' => 'Crawl\Controller\CrawlJobController',
            'Crawl\Controller\CrawlResult' => 'Crawl\Controller\CrawlResultController',
        )
    )
);
