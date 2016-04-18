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

namespace Common\Validators;

class StaticGeneralValidator{
    
    
    public static function validateValue($validator, $value, $options = []){
        switch($validator){
            case 'Email':
                $validator = new \Zend\Validator\EmailAddress();
                break;
            case 'StringLength':
                //options = ['min' => 3, 'max' => 30]
                $validator = new Zend\Validator\StringLength($options);
                break;
            default:
                return ['value' => $value, 'errors' => null];
        }
        
        if($validator->isValid($value)){
            return ['value' => $value, 'errors' => null];
        }else{
            return ['value' => $value, 'errors' => $validator->getMessages()];
        }
    }
    
}

