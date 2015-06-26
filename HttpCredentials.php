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

/**
 * HttpCredentials para autenticação por HTTP.
 * 
 * No contexto de uma transação <i>HTTP</i> a autenticação é um método validação
 * para cada <i>HTTP User Agent</i> com o correspondente nome de utilizador e 
 * sua palavra passe, a cada <i>request</i>.
 * 
 * No ambito da biblioteca <i>\pixelmelody\httpconnect</i> esta classe 
 * implementa o componente que gere umas credenciais <i>HTTP</i>.
 * 
 * Permite a utilização de vários nomes comuns para ambos os campos, onde a 
 * tabela seguinte indica as várias possibilidades:
 * <code>
 *      nome de utilizador  = user, username ou id
 *      palavra chave       = pass, password ou secret 
 * 
 *      exemplo:
 *      $credentials = new HttpCredentials('jon', 'pass');
 *      echo $credentials->user;        // result: jon
 *      echo $credentials->username;    // result: jon
 *      echo $credentials->id;          // result: jon
 * </code>
 * 
 * O encapsolamento garante a definição dos campos apenas no momento do 
 * instanciamento e estratégicamente é permitido o tratamento do objeto como 
 * uma string para obter o conteúdo para o header http.
 * <code>
 *      $credentials = new HttpCredentials('jon', 'pass');
 *      echo $credentials;      // result: jon:pass
 * </code>
 *
 *
 * @author PixelMelody <lab@pixelmelody.com>
 * @copyright 2015 Pixelmelody PT Portugal
 */
class HttpCredentials {

    /**
     * nome do utilizador para a autenticação
     * @var string 
     */
    private $username;

    /**
     * palavra chave para a autenticação
     * @var type 
     */
    private $password;

    /**
     * Construtor que define para o objeto o nome de utilizador e a sua chave.
     * 
     * @param string $username nome do utilizador
     * @param string $password palavra chave correspondente
     */
    function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Obter o conteúdo de um dos campos a partir da sua chave.
     * 
     * Permite a utilização de vários nomes comuns para ambos os campos, onde a 
     * tabela seguinte indica as várias possibilidades:
     * <code>
     *      nome de utilizador  = user, username ou id
     *      palavra chave       = pass, password ou secret 
     * 
     *      exemplo:
     *      $credentials = new HttpCredentials('jon', 'pass');
     *      echo $credentials->user;        // result: jon
     *      echo $credentials->username;    // result: jon
     *      echo $credentials->id;          // result: jon
     * </code>
     * 
     * @param string $name nome do campo a retornar o valor
     * @return string|boolean conteúdo do campo FALSE se inexistente
     */
    public function __get($name) {
        $k = strtolower(trim($name));
        if ($k === 'user' || $k === 'username' || $k === 'id') {
            return $this->username;
        }
        if ($k === 'pass' || $k === 'password' || $k === 'secret') {
            return $this->password;
        }
        return false;
    }

    /**
     * Retorna o texto modelo dentro do protocolo HTTP com os dois campos
     * @return string
     */
    public function __toString() {
        return $this->username . ':' . $this->password;
    }

}
