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

use DateTime;
use Moose\Controller\AbstractController;
use Moose\Entity\User;
use Moose\Util\UiUtil;
use Moose\ViewModel\Message;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponse;
use Moose\Web\HttpResponseInterface;
use Moose\Web\RequestException;
use Moose\Web\RestRequest;
use Moose\Web\RestRequestInterface;
use Moose\Web\RestResponse;
use Moose\Web\RestResponseInterface;
use ReflectionClass;
use Throwable;
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
    
    /** @var RestRequestInterface */
    private $restRequest;
    
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
        $this->restRequest = new RestRequest($httpRequest);
        $this->performRest($this->restResponse, $this->restRequest, $method);
        $this->restResponse->apply();
    }
    
    /**
     * @return RestResponseInterface
     */
    protected function getRestResponse() : RestResponseInterface {
        return $this->restResponse;
    }
    
    /**
     * @return RestRequestInterface
     */
    protected function getRestRequest() : RestRequestInterface {
        return $this->restRequest;
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
    
    protected function makeAccessDeniedResponse(HttpResponseInterface $httpResponse) {
        $response = new RestResponse($httpResponse);
        $response->setError(HttpResponse::HTTP_FORBIDDEN, Message::dangerI18n('accessdenied.message', 'accessdenied.detail', $this->getTranslator()));
        $response->apply();
    }
    
    protected function makeBadRequestResponse(HttpResponseInterface $httpResponse,
            RequestException $requestException) {
        $response = new RestResponse($httpResponse);
        $messageList = $requestException->getMessageList();
        if (\sizeof($messageList)) {
            $messageList []= Message::dangerI18n('illegalrequest.message',
                    'illegalrequest.detail', $this->getTranslator());
        }
        $response->setError($requestException->getCode(), $messageList[0]);
        $response->apply();
    }
    
    protected function makeLoginResponse(HttpResponseInterface $httpResponse, 
            bool $needsSiteAdmin, bool $needsLocalAdmin) {
        $response = new RestResponse($httpResponse);
        $response->setError(HttpResponse::HTTP_FORBIDDEN, Message::dangerI18n('accessdenied.message', 'accessdenied.detail', $this->getTranslator()));
        $response->apply();
    }
        
    private final function performRest(RestResponseInterface $response, RestRequestInterface $request, string $method) {
        switch (\mb_convert_case($method, MB_CASE_UPPER)) {
            case "GET":
                $this->restGet($response, $request);
                break;
            case "POST":
                $this->restPost($response, $request);
                break;
            case "PUT":
                $this->restPut($response, $request);
                break;
            case "HEAD":
                $this->restHead($response, $request);
                break;
            case "PATCH":
                $this->restPatch($response, $request);
                break;
            case "DELETE":
                $this->restDelete($response, $request);
                break;
            case "OPTIONS":
                $this->restOptions($response, $request);
                break;
            default:
                $this->restUnsupportedMethod();
        }
    }
    
    protected function restPost(RestResponseInterface $response, RestRequestInterface $request) {
        $this->restUnsupportedMethod();
    }

    protected function restGet(RestResponseInterface $response, RestRequestInterface $request) {
        $this->restUnsupportedMethod();
    }

    protected function restPut(RestResponseInterface $response, RestRequestInterface $request) {
        $this->restUnsupportedMethod();
    }

    protected function restDelete(RestResponseInterface $response, RestRequestInterface $request) {
        $this->restUnsupportedMethod();
    }
    
    protected function restPatch(RestResponseInterface $response, RestRequestInterface $request) {
        $this->restUnsupportedMethod();
    }
    
    private final function restOptions(RestResponseInterface $response, RestRequestInterface $request) {
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
               if (\mb_substr($name, 0, 4) === 'rest') {
                   $type = \mb_substr($name, 4);
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
    
    protected function getObjects($objectOrArrayJson, string $class = null, array $requiredAttributes = null) {
        if ($objectOrArrayJson === null) {
            $this->getRestResponse()->setError(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal', 'servlet.object.missing', $this->getTranslator())
            );
            return [];
        }
        if (\is_array($objectOrArrayJson)) {
            $objects = [];
            foreach($objectOrArrayJson as $objectData) {
                $object = $this->mapObject($objectData, $class, $requiredAttributes);
                if ($object === null) {
                    return [];
                }
                $objects[] = $object;
            }
            return $objects;
        }
        else if (\is_object($objectOrArrayJson)) {
            $object = $this->mapObject($objectOrArrayJson, $class, $requiredAttributes);
            return $object !== null ? [$object] : [];
        }
        else {
            $this->getRestResponse()->setError(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal', 'servlet.object.illegal', $this->getTranslator())
            );
        }
    }
    
    protected function getExactlyOneObject($objectOrArrayJson, string $class = null, array $requiredAttributes = null) {
        $objects = $this->getObjects($objectOrArrayJson, $class, $requiredAttributes);
        if (\sizeof($objects) !== 1) {
            throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal', 'servlet.object.count', $this->getTranslator(), [
                        'countExpected' => 1,
                        'countActual' => \sizeof($objects)
                    ])
            );
        }
        return $objects[0];
    }
    
    /**
     * @param $objectData object
     * @param $class string
     * @param $requiredAttributes array
     * @return object of type $class when given, or as specified by the $objectData.
     */
    private function mapObject($objectData, string $class = null, array $requiredAttributes = null) {
        if ($class === null) {
            $class = $objectData->class ?? null;
        }
        if ($class === null || !\class_exists($class)) {
            $this->getRestResponse()->setError(HttpResponse::HTTP_BAD_REQUEST,
                Message::warningI18n('request.illegal', 'servlet.object.class.missing', $this->getTranslator())
            );
           return null;
        }
        $object = new $class();
        try {
            $object->injectContext($this->getContext());
        } catch (\Throwable $doesNotWantContext){}
        $fields = $objectData->fields ?? [];
        if ($requiredAttributes === null) {
            return $this->objectWithAllAttributes($objectData, $object);
        }
        else {
            return $this->objectWithSelectedFields($fields, $object, $requiredAttributes);
        }
    }
    
    private function validateField($fieldValue, $fieldOptions, string $fieldName) : bool {
        $nullable = $fieldOptions['nullable'] ?? false;
        $emptieable = $fieldOptions['emptieable'] ?? true;
        if ((!$nullable || !$emptieable) && $fieldValue === null) {
            $this->getRestResponse()->setError(HttpResponse::HTTP_BAD_REQUEST,
                    Message::dangerI18n('error.validation', 'servlet.object.field.null', $this->getTranslator(), ['fieldName' => $fieldName])
            );
            return false;
        }
        else if (!$emptieable && empty($fieldValue)) {
            $this->getRestResponse()->setError(HttpResponse::HTTP_BAD_REQUEST,
                    Message::dangerI18n('error.validation', 'servlet.object.field.empty', $this->getTranslator(), ['fieldName' => $fieldName])
            );
            return false;
        }
        return true;
    }

    private function objectWithAllAttributes($objectData, $object) {
        foreach (($objectData->fields ?? []) as $fieldName => $fieldValue) {
            $this->setObjectFieldValue($object, $fieldName, $fieldValue);
        }
        return $object;
    }

    private function objectWithSelectedFields($fields, $object, array $requiredAttributes) {
        foreach ($requiredAttributes as $fieldNameOrIndex => $fieldNameOrOptions) {
            if (\is_numeric($fieldNameOrIndex)) {
                $fieldName = $fieldNameOrOptions;
                $fieldOptions = [];
            }
            else {
                $fieldName = $fieldNameOrIndex;
                $fieldOptions = $fieldNameOrOptions;
            }
            $fieldValue = $fields->$fieldName ?? null;
            if (!$this->validateField($fieldValue, $fieldOptions, $fieldName)) {
                throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                        Message::dangerI18n('error.validation',
                                'servlet.object.illegal.field.value',
                                $this->getTranslator())
                );
            }
            $this->setObjectFieldValue($object, $fieldName, $fieldValue);
        }
        return $object;
    }
    
    private function setObjectFieldValue($object, string $fieldName, $fieldValue) {
        $method = "set" . UiUtil::firstToUpcase($fieldName);
        if (!\method_exists($object, $method)) {
            throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                    Message::dangerI18n('error.validation',
                            'servlet.object.no.such.field',
                            $this->getTranslator())
            );
        }
        $object->$method($fieldValue);
    }

    private function getObjectFieldValue($object, string $fieldName) {
        $method = "get" . UiUtil::firstToUpcase($fieldName);
        if (!\method_exists($object, $method)) {
            throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                    Message::dangerI18n('error.validation',
                            'servlet.object.no.such.field',
                            $this->getTranslator())
            );
        }
        return $object->$method();
    }
    
    private function prepareForJson($object) {
        if (\is_object($object)) {
            if ($object instanceof DateTime) {
                return $object->getTimestamp();
            }
            return (string)$object;
        }
        return $object;
    }
    
    /**
     * @param object[] $objects
     * @param string[] $fields
     * @param bool $includeClass When false, omits the <code>class</code> entry for each object.
     * @return array
     */
    protected function mapObjects(array $objects, array $fields, bool $omitClass = null) : array {
        return \array_map(function($object) use ($fields, $omitClass) {
            $fieldValues = [];
            foreach ($fields as $field) {
                $fieldValues[$field] = $this->prepareForJson($this->getObjectFieldValue($object, $field));
            }
            if ($omitClass) {
                return ['fields' => $fieldValues];
            }
            else {
                return [
                    'class' => User::class,
                    'fields' => $fieldValues
                ];
            }
        }, $objects);
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