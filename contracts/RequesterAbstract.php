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

namespace pixelmelody\httpconnect\contracts;

use pixelmelody\httpconnect\HttpCredentials;
use pixelmelody\httpconnect\parts\CurlOptions;
use pixelmelody\httpconnect\parts\HeaderArray;
use pixelmelody\httpconnect\parts\RequestHandler;
use pixelmelody\httpconnect\parts\ResponseHandler;
use pixelmelody\httpconnect\parts\RequestCache;

/**
 * RequesterAbstract
 *
 * @author PixelMelody <lab@pixelmelody.com>
 * @copyright 2015 Pixelmelody PT Portugal
 */
abstract class RequesterAbstract {

    /**
     * HTTP Request Method name
     * @var string 
     */
    private $method;

    /**
     * CURL Options Handler
     * @var CurlOptions 
     */
    private $options;

    /**
     * Header handler array based
     * @var HeaderArray 
     */
    private $header;

    /**
     * Post fields array 
     * @var array
     */
    private $postfields = [];

    /**
     * Request cache manager for the requester
     * @var RequestCache
     */
    private $cache;

    /**
     * Constructor
     * 
     * @param string $url 
     * @param string $method HTTP method name
     */
    function __construct($url, $method) {

        $const = "static::HTTP_" . strtoupper($method);
        $this->method = defined($const) ? constant($const) : false;
        $this->header = new HeaderArray();
        $this->options = new CurlOptions();
        $this->cache = false;

        /* add url to curl options */
        $this->options->url = filter_var($url, FILTER_SANITIZE_URL);
    }

    public function addHeader($name, $content) {
        $this->header[$name] = $content;
        return $this;
    }

    /**
     * Return the request cache manager
     * @return RequestCache
     */
    public function getCache() {
        return $this->cache;
    }

    /**
     * Return an array to inject directly to curl_setopt PHP function
     * @return array
     */
    public function getCurlHeaderArray() {
        return $this->header->toCurlArray();
    }
    
    /**
     * Return the content for a defined curl option.
     * @param string $name CURL Property without CURLOPT_ PHP prefix
     * @return string
     */
    public function getCurlOption($name) {
        return $this->options->$name;
    }

    /**
     * Return an array to inject directly to curl_setopt_array PHP function
     * @return array
     */
    public function getCurlOptionsArray() {
        return $this->options->toArray();
    }

    /**
     * Return the active HTTP Method name 
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Return a string to inject directly to curl option POSTFIELDS 
     * @return string
     */
    public function getPostFields() {
        return http_build_query($this->postfields);
    }

    /**
     * Returns the active <i>URL</i>.
     * @return string|FALSE present URL or FALSE not defined
     */
    public function getUrl() {
        return $this->options->url;
    }

    /**
     * Reset all properties for the active requester
     * 
     * All properties means, all Header claims, post fields are cleaned and all
     * CURL options are reset to default.
     * 
     * @param string|null $method method name or null to keep active method
     * @return RequesterAbstract
     */
    public function reset($method = null) {
        $this->header->flush();
        $this->postfields = [];
        return $this->resetOptions($method);
    }

    /**
     * Reset only header properties for this active requester
     * 
     * @return RequesterAbstract
     */
    public function resetHeader() {
        $this->header->flush();
        return $this;
    }

    /**
     * Reset CURL options for this active requester.
     * 
     * @param string|null $method method name or null to keep active method
     * @return RequesterAbstract
     */
    public function resetOptions($method = null) {

        /* buffer some base properties */
        $timeout = $this->options->connecttimeout;
        $agent = $this->options->useragent;
        /* clean it and set default values */
        $this->options->reset();
        /* re set the buffered values */
        $this->setTimeout($timeout)->setUserAgent($agent);

        if ($method !== null) {
            return $this->setMethod($method);
        }
        return $this;
    }

    /**
     * Reset all POST Fields
     * 
     * @return RequesterAbstract
     */
    public function resetPostFields() {
        $this->postfields = [];
        return $this;
    }

    /**
     * Execute the request based on the requester.
     * 
     * @param type $asynchronous
     * @return ResponseHandler
     */
    public function request($asynchronous = false) {
        $handler = new RequestHandler($this);
        /* will return ResponseHandler */
        return $handler->request();
    }

    /**
     * Define um Authentication Header.
     * 
     * @param string $id nome que complementa a password das credenciais
     * @param string $pass password que complementa o nome das credenciais 
     * @param int $type tipo/formato da autenticação
     * @return self
     */
    public function setAuthentication($id, $pass = "", $type = CURLAUTH_BASIC) {
        if ($id === null) {
            $this->options->httpauth = null;
            $this->options->userpwd = null;
        } else {
            $this->options->httpauth = $type;
            $this->options->userpwd = $id . ':' . $pass;
        }
        return $this;
    }

    /**
     * Define um Authentication Header baseado no objecto HttpCredentials.
     * 
     * @see self::setAuthentication($id, $pass, $type)
     * @param HttpCredentials $credentials Objecto que implementa credenciais
     * @param int $type tipo/formato da autenticação
     * @return self
     */
    public function setAuthCredentials(HttpCredentials $credentials, $type = CURLAUTH_BASIC) {
        if (is_object($credentials)) {
            $this->options->httpauth = $type;
            $this->options->userpwd = (string) $credentials;
        } else {
            $this->options->httpauth = null;
            $this->options->userpwd = null;
        }
        return $this;
    }

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
     * @param int $lifetime cache time live in number of seconds
     * @param string $key for signing the content
     * @param int|array $code when http code do caching
     * @param string $filepath full path for temporary file
     * @return self
     */
    public function setCache($lifetime = 120, $key = null, $code = 200, $filepath = null) {

        $path = false;
        if ($filepath === null) {
            $path = session_save_path();
            if (empty($path) || !is_dir($path) || !is_writable($path)) {
                $path = sys_get_temp_dir();
            }
        } elseif (is_dir($filepath) && is_writable($filepath)) {
            $path = $filepath;
        }

        if (is_dir($path) && is_writable($path)) {
            $hash = md5($this->getUrl());
            $filepath = $path . DIRECTORY_SEPARATOR . "{$hash}.tmp";
        }

        if ($key === true) {
            $key = md5($this->getUrl());
        }

        $this->cache = new RequestCache($filepath, $lifetime, $key, $code);
        return $this;
    }

    /**
     * Sets an individual CURL Option.
     * 
     * @param string $name CURL Property without CURLOPT_ PHP prefix
     * @param mixed $value content for the property
     * @return self
     */
    public function setCurlOption($name, $value) {
        $this->options->$name = $value;
        return $this;
    }

    /**
     * Add an array to the header
     * 
     * @param string $array of key and values to be added
     * @return self
     */
    public function setHeader($array) {
        $this->header->merge($array);
        return $this;
    }

    /**
     * Sets a valid HTTP Method according implementation
     * 
     * @param string $method method name
     * @return self
     */
    public function setMethod($method) {
        $const = "static::HTTP_" . strtoupper($method);
        if (defined($const)) {
            $this->method = constant($const);
        }
        return $this;
    }

    /**
     * Sets or eliminates a POST field by is name.
     * 
     * @param string $name POST Field name
     * @param string|null $content content for the field NULL to delete it 
     * @return self
     */
    public function setPostField($name, $content) {
        if ($content === null && isset($this->postfields[$name])) {
            unset($this->postfields[$name]);
        } else {
            $this->postfields[$name] = $content;
        }
        return $this;
    }

    /**
     * Sets the <i>TIMEOUT</i> for the initial connection.
     * 
     * This is one of the main properties that the mecanism must preserve.
     * 
     * @link http://curl.haxx.se/libcurl/c/CURLOPT_CONNECTTIMEOUT.html 
     * @param int $seconds number of seconds
     * @return self
     */
    public function setTimeout($seconds) {
        $this->options->connecttimeout = $seconds;
        return $this;
    }

    /**
     * Sets the <i>USERAGENT</i>.
     * 
     * This is one of the main properties that the mecanism must preserve.
     * 
     * @link http://curl.haxx.se/libcurl/c/CURLOPT_USERAGENT.html
     * @param string $useragent name for it
     * @return self
     */
    public function setUserAgent($useragent) {
        $this->options->useragent = $useragent;
        return $this;
    }

    /**
     * Canonical string representation of the requester.
     * 
     * @return string [http method]{url]
     */
    public function __toString() {
        $m = $this->getMethod();
        $u = $this->options->url;
        return ($m === false ? '[undefined]' : $m ) . " url:" . ($u === false ? '[undefined]' : $u);
    }

}
