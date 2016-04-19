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

class CrawlJobHelper extends AbstractDataHelper {
    
    protected static $validTypes = [
        'job_type' => ['link-crawl', 'content-crawl'],
        'startin_params' => ['site-root', 'start-at-page', 'xpath', 'content-match', 'max-depth'],
        'has_depth' => ['N/A'],
        'recurrance' => ['daily', 'weekly', 'hourly']
    ];
    
    /**
     * Negative validate request data
     * @param array $rawData
     * @return array
     */
    protected static function validateRequestData(&$rawData){
        $result= ['result' => []];
        foreach($rawData as $key => $value){
            switch($key){
                case 'job_type':
                    if(!in_array($value, self::$validTypes[$key])){ 
                        $result['result'][] = self::serializeError($key, sprintf('Invalid value: %s for %s', $value, $key));
                    }
                    break;
                case 'startin_params':
                    if(!is_array($value) || count($value) < 2){
                        $result['result'][] = self::serializeError($key, 'site-root & start-at-page is required for all requests');
                    }else{
                        $res = self::validateStatinPrams($value);
                        if(count($res) > 0){
                            $result['result'][] = $res;
                        }
                    }
                    break;
                case 'has_depth':
                    //not in scope at the moment alway N/A
                    break;
                case 'recurrance':
                    $valueBits = explode(':', $value);
                    if(!is_array($valueBits)
                            || count($valueBits) != 2
                            || !in_array($valueBits[0], self::$validTypes[$key])
                            || !is_numeric($valueBits[1])
                            || ($valueBits[0] == 'daily' && ($valueBits[1] < 0 || $valueBits[1] > 24))
                            || ($valueBits[0] == 'weekly' && ($valueBits[1] < 0 || $valueBits[1] > 7))
                            || ($valueBits[0] == 'hourly' && ($valueBits[1] < 0 || $valueBits[1] > 60))){ 
                        
                        $result['result'][] = self::serializeError($key, sprintf('Invalid value: %s for %s', $value, $key));
                    }
                    break;
                default:
                    $result['result'][] = self::serializeError($key, 'Invalid property');
            }
        }
        
        return $result;
    }
    
    /**
     * Validate startin_params request params (with negative validation!)
     * @param array $params
     * @return array
     */
    protected static function validateStatinPrams(array $params){
        $result =[];
        foreach($params as $param){
            $valueBits = explode(':', $param, 2);
            if(is_array($valueBits) 
                    && count($valueBits) == 2
                    && in_array($valueBits[0], self::$validTypes['startin_params'])
                    && strlen($valueBits[1]) > 0){
                if($valueBits[0] == 'site-root' || $valueBits[0] == 'start-at-page'){
                    $res = \Common\Validators\StaticGeneralValidator::validateValue('Uri', $valueBits);
                    if(isset($res['errors'])){
                        $result[] = $res;
                    }
                }else if($valueBits[0] == 'max-depth'){
                    if(!is_numeric($valueBits[1]) 
                            || $valueBits[1] > 4 || $valueBits[1] < 1){
                        $result[] = self::serializeError($param, 'max-depth has to be between 1 and 4 inclusive'); 
                            }
                }

                //@todo: regex validate Xpath 
            }else{
                $result[] = self::serializeError($param, 'Invalid property');
            }
        }
        return $result;
    }
    
    protected static function serializeError(&$key, $msg){
        return [$key => $msg];
    }
    
    /**
     * Create crawling job
     * @param array $data
     * @param HTTP|Response $response
     * @return HTTP|Response
     */
    public function routeCreateJobRequest($user_id, $data, $response) {
        $validationResult = self::validateRequestData($data);
        
        $user_model = new \User\Model\UserModel();
        $userArr = $user_model->getUser($user_id);
        if(count($validationResult['result']) == 0 && $userArr->count() == 1){
            $craw_job_model = new \Crawl\Model\CrawlJobModelModel();
            $jobId = bin2hex(openssl_random_pseudo_bytes(24)) . time();
            if(1 == $craw_job_model->createJob([
                'job_id' => $jobId,
                'user_id' => $userArr[0]['user_id'],
                'job_type' => $data['job_type'], 
                'startin_params' => implode(',', $data['startin_params']),
                'recurrance' => $data['recurrance']])){
                
                $this->generateResponse($response, 200, 
                                        ['success' => true, 
                                            'data' => ['job_id' => $jobId], 
                                            'errors' => null]);
            }else{
                $this->generateResponse($response, 500, 
                                        ['success' => false, 
                                            'data' => [], 
                                            'errors' => 'Internal error - please try again later']);
            }
        }else{
            $this->generateResponse($response, 400, ['success' => false, 'data' => [], 
                'errors' => count($validationResult['result']) == 0 ? 'User not found' : $validationResult['result']]);
        }
        return $response;
    }
    
    /**
     * Router for get sinlge job report
     * @param string $user_id
     * @param string $job_id
     * @param HTTP\Response $response
     * @return HTTP\Response
     */
    public function routeGetJobReportRequest($user_id, $job_id, $response){
        $craw_job_model = new \Crawl\Model\CrawlJobModelModel();
        $user_model = new \User\Model\UserModel();
        
        $userArr = $user_model->getUser($user_id);
        $crawJobArr = $craw_job_model->getJobById($job_id);
        if($crawJobArr->count() == 1 
                && $userArr->count() == 1
                && $crawJobArr[0]['user_id'] == $userArr[0]['user_id']){
            $this->generateResponse($response, 200, ['success' => true, 'data' => $crawJobArr[0], 
                'errors' => null]);
        }else{
            $this->generateResponse($response, 404, ['success' => false, 'data' => [], 
                'errors' => 'User or job not found']);
        }
        return $response;
    }
    
    
    public function routeGetAllJobReportRequest($user_id, $response){
        $craw_job_model = new \Crawl\Model\CrawlJobModelModel();
        $user_model = new \User\Model\UserModel();
        
        $userArr = $user_model->getUser($user_id);
        $crawJobArr = $craw_job_model->getJobByUserId($user_id);
        if($crawJobArr->count() >= 1 
                && $userArr->count() == 1
                && $crawJobArr[0]['user_id'] == $userArr[0]['user_id']){
            $results = [];
            foreach($crawJobArr as $job){
                $results[$job['job_id']] = $job;
            }
            unset($crawJobArr);
            $this->generateResponse($response, 200, ['success' => true, 'data' => $results, 
                'errors' => null]);
        }else{
            $this->generateResponse($response, 404, ['success' => false, 'data' => [], 
                'errors' => 'User or job not found']);
        }
        return $response;
    }

}
