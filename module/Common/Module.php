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
 * @description Module.php - Common mondule loader
 */
namespace Common;

use Zend\Mvc\MvcEvent;
use Common\Helper\SConfigLoader;

class Module{

    /**
     * Convenience method to return the config file
     *
     * @return string
     */
    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Return an autoloader configured namespace
     *
     * @return array
     */
    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                ),
            ),
        );
    }

    /**
     * Attaches the ApiErrorListener on the render event
     *
     * @param MvcEvent $e
     */
    public function onBootstrap($e) {
        $phpSettings = SConfigLoader::getConfig('phpsettings');
        if($phpSettings) {
            foreach($phpSettings as $key => $value) {
                ini_set($key, $value);
            }
        }
        
        $app = $e->getTarget();
        $services = $app->getServiceManager();
        $events = $app->getEventManager();
        $events->attach($services->get('Common\Listeners\GlobalErrorListener'));
        //$events->attach($services->get('Common\Listeners\OAuthListener'));
    }
}
