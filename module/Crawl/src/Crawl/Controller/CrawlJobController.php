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
 * @description CrawlJob controller - handles the request for crawl jobs
 */

namespace Crawl\Controller;

use Common\AControllers\AARestfulController;
use User\Helper\UserHelper;

/**
 * Reolves to the endpoint: /api/crawl/job/*
 */
class UserController extends AARestfulController{
    
    protected $createRequestKeys = [
      'password', 'email', 'first_name', 'last_name'  
    ];
    
    protected $updateRequestKeys = [
        'password', 'email'
    ];
    /**
     * Route the key release request
     * @param type $id
     * @param type $data
     */
    public function update($id, $data) {
        $action = $this->params('actiion');
        if(!empty($id) &&
                $action != 'secret'
                && false === \Common\Validators\StaticGeneralValidator::validateKeysValues($data, $this->createRequestKeys)){
            return $this->customJsonResponse();
        }else{
            $userHelper = new UserHelper();
            return $userHelper->routeUpdateRequest($id, $data, $this->response);
        }
    }
    
    /**
     * Route the user creation request
     * @param type $data
     */
    public function create($data) {
        $action = $this->params('actiion');
        if($action != 'register'
                && \Common\Validators\StaticGeneralValidator::validateKeysValues($data, $this->createRequestKeys)){
            return $this->customJsonResponse();
        }else{
            $userHelper = new UserHelper();
            return $userHelper->routeCreateUserRequest($data, $this->response);
        }
    }
}

