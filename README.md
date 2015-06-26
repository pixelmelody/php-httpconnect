# Pixelmelody PHP Http Connection Wrapper 

This repository contains a HTTP Connector Wrapper for PHP implementations. A client that
makes it easy to send HTTP request based on a procedual and simplistic way. Middleware system
based on PHP CURL allowing you to write an environment and transport agnostic code.

## PHP PSR-4 Namespace
```bash
pixelmelody\httpconnect
```

## Installation

With composer to manage your dependencies:
```bash
    composer require pixelmelody/httpconnect
```

With composer installed you can also reference this repository:
```bash
    ...
    "repositories": [{
            "type": "vcs",
            "url": "https://github.com/pixelmelody/php-httpconnect"
        }],...
```

## Some Features

* Easy integration on any PHP Project
* Abstraction layer for PHP CURL Implementation
* RESTful Requester with specific mecanism
* Integrates a cache for requests that not need constant content update
* Integrates an easy HTTP Credentials manager for HTTP AUTH
* Integrates a Response Handler to better deal with requets results

## Future features

* support for asynchronous requests
* better code documentation

## PHP Example get the public tweets directly from Twitter Api v1.1
```php
<?php    
    $requester = new RestRequester('https://api.twitter.com/oauth2/token');
    $requester->setUserAgent('example.com')
        ->setCache(120)
        ->setPostField('grant_type', 'client_credentials')
        ->setAuthentication('client_apikey', 'client_apisecret');

    $response = $requester->request();
    if ($response) {
        echo "SUCESS: ", $requester->getResponseHandler()->getHttpCode(), "<br>";
        var_dump($requester->getResponseHandler()->getBodyFetch(true));
    } else {
        echo "ERROR:", $requester->getResponseHandler()->getHttpCode();
    }
?>
```


## Important
> **Please always upgrade to latest stable version from master branch and 
beaware that on __dev__ branch changes will ocurr. This is an work in progress **


## Prerequisites

   - PHP 5.4 or above
   - Composer is not required however you will not have a direct implementation.


## Reporting Potential Security Issues

If you have encountered a potential security vulnerability in this library, please
report it at [pixelmelody.com contact form](http://www.pixelmelody.com/#contact). 
We will work with you to verify the vulnerability and patch it.

We request that you contact us via the email address to give the project contributors
a chance to resolve the vulnerability and issue a new release prior to any public
exposure. This helps protect users and provides them with a chance to upgrade and/or
update in order to protect their applications.

The merit on finding it will be added to you and we will mention it on the library repository.

## License
    
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at
 
[http://www.apache.org/licenses/LICENSE-2.0](http://www.apache.org/licenses/LICENSE-2.0)
  
Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.