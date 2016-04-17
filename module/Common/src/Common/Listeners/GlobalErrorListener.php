<?php

/* * 
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
 * @description CLASS NAME - Short description
 */
namespace Common\Listeners;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\Http\Response;
use Zend\Log\Writer\Stream;
use Zend\Log\Logger;

class GlobalErrorListener extends AbstractListenerAggregate {
    /**
     * Method to register this listener on the render event
     *
     * @param EventManagerInterface $events 
     * @return void
     */
    public function attach(EventManagerInterface $events) {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, __CLASS__ . '::onRender', 1000);
        //handle the dispatch error (exception) 
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, __CLASS__ . '::onDispatchError', 1001);
        //handle the view render error (exception) 
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, __CLASS__ . '::onRenderError', 1002);
    }

    /**
     * Method executed when the render event is triggered
     *
     * @param MvcEvent $e 
     * @return void
     */
    public static function onRender(MvcEvent $e) {
        if ($e->getRequest() instanceOf \Zend\Console\Request || $e->getResponse()->isOk() || $e->getResponse()->getStatusCode() == Response::STATUS_CODE_401) {
            return;
        }

        $httpCode = $e->getResponse()->getStatusCode();
        $viewModel = $e->getResult();
        $exception = $viewModel->getVariable('exception');

//write log about exception
        if (isset($exception)) {
            self::writeExceptionLog($exception);
        }

        $model = new JsonModel(array(
            'result' => false,
            'errors' => 'Internal Error'
        ));
        $model->setTerminal(true);

        $e->setResult($model);
        $e->setViewModel($model);
        $e->getResponse()->setStatusCode($httpCode);
    }

    /**
     * Handle Errors with custom response
     * @param Event $e
     * @return HTTP\Response
     */
    public static function onDispatchError($e) {
        $exception = $e->getParam('exception');

//write log about exception
        if (isset($exception)) {
            self::writeExceptionLog($exception);
        }

        if ($e->getRequest() instanceOf \Zend\Console\Request) {
            return;
        }

        $error = $e->getError();
        if (!$error) {
            return;
        }

        $request = $e->getRequest();
        $headers = $request->getHeaders();
        if (!$headers->has('Accept')) {
// nothing to do; can't determine what we can accept 
            return;
        }

        $accept = $headers->get('Accept');
        if (!$accept->match('application/json')) {
// nothing to do; does not match JSON 
            return;
        }

        $status = \Zend\Http\Response::STATUS_CODE_200;
//Exchange error to human readable messages
        switch ($error) {
            case 'error-router-no-match':
                $status = \Zend\Http\Response::STATUS_CODE_404;
                $errors = 'Request error - URL does not match any endpoints';
                break;
            case 'error-controller-cannot-dispatch':
            case 'error-controller-not-found':
            case 'error-controller-invalid':
            default:
                $status = \Zend\Http\Response::STATUS_CODE_400;
                $errors = 'Request error - Please check you request';
                break;
        }

        $response = $e->getResponse();
        $response->getHeaders()->addHeaders([
            'Date: ' => gmdate('D, d M Y H:i:s T'),
            'Content-Type' => 'application/json; charset=UTF-8'
        ]);
        $response->setStatusCode($status);
        $response->setContent(\Zend\Json\Json::encode(['result' => false, 'errors' => $errors]));
        $response->sendHeaders();

        $e->stopPropagation();
        return $response;
    }

    /**
     * Handle Exceptions
     * @param \Common\Exception $e
     * @return type
     */
    public static function onRenderError($e) {
        $exception = $e->getParam('exception');
        self::writeExceptionLog($exception);

        if ($e->getRequest() instanceOf \Zend\Console\Request) {
            return;
        }

        $response = $e->getResponse();
        $response->getHeaders()->addHeaders([
            'Date: ' => gmdate('D, d M Y H:i:s T'),
            'Content-Type' => 'application/json; charset=UTF-8'
        ]);
        $response->setStatusCode(500);
        $response->setContent(\Zend\Json\Json::encode(['result' => false, 'errors' => 'Internal Error']));
        $response->sendHeaders();

        $e->stopPropagation();
        return $response;
    }

    public static function writeExceptionLog($exception) {
        try {
            $logger = new Logger;
//stream writer         
            $writerStream = new Stream('data/logs/' . date('Ymd') . '-api-exception.log');
            $logger->addWriter($writerStream);
            $logger->crit($exception);
        } catch (\Exception $e) {
            
        }
    }

}
