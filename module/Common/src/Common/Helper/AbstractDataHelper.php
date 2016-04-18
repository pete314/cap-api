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
 * @description AbstractDatahelper - Collection of convinient functions
 */
namespace Common\Helper;

abstract class AbstractDataHelper{
    
    /**
     * Create response
     * 
     * @todo create response factory for this
     * 
     * @param Rsponse Object $response
     * @param Response::Status_Code $statusCode
     * @param array $jsonData
     * @param array $headers
     */
    protected function generateResponse(&$response, $statusCode, $jsonData = [], $headers = []) {
        $response->setStatusCode($statusCode);
        $response->getHeaders()->addHeaders(array(
            'Date: ' => gmdate('D, d M Y H:i:s T'),
            'Content-Type' => 'application/json; charset=UTF-8'
                ) + $headers);
        if (count($jsonData) > 0) {
            $response->setContent(\Zend\Json\Json::encode($jsonData));
        }
    }

    /**
     * Create custom response
     * 
     * @todo create response factory for this
     * 
     * @param Rsponse Object $response
     * @param Response::Status_Code $statusCode
     * @param json_encode content
     * @param array $headers
     */
    protected function generateCustomResponse(&$response, $statusCode, $content, $headers = []) {
        $response->setStatusCode($statusCode);
        $response->getHeaders()->addHeaders(array(
            'Date: ' => gmdate('D, d M Y H:i:s T'),
            'Content-Type' => 'application/json; charset=UTF-8'
                ) + $headers);
        $response->setContent($content);
    }

}
