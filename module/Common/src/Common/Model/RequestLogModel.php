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
 * @description RequestLogModel - wrapper for request_log cassandra table
 */

namespace Common\Model;

use Common\Adapters\AbsctractCassandraAdapter;

class RequestLogModel extends AbsctractCassandraAdapter {

    private static $table_name = 'request_log';
    private static $column_family = 'CapData';
    private static $columns = [
        'id','redirect_url', 'remote_addr', 'request_path', 'request_auth', 'created'
    ];

    public function __construct() {
        parent::__construct();
    }
    
    public function createLogEntry($data){
        $query = "insert into %s.%s (id, redirect_url, remote_addr, request_path, request_auth, created) "
                . "values(?, ?, ?, ?, ?, toTimestamp(now()))";

        $statement = parent::$session->prepare(sprintf($query, self::$column_family, self::$table_name));

        $options = new \Cassandra\ExecutionOptions([
            'arguments' => [
                uniqid("", true), $data['redirect_url'], $data['remote_addr'], $data['request_path'],
                $data['request_auth']
            ]
        ]);
        //Async execution, no point to wait for this, just dump
        parent::$session->executeAsync($statement, $options);
    }
}