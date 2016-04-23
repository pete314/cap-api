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
            
            if($data_type == 'json'){
                $result_rows = $crawl_job_result_model->getCrawledLinksByJobId($job_id);
                $result = ['total_count' => $result_rows->count(), 'link_data' => []];
                foreach($result_rows as $row){
                    $result['link_data'][] = $row + ['gmt' => $row['created']->toDateTime()->format('D, d M Y H:i:s T')];

                }
                $this->generateResponse($response, 200, ['success' => true, 'data' => $result, 
                    'errors' => null]);
            }else if($data_type == 'csv'){
                $result_rows = $crawl_job_result_model->getCrawledLinksByJobId($job_id);
                //Will be a stream
                return $this->prepareCsvDownload($result_rows, $data_type, $job_id);
            }else{
                return $this->prepareContentDownload($job_id);
            }
        }else{
            $this->generateResponse($response, 404, ['success' => false, 'data' => [], 
                'errors' => 'User or job not found']);
        }
        return $response;
    }
    
    /**
     * Preapre compressed donwload, currently zip
     * 
     * @param String $job_id
     * @return 
     */
    protected function prepareContentDownload($job_id){
        $crawl_job_result_model = new \Crawl\Model\CrawlResultModel();
        $result_rows = $crawl_job_result_model->getCrawledConentByJobId($job_id);
        
        if($result_rows->count() > 0){
            //Create a temp file to hold files in
            $tmpZipPath = sprintf('data/tmp/%s.zip', $job_id);
            touch($tmpZipPath);
            
            $zip = new \ZipArchive();
            if($zip->open($tmpZipPath, \ZipArchive::CREATE)){
                foreach($result_rows as $row){
                    $zip->addFromString($row['id'] . '.html', $row['content']);
                }
                $zip->close();
                
                //Move from disk to stream and delete file
                $outFP = $this->readFileIntoStream($tmpZipPath);
                return $this->generateStreamResponse($outFP, $job_id, 'zip');
            }else{
                $zip->close();
            }
        }
        
        //there is nothing to reurn
        return (new \Zend\Http\Response\Stream())
                ->setStatusCode(404)
                ->setHeaders((new \Zend\Http\Headers())->addHeaders(['Content-Type' => 'application/json']))
                ->setContent(json_encode(['success' => false, 'data' => [], 'errors' => 'Content not found']));
    }
    
    /**
     * ReadFileContentIntoStream and remove file after
     * @param string $fileName
     * @return filepointer
     */
    protected function readFileIntoStream($fileName){
        $stream = fopen('php://output','w');
        
        $filePointerIn =fopen($fileName, "rb");
        while (!feof($filePointerIn)) {
            fwrite($stream, fread($filePointerIn, 8192));
            ob_flush();
        }
        
        fclose($filePointerIn);
        unlink($fileName);
        
        fclose($stream);
        return $stream;
    }
    
    /**
     * Prepare csv Link crawl results
     * @param Cassandra\Rows $result_rows
     * @param string $job_id
     * @return Http\Response
     */
    protected function prepareCsvDownload(&$result_rows, $job_id){
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
    protected function generateStreamResponse($stream, $job_id, $extension = 'csv'){
        $response = new \Zend\Http\Response\Stream();
        $response->setStream($stream);
        $response->setStatusCode(200);
        $response->setStreamName(sprintf("CAP_link_crawl_result_%s.%s", $job_id, $extension));
        
        $headers = new \Zend\Http\Headers();
        $headers->addHeaders(array(
            'Content-Disposition' => 'attachment; filename="' .sprintf("CAP_link_crawl_result_%s.%s", $job_id, $extension).'"',
            'Content-Type' => 'application/csv'
        ));
        $response->setHeaders($headers);
        
        return $response;
    }
}
