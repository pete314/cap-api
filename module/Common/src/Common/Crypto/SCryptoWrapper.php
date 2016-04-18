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
 * @description Static Crypto wrapper - Crypto functions
 */

namespace Common\Crypto;

use Zend\Crypt\Password\Bcrypt;

class SCryptoWrapper{
    
    public static function generatePasswordHash($password){
        return (new Bcrypt())->create($password);
    }
    
    public static function verifyPassword($password, $storedHash){
        return (new Bcrypt())->verify($password, $storedHash);
    }
    
    public static function generateUserId($email){
        return md5($email);
    }
    
    public static function generatePublicKey(){
        return md5(uniqid() . \Zend\Math\Rand::getString(64));
    }
    
    public static function generatePrivateKey(){
        return hash('sha256', (uniqid() . \Zend\Math\Rand::getString(128)));
    }
    
    public static function calculatePayloadHmac(&$private_key, &$payload, $diggest){
        return $diggest == \Zend\Crypt\Hmac::compute($private_key, 'sha256', $payload);
    }
}