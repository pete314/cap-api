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
 * @description CrawlResult controller - handles the request for crawl jobs
 */

namespace Crawl\Controller;

use Common\AControllers\AARestfulController;
use Crawl\Helper\CrawlResultHelper;

/**
 * Reolves to the endpoint: /api/crawl/result/*
 */
class CrawlResultController extends AARestfulController{
    
    protected $updateRequestKeys = [
        'job_type', 'stratin_params', 'has_depth', 'recurrance'
    ];
    
    protected $createRequestKeys = [
        
    ];
    
    /**
     * Get single job result
     * 
     * format, actiion = [Json, Excel if links, zip download]
     * @param string $id 
     * @return type
     */
    public function get($id) {
        $action = $this->params('actiion');
        $job_id = $this->params('jobid');
        
        if(($action == 'json' || $action == 'csv') && strlen($job_id) >= 30){
            $crawlResultHelper = new CrawlResultHelper();
            return $crawlResultHelper->routeGetJobResultRequest($id, $job_id, $action, $this->response);
        }else{
            return $this->customJsonResponse();
        }
    }
}

