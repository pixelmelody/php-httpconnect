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

namespace pixelmelody\httpconnect;

use pixelmelody\httpconnect\contracts\RequesterAbstract;
use pixelmelody\httpconnect\parts\ResponseHandler;

/**
 * HTTPRequester
 * 
 * @todo build support for asynchronous requests
 *
 * @author PixelMelody <lab@pixelmelody.com>
 * @copyright 2015 Pixelmelody PT Portugal
 */
class HttpRequester extends RequesterAbstract {

    CONST HTTP_POST = "POST", HTTP_GET = "GET";

    /**
     *
     * @var ResponseHandler
     */
    private $rh;

    /**
     * Constructor
     */
    function __construct($url, $method = self::HTTP_POST) {
        
        parent::__construct($url, $method);

        /* inicializar um ResponseHandler de sem conexão */
        $this->rh = new ResponseHandler(null, null);
    }

    /**
     * Returns the content after request.
     * @param type $asynchronous
     * @return string|false body content on sucess FALSE on error
     */
    public function request($asynchronous = false) {
        $this->rh = parent::request($asynchronous);
        return $this->rh->getBody();
    }

    /**
     * 
     * @return ResponseHandler
     */
    public function getResponseHandler() {
        return $this->rh;
    }

}
