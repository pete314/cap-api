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
 * @description User model - Database connector to the user table
 */

namespace User\Model;

use Common\Adapters\AbsctractCassandraAdapter;
use Zend\InputFilter\Factory as InputFactory;


class UserModel extends AbsctractCassandraAdapter {

    private static $table_name = 'users';
    private static $column_family = 'CapData';
    private static $columns = [
        'user_id', 'password', 'email', 'first_name', 'last_name',
        'public_key', 'private_key', 'created', 'updated'
    ];

    public function __construct() {
        parent::__construct();
    }

    public function createUser($data) {
        $query = 'insert into %s.%s (user_id, email, password, first_name, last_name, public_key, private_key) '
                . 'values(?, ?, ?, ?, ?, ?, ?)';

        $statement = parent::$session->prepare(sprintf($query, self::$column_family, self::$table_name));

        $options = new \Cassandra\ExecutionOptions([
            'arguments' => [
                $data['user_id'], $data['email'], $data['password'],
                $data['first_name'], $data['last_name'], $data['public_key'],
                $data['private_key']
            ]
        ]);
        
        parent::$session->execute($statement, $options);
        $result = $this->getUser($data['user_id']);

        return $result;
    }

    /**
     * Get user by id
     * @param string $id
     * @return array
     */
    public function getUser($id = '') {
        $query = "select * from %s.%s where user_id='%s'";
        return parent::$session->execute(new \Cassandra\SimpleStatement(sprintf($query, self::$column_family, self::$table_name, $id)));
    }

    /**
     * Get all users
     * @return array
     */
    public function getAllUsers() {
        $query = 'select * from %s.%s';
        return parent::$session->execute(new \Cassandra\SimpleStatement(sprintf($query, self::$column_family, self::$table_name)));
    }
    
    
    /**
     * Return a configured input filter to be able to validate and
     * filter the data.
     * 
     * @return InputFilter
     */
    public function getRegisterInputFilter(){
        $inputFilter = new \Zend\InputFilter\InputFilter();
        $factory = new InputFactory();
        
        /*
         * email
         * password
         * first_name
         * surname
         */
        // Email field filter
        $inputFilter->add($factory->createInput(array(
            'name'      => 'email',
            'required'  => true,
            'filters'   => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 6,
                        'max' => 254
                    ),
                ),
                array(
                    'name' => 'EmailAddress',
                ),
            ),
        )));
        
        // password field feilter
        $inputFilter->add($factory->createInput(array(
            'name'      => 'password',
            'required'  => true,
            'filters'   => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 1,
                        'max' => 25
                    ),
                )
            ),
        )));
        
        // first_name field feilter
        $inputFilter->add($factory->createInput(array(
            'name'      => 'first_name',
            'required'  => true,
            'filters'   => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 1,
                        'max' => 100
                    ),
                ),
            ),
        )));
        
        // surname field feilter
        $inputFilter->add($factory->createInput(array(
            'name'      => 'last_name',
            'required'  => true,
            'filters'   => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 1,
                        'max' => 100
                    ),
                ),
            ),
        )));
        
        return $inputFilter;
    }
}
