<?php

/*  * 
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

namespace Common\AControllers;

use Zend\Mvc\Exception;
use Zend\Mvc\MvcEvent;
use Zend\Http\Request as HttpRequest;
use Zend\Json\Json;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Mvc\Controller\AbstractController;


abstract class AARestfulController extends AbstractController {
    const CONTENT_TYPE_JSON = 'json';

    /**
     * {@inheritDoc}
     */
    protected $eventIdentifier = __CLASS__;

    /**
     * @var array
     */
    protected $contentTypes = [
        self::CONTENT_TYPE_JSON => [
            'application/hal+json',
            'application/json'
        ]
    ];

    /**
     * Name of request or query parameter containing identifier
     *
     * @var string
     */
    protected $identifierName = 'id';

    /**
     * @var int From Zend\Json\Json
     */
    protected $jsonDecodeType = Json::TYPE_ARRAY;

    /**
     * Map of custom HTTP methods and their handlers
     *
     * @var array
     */
    protected $customHttpMethodsMap = [];

    /**
     * Set the route match/query parameter name containing the identifier
     *
     * @param  string $name
     * @return self
     */
    public function setIdentifierName($name)
    {
        $this->identifierName = (string) $name;
        return $this;
    }

    /**
     * Retrieve the route match/query parameter name containing the identifier
     *
     * @return string
     */
    public function getIdentifierName()
    {
        return $this->identifierName;
    }
    
    /**
     * Create a new resource
     *
     * @param  mixed $data
     * @return mixed
     */
    public function create($data) {
        return $this->methodNotAllowed();
    }

    /**
     * Delete an existing resource
     *
     * @param  mixed $id
     * @return mixed
     */
    public function delete($id) {
        return $this->methodNotAllowed();
    }

    /**
     * Delete the entire resource collection
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @return mixed
     */
    public function deleteList($data) {
        return $this->methodNotAllowed();
    }

    /**
     * Return single resource
     *
     * @param  mixed $id
     * @return mixed
     */
    public function get($id) {
        return $this->methodNotAllowed();
    }

    /**
     * Return list of resources
     *
     * @return mixed
     */
    public function getList() {
        return $this->methodNotAllowed();
    }

    /**
     * Retrieve HEAD metadata for the resource
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @param  null|mixed $id
     * @return mixed
     */
    public function head($id = null) {
        return $this->methodNotAllowed();
    }

    /**
     * Respond to the OPTIONS method
     *
     * Typically, set the Allow header with allowed HTTP methods, and
     * return the response.
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @return mixed
     */
    public function options() {
        return $this->methodNotAllowed();
    }

    /**
     * Respond to the PATCH method
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @param  $id
     * @param  $data
     * @return array
     */
    public function patch($id, $data) {
        return $this->methodNotAllowed();
    }

    /**
     * Replace an entire resource collection
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @param  mixed $data
     * @return mixed
     */
    public function replaceList($data) {
        return $this->methodNotAllowed();
    }

    /**
     * Modify a resource collection without completely replacing it
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.2.0); instead, raises an exception if not implemented.
     *
     * @param  mixed $data
     * @return mixed
     */
    public function patchList($data) {
        return $this->methodNotAllowed();
    }

    /**
     * Update an existing resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return mixed
     */
    public function update($id, $data) {
        return $this->methodNotAllowed();
    }

    /**
     * Dispatch a request
     *
     * If the route match includes an "action" key, then this acts basically like
     * a standard action controller. Otherwise, it introspects the HTTP method
     * to determine how to handle the request, and which method to delegate to.
     *
     * @events dispatch.pre, dispatch.post
     * @param  Request $request
     * @param  null|Response $response
     * @return mixed|Response
     * @throws Exception\InvalidArgumentException
     */
    public function dispatch(Request $request, Response $response = null)
    {
        if (! $request instanceof HttpRequest) {
            throw new Exception\InvalidArgumentException(
                    'Expected an HTTP request');
        }

        return parent::dispatch($request, $response);
    }
    
    /**
     * Handle the request
     *
     * @todo   try-catch in "patch" for patchList should be removed in the future
     * @param  MvcEvent $e
     * @return mixed
     * @throws Exception\DomainException if no route matches in event or invalid HTTP method
     */
    public function onDispatch(MvcEvent $e) {
        $routeMatch = $e->getRouteMatch();
        if (!$routeMatch) {
            /**
             * @todo Determine requirements for when route match is missing.
             *       Potentially allow pulling directly from request metadata?
             */
            throw new Exception\DomainException(
            'Missing route matches; unsure how to retrieve action');
        }

        $request = $e->getRequest();
        
        // Was an "action" requested?
        $action = $routeMatch->getParam('action', false);
        if ($action) {
            // Handle arbitrary methods, ending in Action
            $method = static::getMethodFromAction($action);
            if (!method_exists($this, $method)) {
                $method = 'notFoundAction';
            }
            $return = $this->$method();
            $e->setResult($return);
            return $return;
        }

        // RESTful methods
        $method = strtolower($request->getMethod());
        switch ($method) {
            // Custom HTTP methods (or custom overrides for standard methods)
            case (isset($this->customHttpMethodsMap[$method])):
                $callable = $this->customHttpMethodsMap[$method];
                $action = $method;
                $return = call_user_func($callable, $e);
                break;
            // DELETE
            case 'delete':
                $id = $this->getIdentifier($routeMatch, $request);
                $data = $this->processBodyContent($request);

                if ($id !== false) {
                    $action = 'delete';
                    $return = $this->delete($id);
                    break;
                }

                $action = 'deleteList';
                $return = $this->deleteList($data);
                break;
            // GET
            case 'get':
                $id = $this->getIdentifier($routeMatch, $request);
                if ($id !== false) {
                    $action = 'get';
                    $return = $this->get($id);
                    break;
                }
                $action = 'getList';
                $return = $this->getList();
                break;
            // HEAD
            case 'head':
                $id = $this->getIdentifier($routeMatch, $request);
                if ($id === false) {
                    $id = null;
                }
                $action = 'head';
                $headResult = $this->head($id);
                $response = ($headResult instanceof Response) ? clone $headResult : $e->getResponse();
                $response->setContent('');
                $return = $response;
                break;
            // OPTIONS
            case 'options':
                $action = 'options';
                $this->options();
                $return = $e->getResponse();
                break;
            // PATCH
            case 'patch':
                $id = $this->getIdentifier($routeMatch, $request);
                $data = $this->processBodyContent($request);

                if ($id !== false) {
                    $action = 'patch';
                    $return = $this->patch($id, $data);
                    break;
                }

                // TODO: This try-catch should be removed in the future, but it
                // will create a BC break for pre-2.2.0 apps that expect a 405
                // instead of going to patchList
                try {
                    $action = 'patchList';
                    $return = $this->patchList($data);
                } catch (Exception\RuntimeException $ex) {
                    return $this->methodNotAllowed();
                }
                break;
            // POST
            case 'post':
                $action = 'create';
                $return = $this->processPostData($request);
                break;
            // PUT
            case 'put':
                $id = $this->getIdentifier($routeMatch, $request);
                $data = $this->processBodyContent($request);

                if ($id !== false) {
                    $action = 'update';
                    $return = $this->update($id, $data);
                    break;
                }

                $action = 'replaceList';
                $return = $this->replaceList($data);
                break;
            // All others...
            default:
                return $this->methodNotAllowed();
        }

        $routeMatch->setParam('action', $action);
        $e->setResult($return);
        return $return;
    }
    
    
    /**
     * Process post data and call create
     *
     * @param Request $request
     * @return mixed
     */
    public function processPostData(Request $request)
    {
        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
            $data = Json::decode($request->getContent(), $this->jsonDecodeType);
        } else {
            $data = $request->getPost()->toArray();
        }

        return $this->create($data);
    }

    /**
     * Check if request has certain content type
     *
     * @param  Request $request
     * @param  string|null $contentType
     * @return bool
     */
    public function requestHasContentType(Request $request, $contentType = '')
    {
        /** @var $headerContentType \Zend\Http\Header\ContentType */
        $headerContentType = $request->getHeaders()->get('content-type');
        if (!$headerContentType) {
            return false;
        }

        $requestedContentType = $headerContentType->getFieldValue();
        if (strstr($requestedContentType, ';')) {
            $headerData = explode(';', $requestedContentType);
            $requestedContentType = array_shift($headerData);
        }
        $requestedContentType = trim($requestedContentType);
        if (array_key_exists($contentType, $this->contentTypes)) {
            foreach ($this->contentTypes[$contentType] as $contentTypeValue) {
                if (stripos($contentTypeValue, $requestedContentType) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Register a handler for a custom HTTP method
     *
     * This method allows you to handle arbitrary HTTP method types, mapping
     * them to callables. Typically, these will be methods of the controller
     * instance: e.g., array($this, 'foobar'). The typical place to register
     * these is in your constructor.
     *
     * Additionally, as this map is checked prior to testing the standard HTTP
     * methods, this is a way to override what methods will handle the standard
     * HTTP methods. However, if you do this, you will have to retrieve the
     * identifier and any request content manually.
     *
     * Callbacks will be passed the current MvcEvent instance.
     *
     * To retrieve the identifier, you can use "$id =
     * $this->getIdentifier($routeMatch, $request)",
     * passing the appropriate objects.
     *
     * To retrieve the body content data, use "$data = $this->processBodyContent($request)";
     * that method will return a string, array, or, in the case of JSON, an object.
     *
     * @param  string $method
     * @param  Callable $handler
     * @return AbstractRestfulController
     */
    public function addHttpMethodHandler($method, /* Callable */ $handler)
    {
        if (!is_callable($handler)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid HTTP method handler: must be a callable; received "%s"',
                (is_object($handler) ? get_class($handler) : gettype($handler))
            ));
        }
        $method = strtolower($method);
        $this->customHttpMethodsMap[$method] = $handler;
        return $this;
    }

    /**
     * Retrieve the identifier, if any
     *
     * Attempts to see if an identifier was passed in either the URI or the
     * query string, returning it if found. Otherwise, returns a boolean false.
     *
     * @param  \Zend\Mvc\Router\RouteMatch $routeMatch
     * @param  Request $request
     * @return false|mixed
     */
    protected function getIdentifier($routeMatch, $request)
    {
        $identifier = $this->getIdentifierName();
        $id = $routeMatch->getParam($identifier, false);
        if ($id !== false) {
            return $id;
        }

        $id = $request->getQuery()->get($identifier, false);
        if ($id !== false) {
            return $id;
        }

        return false;
    }

    /**
     * Process the raw body content
     *
     * If the content-type indicates a JSON payload, the payload is immediately
     * decoded and the data returned. Otherwise, the data is passed to
     * parse_str(). If that function returns a single-member array with a empty
     * value, the method assumes that we have non-urlencoded content and
     * returns the raw content; otherwise, the array created is returned.
     *
     * @param  mixed $request
     * @return object|string|array
     */
    protected function processBodyContent($request)
    {
        $content = $request->getContent();

        // JSON content? decode and return it.
        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
            return Json::decode($content, $this->jsonDecodeType);
        }

        parse_str($content, $parsedParams);

        // If parse_str fails to decode, or we have a single element with empty value
        if (!is_array($parsedParams) || empty($parsedParams)
            || (1 == count($parsedParams) && '' === reset($parsedParams))
        ) {
            return $content;
        }

        return $parsedParams;
    }

    /**
     * Basic functionality for when a page is not available
     *
     * @return array
     */
    public function notFoundAction() {
        return $this->customJsonResponse(404, 'Method not found');
    }

    public function methodNotAllowed() {
        $this->response->setStatusCode(\Zend\Http\PhpEnvironment\Response::STATUS_CODE_405);
        $this->response->getHeaders()->addHeaders(array(
            'Date: ' => gmdate('D, d M Y H:i:s T'),
            'Content-Type' => 'application/json; charset=UTF-8'
        ));
        $this->response->setContent(\Zend\Json\Json::encode(['success' => false, 'data' => [], 'errors' => 'Requested method is not allowed on this endpoint']));
        return $this->response;
    }

    public function customJsonResponse($status_code = 405, $messages = 'Requested method is not allowed on this endpoint') {
        $this->response->setStatusCode($status_code);
        $this->response->getHeaders()->addHeaders(array(
            'Date' => gmdate('D, d M Y H:i:s T'),
            'Content-Type' => 'application/json; charset=UTF-8'
        ));
        $this->response->setContent(\Zend\Json\Json::encode(['success' => false, 'data' => [], 'errors' => $messages], false, [JSON_UNESCAPED_UNICODE]));
        return $this->response;
    }

    public function customContentResponse($content, $status_code = 405, $headers = []) {
        $this->response->setStatusCode($status_code);
        $this->response->getHeaders()->addHeaders(array(
            'Date' => gmdate('D, d M Y H:i:s T'),
            'Content-Type' => 'application/json; charset=UTF-8'
                ) + $headers);
        $this->response->setContent($content);
        return $this->response;
    }
}

