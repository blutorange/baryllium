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

namespace Moose\Web;

use LogicException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface HttpRequestInterface {
    
    const PARAM_ALL = 0;
    const PARAM_QUERY = 1;
    const PARAM_FORM = 2;
    const PARAM_FILE = 3;
    const PARAM_HEADER = 4;
    const PARAM_COOKIE = 5;
    
    public function getParam(string $key, $defaultValue = null, int $fromWhere = self::PARAM_ALL);
    
    public function getAllParams(int $fromWhere = self::PARAM_ALL);
    
    public function getParamBool(string $key, bool $defaultValue = null, int $fromWhere = self::PARAM_ALL, bool $strict = false);

    public function getParamInt(string $key, int $defaultValue = null, int $fromWhere = self::PARAM_ALL);

    /**
     * @param string $name Name of file(s). Use null to get all files.
     * @return UploadedFile[] All files for the given name. Empty array when there are none.
     */
    public function getFiles(string $name = null) : array;
    
    /**
     * @return string The client IP address.
     */
    public function getClientIp();
    
    /**
     * @return string
     */
    public function getScheme();

    /**
     * @return string The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri();

    /**
     * @return string A normalized query string for the Request
     */
    public function getQueryString();
    
    /**
     * @return bool Whether there is a query.
     */
    public function hasQuery();

    /**
     * @var string The HTTP method used.
     */
    public function getHttpMethod();

    /**
     * @return string|null The format (null if no content type is present)
     */
    public function getContentType();

    public function getCookieOption(string $field, string $key, $defaultValue=null);

    /**
     * Returns the request body content.
     *
     * @param bool $asResource If true, a resource will be returned
     *
     * @return string|resource The request body content or a resource to read the body stream.
     * @throws LogicException When requesting a resource and the PHP version is too old.
     */
    public function getContent($asResource = false);
    
    /**
     * @return string
     */
    public function getHttpHost();
}