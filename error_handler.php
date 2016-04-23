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
 * @description error handler - Catch and handle all errors not cought by zend
 */

define('E_FATAL',  E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR |
        E_COMPILE_ERROR | E_RECOVERABLE_ERROR);
 
define('DISPLAY_ERRORS', TRUE);
define('ERROR_REPORTING', E_ALL | E_STRICT);
 
register_shutdown_function('shut');
set_error_handler('handler');
 
//catch function
function shut()
{
    $error = error_get_last();
    if ($error && ($error['type'] & E_FATAL)) {
        handler($error['type'], $error['message'], $error['file'], $error['line']);
    }
}
 
function handler($errno, $errstr, $errfile, $errline)
{
    switch ($errno) {
 
        case E_ERROR: // 1 //
            $typestr = 'E_ERROR'; break;
        case E_WARNING: // 2 //
            $typestr = 'E_WARNING'; break;
        case E_PARSE: // 4 //
            $typestr = 'E_PARSE'; break;
        case E_NOTICE: // 8 //
            $typestr = 'E_NOTICE'; break;
        case E_CORE_ERROR: // 16 //
            $typestr = 'E_CORE_ERROR'; break;
        case E_CORE_WARNING: // 32 //
            $typestr = 'E_CORE_WARNING'; break;
        case E_COMPILE_ERROR: // 64 //
            $typestr = 'E_COMPILE_ERROR'; break;
        case E_CORE_WARNING: // 128 //
            $typestr = 'E_COMPILE_WARNING'; break;
        case E_USER_ERROR: // 256 //
            $typestr = 'E_USER_ERROR'; break;
        case E_USER_WARNING: // 512 //
            $typestr = 'E_USER_WARNING'; break;
        case E_USER_NOTICE: // 1024 //
            $typestr = 'E_USER_NOTICE'; break;
        case E_STRICT: // 2048 //
            $typestr = 'E_STRICT'; break;
        case E_RECOVERABLE_ERROR: // 4096 //
            $typestr = 'E_RECOVERABLE_ERROR'; break;
        case E_DEPRECATED: // 8192 //
            $typestr = 'E_DEPRECATED'; break;
        case E_USER_DEPRECATED: // 16384 //
            $typestr = 'E_USER_DEPRECATED'; break;
    }
     
    $message = " Error PHP in file : ".$errfile." at line : ".$errline."
    with type error : ".$typestr." : ".$errstr." in ". php_sapi_name() == 'cli' ? 'cli' : $_SERVER['REQUEST_URI'];
    
    if(!($errno & ERROR_REPORTING)) {
        return;
    }
 
    if (DISPLAY_ERRORS) {
        
        if($typestr == 'E_ERROR' || $typestr == 'E_PARSE' || $typestr == 'E_CORE_ERROR' || $typestr == 'E_COMPILE_ERROR'){
            //logging...
            $logger = new \Zend\Log\Logger;         
            $writerStream = new \Zend\Log\Writer\Stream(__DIR__.'/data/logs/'.date('Ymd').'-api-error.log');
            $logger->addWriter($writerStream);
            $logger->crit($message);
            //UNCOMMENT FOR ERROR EMAILING
    //        $mail = new \Zend\Mail\Message();
    //        $mail->setFrom('your@mail.ie', 'Sender Name');
    //        $mail->addTo('admin@mail.ie', 'Receiver Name');
    //        $transport = new \Zend\Mail\Transport\Sendmail(); 
    //        $writerMail = new \Zend\Log\Writer\mail($mail, $transport);
    //        $writerMail->setSubjectPrependText("PHP Error :  $typestr : $errstr ");
    //         
    //        $logger->addWriter($writerMail);
            //log it!

            //Return a cosumable result
            header("HTTP/1.1 500 Internal Server Error");
            header("Content-type: application/json; charset=utf-8");
            header("Date: ". gmdate('D, d M Y H:i:s T'));
            echo json_encode(['success' => false, 'data' => false, 'errors' => 'Internal Server Error']);
            die();
        }
    }
}
