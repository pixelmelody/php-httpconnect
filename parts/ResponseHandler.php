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

/**
 * ResponseHandler
 *
 * @author PixelMelody <lab@pixelmelody.com>
 * @copyright 2015 Pixelmelody PT Portugal
 */
class ResponseHandler {

    private $header;
    private $body;
    private $code;
    private $attempts;

    /**
     * Constructor
     */
    function __construct($curlhandler, $response, $attempts = 0) {

        $this->attempts = intval($attempts);

        if (is_resource($curlhandler)) {
            $size = curl_getinfo($curlhandler, CURLINFO_HEADER_SIZE);
            $this->code = intval(curl_getinfo($curlhandler, CURLINFO_HTTP_CODE));
            $this->header = $size > 0 ? substr($response, 0, $size) : false;
            $this->body = is_string($response) ? substr($response, $size) : false;
        } else if (is_array($response)) {
            $this->code = 417; // <- Expectation Failed
            if (isset($response['header'])) {
                $this->header = $response['header'];
                $p = strpos($this->header, "\r\n");
                if ($p !== false) {
                    $parts = explode(" ", substr($this->header, 0, $p));
                    if (is_array($parts) && isset($parts[1])) {
                        $this->code = intval($parts[1]);
                    }
                }
            }
            if (isset($response['body'])) {
                $this->body = $response['body'];
            }
        }
    }

    /**
     * Return all header properties and values on a array 
     * @return array response headers by request
     */
    public function getHeaderArray() {

        if (is_array($this->header)) {
            return end($this->header);
        }

        $headers = [];
        if ($this->header === false) {
            return $headers;
        }

        /* Split the string on every "double" new line */
        $arrRequests = explode("\r\n\r\n", $this->header);

        // Loop of response headers. The "count() -1" is to 
        //avoid an empty row for the extra line break before the body of the response.
        for ($index = 0; $index < count($arrRequests) - 1; $index++) {
            foreach (explode("\r\n", $arrRequests[$index]) as $i => $line) {
                if ($i === 0) {
                    $headers[$index]['http_code'] = $line;
                } else {
                    list ($key, $value) = explode(': ', $line);
                    $headers[$index][strtoupper($key)] = $value;
                }
            }
        }
        $this->header = $headers;
        return end($headers); // <= se HTTP/1.1 100 returns the second one
    }

    /**
     * 
     * @return type
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * 
     * @param type $assoc
     * @return type
     */
    public function getBodyFetch($assoc = false) {
        if (is_string($this->body)) {
            $ctype = $this->getHeaderProperty('content-type');
            if (stripos($ctype, 'application/json') !== false) {
                return json_decode($this->body, $assoc);
            }
        }
        return $this->body;
    }

    /**
     * 
     * @param type $name
     * @return boolean
     */
    public function getHeaderProperty($name) {
        $header = $this->getHeaderArray();
        if (is_array($header) && isset($header[strtoupper($name)])) {
            return $header[strtoupper($name)];
        }
        return false;
    }

    /**
     * 
     * @return type
     */
    public function getHttpCode() {
        return $this->code;
    }

    /**
     * 
     * @return type
     */
    public function __toString() {
        return json_encode([
            'header' => $this->header,
            'body' => $this->body === false ? "" : $this->body
        ]);
    }

}
