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
 * @description SLogWriter - static log writer
 */

namespace Common\Helper;

class SLogWriter {
    
    /**
     * Write logs with current date as base name
     * @param String $fileName - eg: file-name, don't include the extension
     * @param String $formatedContent
     */
    public static function writeLog($fileName, $formatedContent) {
        try{
            $logger = new \Zend\Log\Logger;
            //stream writer         
            $writerStream = new \Zend\Log\Writer\Stream('data/logs/' . date('Ymd') . "-$fileName.log");
            $logger->addWriter($writerStream);
            $logger->crit($formatedContent);
        }catch(\Exception $e){
            //The folder is no writable
        }
    }
    
    public static function printToErrorLog($message){
        error_log(print_r($message, true));
    }
}
