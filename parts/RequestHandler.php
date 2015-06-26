<?php

/**
 *  Pixelmelody PHP HttpConnect - CURL Wrapper 
 *  
 *  @author      PixelMelody <lab@pixelmelody.com>
 *  @copyright   2015 Pixelmelody PT Portugal
 *  @link        http://www.pixelmelody.com/lab/php-httpconnect
 *  @license     http://www.pixelmelody.com/lab/php-httpconnect
 *  @package     httpconnect
 *  
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *  
 *  http://www.apache.org/licenses/LICENSE-2.0
 *  
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace pixelmelody\httpconnect\parts;

use pixelmelody\httpconnect\contracts\RequesterAbstract;

/**
 * Requester object that executes the request.
 * 
 * Build a request based on RequesterAbstract object that instances and run it.
 *
 * @author PixelMelody <lab@pixelmelody.com>
 * @copyright 2015 Pixelmelody PT Portugal
 */
class RequestHandler {

    /**
     * HTTP status codes for which a retry must be attempted
     * retry is currently attempted for Request timeout, Bad Gateway,
     * Service Unavailable and Gateway timeout errors.
     */
    private static $retrycodes = [408, 502, 503, 504];

    /**
     *
     * @var RequesterAbstract
     */
    private $requester;

    /**
     * PHP Curl Handler
     * @var object instance  
     */
    private $ch;

    /**
     * Number of retries for on request that fails.
     * @var int number of consecutive attempts
     */
    private $retry_max_attempts = 2;

    /**
     * Constructor
     */
    function __construct(RequesterAbstract $requester) {

        $this->requester = $requester;
        if (function_exists("curl_init")) {
            $this->ch = curl_init();
        }
        if (!isset($this->ch) || $this->ch === false) {
            throw new Exception("Curl module initialization problem!");
        }
    }

    /**
     * fecha e liberta recursos da instancia ativa
     */
    public function __destruct() {
        if (is_resource($this->ch)) {
            curl_close($this->ch);
        }
    }

    /**
     * 
     * @param type $retry
     * @return ResponseHandler
     */
    public function request($retry = true) {

        $cached = $this->requester->getCache();
        if ($cached) {
            $content = $cached->get();
            if ($content) {
                return $content;
            }
        }
       
        if ($this->requester->getMethod() === 'POST') {
            $fields = $this->requester->getPostFields();
            $this->requester->setCurlOption('POSTFIELDS', $fields);
            if (!empty($fields)) {
                $this->requester->addHeader('Content-Type', 'application/x-www-form-urlencoded;charset=UTF-8');
            }
        }

        $header = $this->requester->getCurlHeaderArray();
        if (count($header) > 0) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
        }

        /* alocar as variáveis CURL do requester */
        curl_setopt_array($this->ch, $this->requester->getCurlOptionsArray());

        curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);

        $retries = 0;
        $code = -1;

        do {
            $response = curl_exec($this->ch);
            $code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        } while ($retry && in_array($code, self::$retrycodes) && ( ++$retries < $this->retry_max_attempts));
        
        $handler = new ResponseHandler($this->ch, $response, $retries);
        if ($cached) {
            $cached->set($handler);
        }
        return $handler;
    }

}
