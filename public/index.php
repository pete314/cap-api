<?php

/** 
 * ===============================================================
 * Copyright (C) 2016 Peter Nagy.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * ===============================================================
 * @author     Peter Nagy
 * @since      Jan 2016
 * @version    0.1
 * @Description CLASS NAME - Short description
 */

chdir(dirname(__DIR__));
//Setup error handler
require_once 'error_handler.php';
// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}
// Setup autoloading
include 'init_autoloader.php';
// Run the application!
Zend\Mvc\Application::init(include 'config/application.config.php')->run();

