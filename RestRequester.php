<?php

/**
 *  Pixelmelody PHP HttConnect - CURL Wrapper 
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

namespace pixelmelody\httpconnect;

/**
 * RestRequester
 * 
 * @todo Parameter and router lack for implementation
 *
 * @author PixelMelody <lab@pixelmelody.com>
 * @copyright 2015 Pixelmelody PT Portugal
 */
class RestRequester extends HttpRequester {

    CONST HTTP_DELETE = 'DELETE', HTTP_HEAD = "HEAD", HTTP_PUT = 'PUT', RAW_POST = 'RAW_POST';

    /**
     * Parameters present in the <i>URL</i>
     * @var array 
     */
    private $params = [];

    /**
     * Constructor
     */
    function __construct($url, $method = self::HTTP_POST) {
        parent::__construct($url, $method);
    }

    public function getParameter($name) {
        if (isset($this->params[$name])) {
            return $this->params;
        }
        return false;
    }

    public function getUri() {
        $uri = parent::getUrl();
        if ($uri !== false) {
            $uri .= $this->getRouter();
            foreach ($this->params as $k => $value) {
                $uri .= $k . "/" . $value;
            }
            return $uri;
        }
        return false;
    }

    public function flush() {
        $this->params = [];
        return $this;
    }

    /**
     * Sets a new parameter.
     * 
     * IMPORTANT: If <i>$value</i> is <i>null</i> parameter will be deleted.
     * 
     * @param string $name
     * @param mixed $value 
     * @return self
     */
    public function setParameter($name, $value = "") {

        $nname = $this->_url_normalization($name);
        if ($value === null) {
            unset($this->params[$nname]);
        } else {
            $this->params[$nname] = $this->_url_normalization($value);
        }
        return $this;
    }

    /**
     * 
     * @param string $context
     * @return self
     */
    public function setRouter($context) {
        $this->router = $this->_url_normalization($context) . "/";
        return $this;
    }

    /**
     * Returns a normalized string to use in <i>URI</i>
     * @param string $string
     * @return string
     */
    protected function _url_normalization($string) {
        $matches = preg_replace(
                '~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'));
        $decoded = html_entity_decode($matches, ENT_QUOTES, 'UTF-8');
        return strtolower(preg_replace(array('~[^0-9a-z]~i', '~[ -]+~'), '', $decoded));
    }

    /**
     * 
     * @return string [http method]{url]/[parameter1]/[value1]/[parameter2)...
     */
    public function __toString() {
        return "RESTFUL-API " . $this->getMethod() . ' url:' . $this->getUrl();
    }

}
