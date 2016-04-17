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
 * @description SConfigLoader - Convinent static configurtion loader
 */

namespace Common\Helper;

use Zend\Config\Config;

class SConfigLoader{
    protected static $config;
    protected static $configFiles = [
        'phpsettings' => 'config/autoload/storage.client.local.php'
    ];
    protected static $configurations = [];
    
    private static function initConfig(){
        if(!self::$config){
            foreach(self::$configFiles as $configName => $filePath){
                self::$config = new Config(include $filePath);
                self::$configurations[$configName] = self::$config->$configName; 
            }
        }
    }
    
    public static function getConfig($configName){
        self::initConfig();
        if(array_key_exists($configName, self::$configurations)){
            return self::$configurations[$configName];
        }else{
            throw new Exception('Config is not loaded, or does not exist', '404', "SConfigLoader::getConfig($configName)");
        }
    }
} 