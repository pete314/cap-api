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

namespace User\Controller;

use Common\AControllers\AARestfulController;
use User\Helper\LoginHelper;

class LoginController extends AARestfulController{
    
    protected $createRequestKeys = [
      'password', 'email'  
    ];
    
    /**
     * Route the user creation request
     * @param type $data
     */
    public function create($data) {
        $loginHelper = new LoginHelper();
        return $loginHelper->routeCreateUserRequest($data, $this->response);
    }
}

