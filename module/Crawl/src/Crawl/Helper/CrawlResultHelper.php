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
 * @description CrawlJobHelper - Business class for Crwal job controller
 */

namespace Crawl\Helper;

use Common\Helper\AbstractDataHelper;

class CrawlResultHelper extends AbstractDataHelper {
   
    /**
     * Router for get sinlge job report
     * @param string $user_id
     * @param string $job_id
     * @param HTTP\Response $response
     * @return HTTP\Response
     */
    public function routeGetJobResultRequest($user_id, $job_id, $data_type, $response){
        $craw_job_model = new \Crawl\Model\CrawlJobModel();
        $user_model = new \User\Model\UserModel();
        
        $userArr = $user_model->getUser($user_id);
        $crawJobArr = $craw_job_model->getJobById($job_id);
        if($crawJobArr->count() == 1 
                && $userArr->count() == 1
                && $crawJobArr[0]['user_id'] == $userArr[0]['user_id']){
            $crawl_job_result_model = new \Crawl\Model\CrawlResultModel();
            $result_rows = $crawl_job_result_model->getCrawledLinksByJobId($job_id);
            
            if($data_type == 'json'){
                $result = ['total_count' => $result_rows->count(), 'link_data' => []];
                foreach($result_rows as $row){
                    $result['link_data'][] = $row + ['gmt' => $row['created']->toDateTime()->format('D, d M Y H:i:s T')];

                }
                $this->generateResponse($response, 200, ['success' => true, 'data' => $result, 
                    'errors' => null]);
            }else{
                //Will be a stream
                return $this->prepareCsvDownload($result_rows, $job_id);
            }
        }else{
            $this->generateResponse($response, 404, ['success' => false, 'data' => [], 
                'errors' => 'User or job not found']);
        }
        return $response;
    }
    
    protected function prepareCsvDownload(&$result_rows, $data_type, $job_id){
        if($result_rows->count() > 0){
            $stream = fopen('php://output', 'w');
            // id, url, status, message, created
            fputcsv($stream, ['id', 'url', 'status', 'message', 'created', 'gmt']);
            foreach ($result_rows as $row) {
                fputcsv($stream, [$row['id'], $row['url'], $row['status'], $row['message'], $row['created']->time(), $row['created']->toDateTime()->format('D, d M Y H:i:s T')]);
            }
            fclose($stream);
        }
        
        return $this->generateStreamResponse($stream, $job_id);
    }
    
    
    /**
     * Generate stream response
     * 
     * @param FilePointer $stream
     * @param String $job_id
     * @return \Crawl\Helper\Stream
     */
    protected function generateStreamResponse($stream, $job_id){
        $response = new \Zend\Http\Response\Stream();
        $response->setStream($stream);
        $response->setStatusCode(200);
        $response->setStreamName("CAP_link_crawl_result_$job_id.csv");
        
        $headers = new \Zend\Http\Headers();
        $headers->addHeaders(array(
            'Content-Disposition' => 'attachment; filename="' ."CAP_link_crawl_result_$job_id.csv".'"',
            'Content-Type' => 'application/csv'
        ));
        $response->setHeaders($headers);
        
        return $response;
    }
}
