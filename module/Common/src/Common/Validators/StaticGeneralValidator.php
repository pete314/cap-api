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
    
    /**
     * Validate a single value based on \Zend|Validator\*
     * 
     * @param string $validator
     * @param var $value
     * @param array $options - eg ['min' => 2, 'max' => 62]
     * @return type
     */
    public static function validateValue($validator, $value, $options = []){
        switch($validator){
            case 'Email':
                $validator = new \Zend\Validator\EmailAddress();
                break;
            case 'StringLength':
                //options = ['min' => 3, 'max' => 30]
                $validator = new \Zend\Validator\StringLength($options);
                break;
            case 'Uri':
                $validator = new \Zend\Validator\Uri(['allowRelative' => false]);
            default:
                return ['value' => $value, 'errors' => null];
        }
        
        if($validator->isValid($value)){
            return ['value' => $value, 'errors' => null];
        }else{
            return ['value' => $value, 'errors' => $validator->getMessages()];
        }
    }
    
    /**
     * Validate array keys and values are set
     * 
     * @param array $arr - the array to check
     * @param array $keys - the keys to check
     * @param bool $buildByKeys - if true an array is returned with the scedified keys
     * @return boolean
     */
    public static function validateKeysValues(&$arr, $keys = [], $buildByKeys = false) {
        if (!is_array($arr) || count($keys) == 0){
            return false;
        }
        $arrObj = new \Zend\Stdlib\ArrayObject($arr);
        
        $validArr = [];
        foreach($keys as $key){
            $value = $arrObj->offsetGet($key);
            if(is_string($value)){
                $value = trim($value);
            }
            if(empty($value) && !is_numeric($value)){//exclude 0 as that may be in use
                return false;
            }
            $validArr[$key] = $value;
        }
        
        return (array)$arrObj->getArrayCopy();
    }
    
}

