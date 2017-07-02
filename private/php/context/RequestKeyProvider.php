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

namespace Moose\Context;

use Doctrine\DBAL\Types\ProtectedString;
use LogicException;
use Moose\Web\HttpRequest;
use Moose\Web\HttpRequestInterface;

/**
 * Only allows localhost.
 * @author madgaksha
 */
class RequestKeyProvider implements PrivateKeyProviderInterface {
    private $key;
    private $request;
    private function __construct(HttpRequestInterface $request) {
        $this->request = $request;
    }
    public static function fromRequest(HttpRequestInterface $request) : PrivateKeyProviderInterface {
        return new RequestKeyProvider(($request));
    }
    public static function fromGlobals() : PrivateKeyProviderInterface {
        return new RequestKeyProvider((HttpRequest::createFromGlobals()));
    }
    public function fetch(): ProtectedString {
        if ($this->key === null) {
            if (!$this->request->isLocalhost()) {
                throw new LogicException('Security violation: HOST not allowed.');
            }
            $key = $this->request->getParam('pk', null);
            if (empty($key)) {
                throw new LogicException('Security violation: No private key given.');
            }
            $this->key = new ProtectedString($key);
        }
        return $this->key;
    }
}