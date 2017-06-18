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

namespace Moose\ViewModel;

use Moose\Context\Context;
use Moose\Web\HttpResponse;
use Moose\Web\RequestException;

/**
 * Base class for encapsulating a REST API request object.
 *
 * @author madgaksha
 */
class ARestServletModel {
    /** @var Context */
    private $context;
    
    public final function injectContext(Context $context) {
        $this->context = $context;
        return $this;
    }
    
    public function getContext(): Context {
        return $this->context;
    }
    
    /**
     * @param string $param
     * @return int
     * @throws RequestException
     */
    protected final function paramInt(string $param = null, int $default = null) : int {
        if (\ctype_digit($param)) {
            return \intval($param);
        }
        if ($default !== null) {
            return $default;
        }
        throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                Message::warningI18n('illegal.request', 'servlet.number.required',
                    $this->getContext()->getSessionHandler()->getTranslator(),
                    [value => $param])
        );
    }
    
    /**
     * @param string $param
     * @param int $default
     * @return int
     */
    protected final function paramNullableInt(string $param = null, int $default = null) {
        if ($param === null || $param === '' || $param === 'null') {
            return null;
        }
        return $this->paramInt($param, $default);
    }
    
    protected final function paramBool(string $param = null, bool $default = null) : bool {
        if ($param === 'true' || $param === '1') {
            return true;
        }
        if ($param === 'false' || $param === '0') {
            return false;
        }
        if ($default !== null) {
            return $default;
        }
        throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                Message::warningI18n('illegal.request', 'servlet.bool.required',
                    $this->getContext()->getSessionHandler()->getTranslator(),
                    [value => $param])
        );
    }
}