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
 * @description AbsctractCassandraAdapter - Cassandra wrapper for models
 */

namespace Common\Adapters;

use Common\Helper\SConfigLoader;

abstract class AbsctractCassandraAdapter {

    //Cassa Connection session
    protected static $session;
    protected static $cluster;

    
    public function __construct() {
        self::buildConnection();
    }
    
    /**
     * Try build a connection session to the cluster specified in config
     */
    private static function buildConnection() {
        if (!self::$cluster) {
            $cassaConfig = SConfigLoader::getConfig('cassaconf');
            try {
                self::$cluster = \Cassandra::cluster()
                        ->withContactPoints(implode(',', $cassaConfig['contactPoints']))
                        ->withPort($cassaConfig['contactPort'])
                        ->build();
                self::$session = self::$cluster->connect();
            } catch (\Exception $e) {
                //Exception will catched generally
                //Reason: the config is not loaded or can't connect to cluster 
            }
        }
    }
    

}
