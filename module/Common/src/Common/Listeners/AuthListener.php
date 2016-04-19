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
 * @description AuthListener - Listener for authentication(currently hmac)
 */

namespace Common\Listeners;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;

class AuthListener extends AbstractListenerAggregate {

    /**
     * Holds the attached listeners
     * 
     * @var array
     */
    protected $listeners = array();

    protected static $requestLogModel;
    /**
     * Method to register this listener on the render event
     *
     * @param EventManagerInterface $events 
     * @return void
     */
    public function attach(EventManagerInterface $events) {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, __CLASS__ . '::onDispatch', 1000);
    }

    /**
     * Method executed when the dispatch event is triggered
     * Note: 
     * The verification is based on the route and the sent diggest/public key
     * At the moment, hmac based on payload and/or url check is implemented
     * OAuth can be performed with same way with little modifications.
     * All routes require authentication except 'user-general' - as it may be
     * 
     * @param MvcEvent $event
     * @return void
     */
    public static function onDispatch(MvcEvent $event) {
        if ($event->getRequest() instanceOf \Zend\Console\Request) {
            return;
        }
        self::logRequest($event);
        
        $routeName = $event->getRouteMatch()->getMatchedRouteName();
        
        if ($routeName == 'user-general') {
            return;
        } else if (false === self::authorizeRequest($event)) {
            //@todo:log failed requests sepratly
            $response = $event->getResponse();
            self::generateFailedAuthResponse($response);
            return $response;
        }
        return;
    }
    
    /**
     * Method to check request is valid, based on the payload & headers
     * @param MvcEvent $event
     * @return boolean
     */
    private static function authorizeRequest(MvcEvent &$event){
        if (!$event->getRequest()->getHeaders('authorization')) {
            return false;
        }
        
        $authTokens = $event->getRequest()->getHeaders('authorization')->getFieldValue();
        $authType = substr($authTokens, 0, 6);
        $keys = explode(':', substr($authTokens, 6));

        if ($authType == "CAPMAC" && count($keys) == 2 && strlen($keys[0]) == 32) {
            $result = (new \User\Model\UserModel())->getUserByPublicKey($keys[0]);
            if($result && $result->count() == 1){
                $content = $event->getRequest()->getContent();
                if (empty($content)) {
                    //Request body is empty, get the url
                    $content = $event->getRequest()->getUri()->getPath();
                }
                return \Common\Crypto\SCryptoWrapper::calculatePayloadHmac($result[0]['private_key'], $content, $keys[1]);
            }
            
        }

        return false;
    }
    
    /**
     * Generate response for failed authentication
     * @param HTTP\Response $response
     */
    private static function generateFailedAuthResponse(&$response) {
        $response->getHeaders()->addHeaders([
            'Date' => gmdate('D, d M Y H:i:s T'),
            'Content-Type' => 'application/json; charset=UTF-8'
        ]);
        $response->setContent(\Zend\Json\Json::encode(['result' => false, 'errors' => 'Request quthorization failed, check keys/payload']));
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setStatusCode(405);
    }

    /**
     * Log requests
     * @param MvcEvent $event
     */
    private static function logRequest(MvcEvent &$event) {
        $request = $event->getRequest();
        
        $requestData = [];
        $requestData['redirect_url'] =  null == $request->getServer('REDIRECT_URL') ? $request->getServer('REDIRECT_URL') : "No URL";
        $requestData['remote_addr']  =  null == $request->getServer('REMOTE_ADDR') ? $request->getServer('REMOTE_ADDR') : "No IP";
        $requestData['request_path'] =  null == $request->getUri()->getPath() ? $request->getUri()->getPath() : "No Path";
        $requestData['request_auth'] =  $request->getHeaders('authorization') ? $request->getHeaders('authorization')->getFieldValue() : "No Auth";
        
        if(!self::$requestLogModel){
            self::$requestLogModel = new \Common\Model\RequestLogModel();
        }
        self::$requestLogModel->createLogEntry($requestData);
    }

}
