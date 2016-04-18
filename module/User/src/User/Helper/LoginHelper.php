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
 * @description Login Helper - Business class for Login controller
 */

namespace User\Helper;

use Common\Helper\AbstractDataHelper;

class LoginHelper extends AbstractDataHelper{
    
    public function routeCreateUserRequest($unfilteredData, $response){
        $user_model = new \User\Model\UserModel();
        $user_model->getUser();
        
        $this->generateResponse($response, 200, ['success' => true, 'data' => [], 'errors']);
        return $response;
    }
}
