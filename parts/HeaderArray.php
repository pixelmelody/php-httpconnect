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
 * Request header array manager.
 *
 * @author PixelMelody <lab@pixelmelody.com>
 * @copyright 2015 Pixelmelody PT Portugal
 */
class HeaderArray implements \ArrayAccess, \Countable {

    /**
     * Http header cache
     * @var array
     */
    private $elements = [];

    /**
     * Construtor em HttpHeader
     */
    function __construct() {
        
    }

    public function __set($name, $value) {
        $this->elements[$name] = $value;
    }

    public function __get($name) {
        if (isset($this->elements[$name])) {
            return $this->elements[$name];
        }
        return false;
    }

    public function __unset($name) {
        unset($this->elements[$name]);
    }

    public function count($mode = 'COUNT_NORMAL') {
        return count($this->elements);
    }
    
    public function flush(){
        $this->elements = [];
    }

    public function merge($array) {
        $sucess = false;
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                if (!is_array($v)) {
                    $this->elements[$k] = $v;
                    $sucess = true;
                }
            }
        }
        return $sucess;
    }

    public function offsetExists($offset) {
        return isset($this->elements[$offset]);
    }

    public function offsetGet($offset) {
        if (isset($this->elements[$offset])) {
            return $this->elements[$offset];
        }
        return false;
    }

    public function offsetSet($offset, $value) {
        $this->elements[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->elements[$offset]);
    }

    /**
     * Returns an array the <i>PHP Curl</i> way.
     * 
     * @return array
     */
    public function toCurlArray() {
        $ret = [];
        foreach ($this->elements as $k => $v) {
            $ret[] = "$k: $v";
        }
        return $ret;
    }

}
