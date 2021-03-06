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

namespace Moose\Controller;

use Moose\Util\CmnCnst;
use Moose\Util\UiUtil;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponse;
use Moose\Web\HttpResponseInterface;
use Moose\Web\RequestException;
use Nette\Mail\Message;
use const MB_CASE_LOWER;
use function mb_convert_case;


/**
 * Description of BaseController
 *
 * @author madgaksha
 */
abstract class BaseController extends AbstractController {
    /**
     * Renders a template. Automatically adds global messages to be shown as
     * well as the current language and translator. To override with your own
     * messages or locale, simple* add an entry for the key <pre>messages</pre>
     * or <pre>locale</pre> in the data array.
     * @param string $templateName Name of the template to render.
     * @param array $data Additional data to be passed to the template.
     */
    protected function renderTemplate(string $templateName, array $data = null) {
        $this->getResponse()->appendTemplate($templateName, $this->getEngine(), $this->getTranslator(), $this->getLang(), $data);
    }
    
    protected function routeFromSubmitButton(HttpResponseInterface $response, HttpRequestInterface $request) {
        $action = mb_convert_case($request->getParam(CmnCnst::URL_PARAM_SUBMIT_BUTTON, ''), MB_CASE_LOWER);
        $data = $request->getParam(CmnCnst::URL_PARAM_SUBMIT_BUTTON_DATA, null);
        $type = mb_convert_case($request->getHttpMethod(), MB_CASE_LOWER);
        $method = $type . UiUtil::firstToUpcase($action);
        if (!method_exists($this, $method)) {
                throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                        Message::warningI18n('request.illegal',
                                'request.no.submit.action',
                                $this->getTranslator(), [
                                    'action' => $action
                                ]));                            
        }
        $this->$method($response, $request, $data);
    }
}
