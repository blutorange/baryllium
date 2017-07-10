<?php

/* The 3-Clause BSD License
 * 
 * SPDX short identifier: BSD-3-Clause
 *
 * Note: This license has also been called the "New BSD License" or "Modified
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

namespace Moose\Servlet;

use Moose\Util\CmnCnst;
use Moose\Util\UiUtil;
use Moose\ViewModel\Message;
use Moose\Web\HttpResponse;
use Moose\Web\RestRequestInterface;
use Moose\Web\RestResponseInterface;
use const MB_CASE_TITLE;

/**
 * Description of AbstractEntityServlet
 *
 * @author madgaksha
 */
abstract class AbstractEntityServlet extends AbstractRestServlet {
    /**
     * @param string $class
     * @param array $requiredAttributes
     * @return object[]
     */
    protected function getEntities(string $class = null, array $requiredAttributes = null) {
        $json = $this->getRestRequest()->getJson();
        $entityOrArray = $json->entity ?? [];
        return $this->getObjects($entityOrArray, $class, $requiredAttributes);
    }
    
    /**
     * @param string $class
     * @param array $requiredAttributes
     * @return mixed
     */
    protected function getExactlyOneEntity(string $class = null, array $requiredAttributes = null) {
        $json = $this->getRestRequest()->getJson();
        $objectOrArrayJson = $json->entity ?? [];
        return $this->getExactlyOneObject($objectOrArrayJson, $class, $requiredAttributes);
    }
       
    protected final function restGet(RestResponseInterface $response, RestRequestInterface $request) {
        $action = $request->getQueryParam(CmnCnst::URL_PARAM_ACTION);
        $this->processEntityRequest($response, $request, $action, 'get');
    }
    
    protected final function restPatch(RestResponseInterface $response, RestRequestInterface $request) {
        $this->processEntityRequest($response, $request, $this->getAction(), 'patch');
    }
    
    protected final function restPost(RestResponseInterface $response, RestRequestInterface $request) {
        $this->processEntityRequest($response, $request, $this->getAction(), 'post');
    }
    
    protected final function restDelete(RestResponseInterface $response, RestRequestInterface $request) {
        $this->processEntityRequest($response, $request, $this->getAction(), 'delete');
    }
    
    protected final function restHead(RestResponseInterface $response, RestRequestInterface $request) {
        $this->processEntityRequest($response, $request, $this->getAction(), 'head');
    }

    private function processEntityRequest(RestResponseInterface $response,
            RestRequestInterface $request, string $action = null,
            string $method = 'GET') {
        $method = $method . UiUtil::firstToUpcase($action ?? '');
        if (!method_exists($this, $method)) {
            $response->setError(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n(
                            'error.validation',
                            'servlet.illegal.action',
                            $this->getTranslator(),
                            ['action' => $action]
                    )
            );    
            return;
        }
        $this->$method($response, $request);
    }
        
    protected function getAction() {
        return $this->getRestRequest()->getJson()->action ?? $this->getRestRequest()->getQueryParam('action') ?? null;
    }
}