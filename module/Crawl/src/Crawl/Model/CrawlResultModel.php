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

class CrawlResultModel extends AbsctractCassandraAdapter {

    private static $links_table_name = 'crawled_links';
    private static $content_dump_table_name = 'crawl_dump';
    private static $column_family = 'CrawlDump';
    private static $links_columns = [
        'url_hash', 'crawler_job', 'created', 'url', 'status', 'message'
    ];
    private static $content_columns = [
        'url_hash', 'content', 'crawler_job', 'url', 'scraper_type'
    ];

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get crawled links for job
     * @param string $jobId
     * @return Cassandra\Rows
     */
    public function getCrawledLinksByJobId($jobId){
        $query = "select url_hash as id, url, status, message, created from %s.%s where crawler_job='%s'";
        return parent::$session->execute(new \Cassandra\SimpleStatement(sprintf($query, self::$column_family, self::$links_table_name, $jobId)));
    }
    
    /**
     * Get crawled conent
     * !!!RETURNS LARGE BLOBS!!!
     * 
     * @param string $jobId
     * @return Cassandra\Rows
     */
    public function getCrawledConentByJobId($jobId){
        $query = "select url_hash as id, url, content, scrape_type, created from %s.%s where crawler_job='%s'";
        return parent::$session->execute(new \Cassandra\SimpleStatement(sprintf($query, self::$column_family, self::$content_dump_table_name, $jobId)));
    }
    
}