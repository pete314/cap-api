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

class UserModel {

    protected static function getConnection() {

        $nodes = [
            '127.0.0.1'];
        $connection = new \Cassandra\Connection($nodes, 'CapData');

        //Connect
        try {
            $connection->connect();
            return $connection;
        } catch (\Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }

        return null;
    }

    public function getUser($id = '') {
        $cluster = \Cassandra::cluster()
               ->withContactPoints('localhost')
               ->withPort(9042)
               ->build();
        $session = $cluster->connect();
        error_log(print_r($session, true));
    }

}
