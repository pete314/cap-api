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

class UserHelper extends AbstractDataHelper {
    
    /**
     * Create user
     * @param array $data
     * @param HTTP|Response $response
     * @return HTTP|Response
     */
    public function routeCreateUserRequest($data, $response) {
        $user_model = new \User\Model\UserModel();
        $regFilter = $user_model->getRegisterInputFilter();
        $regFilter->setData($data);
        
        if($regFilter->isValid()){
            $filteredData = $regFilter->getValues();
            
            $filteredData['user_id'] = \Common\Crypto\SCryptoWrapper::generateUserId($filteredData['email']);
            $result = $user_model->getUser($filteredData['user_id']);
            
            if($result->count() == 0){
                $filteredData['password'] = \Common\Crypto\SCryptoWrapper::generatePasswordHash($filteredData['password']);
                $filteredData['public_key'] = \Common\Crypto\SCryptoWrapper::generatePublicKey();
                $filteredData['private_key'] = \Common\Crypto\SCryptoWrapper::generatePrivateKey();
                $filteredData['user_id'] = \Common\Crypto\SCryptoWrapper::generateUserId($filteredData['email']);

                //Returns Cassandra\Rows 
                $result = $user_model->createUser($filteredData);
                if($result->count() == 1){
                    \Common\Factories\EmailFactory::sendWelcomeEmail(
                            $filteredData['email'], 
                            sprintf('%s %s', $filteredData['first_name'], $filteredData['last_name']),
                            'Welcome to CAP api', 
                            ['user_id' => $filteredData['user_id'], 
                             'public_key' => $filteredData['public_key'],
                             'private_key' => $filteredData['private_key']]
                            );
                    $this->generateResponse($response, 200, [
                        'success' => true, 
                        'data' => [
                            'user_id' => $filteredData['user_id'], 
                            'public_key' => $filteredData['public_key'],
                            'private_key' => $filteredData['private_key']
                            ], 
                        'errors' => $regFilter->getMessages()]);
                }
            }else{
                $this->generateResponse($response, 200, [
                        'success' => false, 
                        'data' => [], 
                        'errors' => 'User already exists']);
            }
        }else{
            $this->generateResponse($response, 400, ['success' => false, 'data' => [], 'errors' => $regFilter->getMessages()]);
        }
        return $response;
    }

}
