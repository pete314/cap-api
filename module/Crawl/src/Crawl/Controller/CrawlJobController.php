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
 * @description CrawlJob controller - handles the request for crawl jobs
 */

namespace Crawl\Controller;

use Common\AControllers\AARestfulController;
use Crawl\Helper\CrawlJobHelper;

/**
 * Reolves to the endpoint: /api/crawl/job/*
 */
class CrawlJobController extends AARestfulController{
    
    protected $updateRequestKeys = [
        'job_type', 'stratin_params', 'has_depth', 'recurrance'
    ];
    
    protected $createRequestKeys = [
        
    ];
    
    /**
     * Get single job report
     * @param string $id 
     * @return type
     */
    public function get($id) {
        $action = $this->params('actiion');
        $job_id = $this->params('jobid');
        if($action == 'report' && empty($job_id)){
            $crawlJobHelper = new CrawlJobHelper();
            return $crawlJobHelper->routeGetAllJobReportRequest($id, $this->response);
        }else if($action != 'report' && !empty($job_id)){
            $crawlJobHelper = new CrawlJobHelper();
            return $crawlJobHelper->routeGetJobReportRequest($id, $job_id, $this->response);
        }else{
            return $this->customJsonResponse();
        }
    }
    
    
    /**
     * Route the key release request
     * @param type $id
     * @param type $data
     */
    public function update($id, $data) {
        $action = $this->params('actiion');
        if($action != 'register'
                && \Common\Validators\StaticGeneralValidator::validateKeysValues($data, $this->updateRequestKeys)){
            return $this->customJsonResponse();
        }else{
            $crawlJobHelper = new CrawlJobHelper();
            return $crawlJobHelper->routeCreateJobRequest($id, $data, $this->response);
        }
    }
    
    /**
     * Route the user creation request
     * @param type $data
     */
    public function create($data) {
        $action = $this->params('actiion');
        $user_id = $this->params('id');
        if($action != 'register'
                && \Common\Validators\StaticGeneralValidator::validateKeysValues($data, $this->createRequestKeys)){
            return $this->customJsonResponse();
        }else{
            $crawlJobHelper = new CrawlJobHelper();
            return $crawlJobHelper->routeCreateJobRequest($user_id, $data, $this->response);
        }
    }
}

