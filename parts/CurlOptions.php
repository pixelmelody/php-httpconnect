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
 * PHP Curl Options Manager.
 *
 * This component is based in the library <i>PHP Curl</i>, therefor this
 * manager simplifies the code for set ou get any property.
 * 
 * By convention all options name are based on Curl names and gives you the 
 * possibility use properties without the prefix <i>CURLOPT_</i> regardless 
 * the letter case. Example:
 * 
 * <code>
 *       $options = new CurlOptions();
 *       $options->useragent = "teste";
 *       $options->curlopt_useragent = "teste";
 * 
 *       // to delete
 *       $options->useragent = null;
 * </code>
 *
 * @author PixelMelody <lab@pixelmelody.com>
 * @copyright 2015 Pixelmelody PT Portugal
 */
class CurlOptions {

    /**
     * Curl prefix
     */
    CONST PREFIX = "CURLOPT_";

    /**
     * Options cache
     * @var array 
     */
    private $options;

    /**
     * Construtor
     */
    function __construct() {
        $this->options = $this->_default_options_array();
    }

    public function __set($name, $value) {
        $n = stripos($name, self::PREFIX) === 0 ? $name : self::PREFIX . $name;
        $vn = constant(strtoupper($n));
        if (!is_null($vn)) {
            if ($value === null && isset($this->options[$vn])) {
                unset($this->options[$vn]);
            } else {
                $this->options[$vn] = $value;
            }
        }
    }

    public function __get($name) {
        $n = stripos($name, self::PREFIX) === 0 ? $name : self::PREFIX . $name;
        $vn = constant(strtoupper($n));
        if (!is_null($vn) && isset($this->options[$vn])) {
            return $this->options[$vn];
        }
        return false;
    }

    public function reset() {
        $this->options = $this->_default_options_array();
    }

    public function toArray() {
        return $this->options;
    }

    private function _default_options_array() {
        return [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_ENCODING => 'gzip',
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSLVERSION => 1,
            CURLOPT_SSL_CIPHER_LIST => 'TLSv1',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_USERAGENT => str_replace("/", " - ", dirname(__CLASS__))
        ];
    }

}
