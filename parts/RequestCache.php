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
 * RequestCache
 * 
 * Implements a file cache for requests. Ideal for request that do not need to
 * refresh content every time, or in situations that hiting some services on
 * every visitor request is not apropriated and mainly not recomended.
 * 
 * It also permits to signing the content to grant autenticity.
 *
 * @author PixelMelody <lab@pixelmelody.com>
 * @copyright 2015 Pixelmelody PT Portugal
 */
class RequestCache {

    /**
     * Cache / Temporary file path
     * @var string
     */
    private $filecache;

    /**
     * Number of seconds that the cache will live
     * @var int 
     */
    private $lifetime;

    /**
     * HTTP CODE de requests para manter cache.
     * @var int|boolean
     */
    private $code;

    /**
     * Key for HMAC Signature
     * @var string
     */
    private $key;

    /**
     * Construtor
     * 
     * Manage a temporary file cache for some request content and sign it. Can
     * be defined the lifetime in number of seconds and when time passed the 
     * cache lifetime will not be considered. 
     * 
     * A <i>key</i> can be supplied all will be used to generate signature or 
     * to verify the content. If <i>key</i> is TRUE then a key based on the URL
     * is generated forcing a signature. 
     * 
     * Also a path can be supplied to a file or to a directory and in result
     * a random file name will be created based on the request URL . If the 
     * supplied file path is TRUE then it will look for the session temporay
     * directory or php temporay directory set in the configuration file.
     * 
     * By default a cache is provided if the request returns the <i>HTTP CODE
     * 200</i>. Nevertheless is possible to define another code where cache must
     * be used or an array of codes. If the parameter <i>$code</i> have the 
     * value of <i>TRUE</i> then cache will be provided on any circumstances
     * also on error codes.
     * 
     * @param string $filepath full path for temporary file
     * @param int $lifetime cache time live in number of seconds
     * @param string $key for signing the content
     * @param int|array $code when http code do caching
     */
    function __construct($filepath, $lifetime = 120, $key = null, $code = 200) {
        $this->filecache = $filepath;
        $this->lifetime = $lifetime;
        $this->code = $code;
        $this->key = $key;
    }

    /**
     * Loads content in the cache file especified in the constructor
     * 
     * @return boolean|ResponseHandler FALSE error or no cache file
     */
    public function get() {
        if (is_file($this->filecache) && is_readable($this->filecache)) {
            /* utilizar se a cache tiver menos de 24 horas */
            if ($this->lifetime === 0 || time() - filemtime($this->filecache) < $this->lifetime) {
                $content = json_decode(file_get_contents($this->filecache), true);
                if (is_array($content) && isset($content['signature'])) {
                    if (isset($this->key) || $this->key !== null) {
                        $sign = $content['signature'];
                        unset($content['signature']);
                        if ($sign === hash_hmac('sha256', json_encode($content), $this->key)) {
                            return new ResponseHandler(null, $content, 1);
                        }
                    }
                } else {
                    return new ResponseHandler(null, $content, 1);
                }
            }
        }
        return false;
    }

    /**
     * Saves a content to the file cache.
     * 
     * IMPORTANT: if do not have permission to save the cache file where you 
     * specify this function will return FALSE.
     * 
     * @param string ResponseHandler to save it content on the cache file
     * @return boolean TRUE content cached FALSE error and not cached
     */
    public function set(ResponseHandler $handler) {
        if (is_writable(pathinfo($this->filecache)['dirname'])) {
            $c = $handler->getHttpCode();
            if ((is_array($this->code) && in_array($c, $this->code)) || $this->code === $c) {
                $content = (string) $handler;
                if (isset($this->key) || $this->key !== null) {
                    $json = json_decode($content, true);
                    if (is_array($json)) {
                        $json['signature'] = hash_hmac('sha256', $content, $this->key);
                        $content = json_encode($json);
                    }
                }
                return file_put_contents($this->filecache, $content, LOCK_EX);
            }
        }
        return false;
    }

}
