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

use Moose\Context\Context;
use Moose\Context\MooseConfig;
use Moose\Seed\DormantSeed;
use Moose\Util\CmnCnst;
use Moose\ViewModel\Message;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponse;
use Moose\Web\RestResponseInterface;
use Throwable;

/**
 * Servlet for automated testing. Runs the seeds passed to this servlet.
 * Works only in testing mode.
 *
 * @author madgaksha
 */
class SeedServlet extends AbstractRestServlet {
    
    protected function restPost(RestResponseInterface $response, HttpRequestInterface $request) {
        $json = \json_decode($request->getContent(), true);
        if (Context::getInstance()->getConfiguration()->isNotEnvironment(MooseConfig::ENVIRONMENT_TESTING)) {
            $response->setError(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal',
                            'rest.mode.not.testing', $this->getTranslator()));
            return;
        }
        if (json_last_error() !== JSON_ERROR_NONE) {
            $response->setError(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal',
                            'rest.no.json', $this->getTranslator()));
            return;
        }
        if (!is_array($json)) {
            $response->setError(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal',
                            'rest.no.json.object', $this->getTranslator()));
            return;
        }
        try {
            DormantSeed::grow($json);
            $response->setKey('success', 'true');
        }
        catch (Throwable $e) {
            $msg = $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . '\n' . $e->getTraceAsString();
            $response->setError(HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                    Message::dangerI18n('servlet.seed.failure', $msg,
                            $this->getTranslator()));
        }
    }
    
    public static function getRoutingPath(): string {
        return CmnCnst::SERVLET_SEED;
    }

}
