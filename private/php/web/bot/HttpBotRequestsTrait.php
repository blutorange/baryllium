<?php
declare(strict_types = 1);

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

namespace Moose\Web;

/**
 * Implements some convenience methods required by the HttpBotInterface.
 * @author madgaksha
 */
trait HttpBotRequestsTrait {
    public final function get(string $url, array $data = [], array $headers = [],
                              array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_GET, $data, $headers, $options);
    }

    public final function post(string $url, array $data = [], array $headers = [],
                               array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_POST, $data, $headers, $options);
    }

    public final function delete(string $url, array $data = [], array $headers = [],
                                 array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_DELETE, $data, $headers, $options);
    }

    public final function put(string $url, array $data = [], array $headers = [],
                              array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_PUT, $data, $headers, $options);
    }

    public final function head(string $url, array $data = [], array $headers = [],
                               array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_HEAD, $data, $headers, $options);
    }

    public final function patch(string $url, array $data = [], array $headers = [],
                                array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_PATCH, $data, $headers, $options);
    }

    public final function options(string $url, array $data = [], array $headers = [],
                                  array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_OPTIONS, $data, $headers, $options);
    }

    public final function trace(string $url, array $data = [], array $headers = [],
                                array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_TRACE, $data, $headers, $options);
    }

    public abstract function request(string $url,
            string $method = HttpBotInterface::HTTP_GET, array $data = [],
            array $headers = [], array $options = []) : HttpBotInterface;
}
