<?php

/* Note: This license has also been called the "New BSD License" or "Modified
 * BSD License". See also the 2-clause BSD License.
 * 
 * Copyright 2015 The Moose Team
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Moose\Servlet;

use Moose\Controller\AbstractController;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponse;
use Moose\Web\HttpResponseInterface;
use Moose\Web\RestResponse;
use Moose\Web\RestResponseInterface;
use ReflectionClass;
use Throwable;
use Ui\Message;
use const MB_CASE_UPPER;
use function mb_convert_case;
use function mb_substr;


/**
 * A REST-like servlet with additional methods and a dedicated ServletResponse
 * object.
 * @author madgaksha
 */
abstract class AbstractRestServlet extends AbstractController {

    /** @var RestResponseInterface */
    private $restResponse;
    
    public final function doGet(HttpResponseInterface $httpResponse, HttpRequestInterface $httpRequest) {
        $this->rest($httpResponse, $httpRequest, 'GET');
    }

    public final function doPost(HttpResponseInterface $httpResponse, HttpRequestInterface $httpRequest) {
        $this->rest($httpResponse,$httpRequest, 'POST');
    }
    
    public final function doOther(HttpResponseInterface $httpResponse, HttpRequestInterface $httpRequest, string $method) {
        $this->rest($httpResponse, $httpRequest, $method);
    }
    
    protected function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_WHENPOSSIBLE;
    }

    private final function rest(HttpResponseInterface $httpResponse, HttpRequestInterface $httpRequest, string $method) {
        $this->restResponse = new RestResponse($httpResponse);
        $this->performRest($this->restResponse, $httpRequest, $method);
        $this->restResponse->apply();
    }
    
    /**
     * @return RestResponseInterface
     */
    protected function getRestResponse() : RestResponseInterface {
        return $this->restResponse;
    }

    /** {@inheritDoc} */
    protected function renderUnhandledError(Throwable $e, bool $isProductionEnvironment, bool $isDbError) {
        $suf = " in " . $e->getFile() . " on line " . $e->getLine();
        $short = $isProductionEnvironment ? $isDbError ? 'Database error' : 'Unhandled error' : $e->getMessage() . $suf;
        $details = $isProductionEnvironment ? \get_class($e) : $e->getTraceAsString();
        $message = Message::danger($short, $details);
        $this->restResponse = new RestResponse($this->getResponse());
        $this->restResponse->setError(HttpResponse::HTTP_INTERNAL_SERVER_ERROR, $message);
        $this->restResponse->setStatusCode(HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        $this->restResponse->apply();
    }
    
    private final function performRest(RestResponseInterface $response, HttpRequestInterface $httpRequest, string $method) {
        switch (mb_convert_case($method, MB_CASE_UPPER)) {
            case "GET":
                $this->restGet($response, $httpRequest);
                break;
            case "POST":
                $this->restPost($response, $httpRequest);
                break;
            case "PUT":
                $this->restPut($response, $httpRequest);
                break;
            case "HEAD":
                $this->restHead($response, $httpRequest);
                break;
            case "PATCH":
                $this->restPatch($response, $httpRequest);
                break;
            case "DELETE":
                $this->restDelete($response, $httpRequest);
                break;
            case "OPTIONS":
                $this->restOptions($response, $httpRequest);
                break;
            default:
                $this->restUnsupportedMethod();
        }
    }
    
    protected function restPost(RestResponseInterface $response, HttpRequestInterface $httpRequest) {
        $this->restUnsupportedMethod();
    }

    protected function restGet(RestResponseInterface $response, HttpRequestInterface $httpRequest) {
        $this->restUnsupportedMethod();
    }

    protected function restPut(RestResponseInterface $response, HttpRequestInterface $httpRequest) {
        $this->restUnsupportedMethod();
    }

    protected function restDelete(RestResponseInterface $response, HttpRequestInterface $httpRequest) {
        $this->restUnsupportedMethod();
    }
    
    protected function restPatch(RestResponseInterface $response, HttpRequestInterface $httpRequest) {
        $this->restUnsupportedMethod();
    }
    
    private final function restOptions(RestResponseInterface $response, HttpRequestInterface $httpRequest) {
       $supported = [
           'Get' =>  false,
           'Post' => false,
           'Put' => false,
           'Options' => true,
           'Head' => false,
           'Patch' => false,
           'Delete' => false,
      ];
       $responseArray = ['OPTIONS'];
       $class = \get_class($this);
       $rfl = new ReflectionClass($class);
       foreach ($rfl->getMethods() as $method) {
           if ($method->getDeclaringClass()->getName() === $class) {
               $name = $method->getName();
               if (mb_substr($name, 0, 4) === 'rest') {
                   $type = mb_substr($name, 4);
                   if (\array_key_exists($type, $supported)) {
                       \array_push($responseArray, mb_convert_case($type, MB_CASE_UPPER));
                   }
               }
           }
       }
       $response->addHeader('Allow', \implode(',', $responseArray));
       $response->setStatusCode(200);
       $response->setJson($this->getDetailedCapabilties());
    }
    
    private final function restUnsupportedMethod() {
        $this->getResponse()->setStatusCode(HttpResponse::HTTP_METHOD_NOT_ALLOWED);
        $this->getResponse()->addMessage(Message::dangerI18n('rest.method.unsupported.message', 'rest.method.unsupported.details', $this->getTranslator()));
    }
    
    public function getResolvedRoutingPath() : string {
        return $this->getContext()->getServerPath(self::getRoutingPath());
    }

    /**
     * Override this to provide info on the capabilities of this servlet. Used to
     * respond to an OPTIONS request.
     * @return array JSON array.
     */
    protected function getDetailedCapabilties() : array {
        return [];
    }
    
    public abstract static function getRoutingPath() : string;
}