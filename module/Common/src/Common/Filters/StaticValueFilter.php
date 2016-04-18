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
 * @description Static value filter - Does what it stands for
 */

namespace Common\Filters;

class StaticValueFilter{
    
    /**
     * Filter single value
     * 
     * @param var $value
     * @param String $filterName
     * @return var
     */
    public static function filterSingle($value, $filterName){
        switch ($filterName) {
            case 'Trim':
                return \Zend\Filter\StaticFilter::execute($value, 'StringTrim');
            case 'StripTags':
                return \Zend\Filter\StaticFilter::execute($value, 'StripTags');
            case 'Digits':
                return \Zend\Filter\StaticFilter::execute($value, 'Digits');
        }
    }
    
    /**
     * Filter accociative array values
     * 
     * @param array $arrData
     * @param String array $filters
     */
    public static function filterArrayValues(&$arrData, $filters = ['StringTrim']){
        $result =[];
        foreach($arrData as $key => $value){
            foreach($filters as $filter){
                $result[$key]  = self::filterSingle($arrData[$key], $filter);
            }
        }
        return $result;
    }
}