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

namespace Crawl\Model;

use Common\Adapters\AbsctractCassandraAdapter;

class CrawlJobModel extends AbsctractCassandraAdapter {

    private static $table_name = 'crawl_job';
    private static $column_family = 'CapData';
    private static $columns = [
        'job_id', 'user_id', 'job_type', 'stratin_params', 'has_depth', 
        'recurrance', 'created', 'started', 'finished'
    ];

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Create crawling job entry
     * 
     * @param array $data
     * @return int affectedRows :)
     */
    public function createJob($data){
        
        $query = "insert into %s.%s (job_id, user_id, job_type, startin_params, has_depth, recurrance, created) "
                . "values(?, ?, ?, ?, 'N/A', ?, toTimestamp(now()))";

        $statement = parent::$session->prepare(sprintf($query, self::$column_family, self::$table_name));
        
        $options = new \Cassandra\ExecutionOptions([
            'arguments' => [
                $data['job_id'], $data['user_id'], $data['job_type'], $data['startin_params'],
                $data['recurrance']
            ]
        ]);
        parent::$session->execute($statement, $options);
        return $this->getJobById($data['job_id'])->count();
    }
    
    /**
     * Method to get job by id
     * @param string $jobId
     * @return Cassandra\Rows
     */
    public function getJobById($jobId){
        $query = "select * from %s.%s where job_id='%s'";
        return parent::$session->execute(new \Cassandra\SimpleStatement(sprintf($query, self::$column_family, self::$table_name, $jobId)));
    }
    
    /**
     * 
     * @param type $userId
     * @return Cassandra\Rows
     */
    public function getJobByUserId($userId){
        $query = "select * from %s.%s where user_id='%s'";
        return parent::$session->execute(new \Cassandra\SimpleStatement(sprintf($query, self::$column_family, self::$table_name, $userId)));
    }
}