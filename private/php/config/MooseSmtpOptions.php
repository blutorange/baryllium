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

/**
 * Options for the SMTP mailer.
 *
 * @author madgaksha
 */
class MooseSmtpOptions extends MooseMailOptions  {

    const ENCRYPTION_SSL = 'ssl';
    const ENCRYPTION_TLS = 'tls';
    
    const KEY_BIND_TO = 'bindto';
    const KEY_CONNECTION_TIMEOUT = 'timeout';
    const KEY_IS_SECURE = 'secure';
    const KEY_IS_PERSISTENT = 'persistent';
    const KEY_PORT = 'port';
    const KEY_HOST = 'host';
    const KEY_USERNAME = 'user';
    const KEY_PASSWORD = 'pass';

    public function __construct(array $options) {
        parent::__construct($options);
        $this->setBindTo(\intval($this->options['bindto'] ?? 0));
        $this->setConnectionTimeout(\intval($this->options['timeout'] ?? 20));
        $this->setIsSecure($this->asBool('secure', 'Option secure'));
        $this->setIsPersistent($this->asBool('persistent', 'Option persistent'));
        $this->setPort(\intval($this->options['port'] ?? ($this->getIsSecure() ? 465 : 25)));
        $this->setHost($this::notNull('host', 'SMTP host'));
        $this->setUsername($this::notNull('user', 'SMTP user'));
        $this->setPassword($this::notNull('pass', 'SMTP password'));
    }
    
    public function getHost() : string {
        return $this->options['host'];
    }
    
    public function setHost(string $host) : MooseSmtpOptions {
        $this->options['host'] = $host;
        return $this;
    }
    
    public function getUsername() :string  {
        return $this->options['user'];
    }
    
    public function setUsername(string $username) : MooseSmtpOptions {
        $this->options['user'] = $username;
        return $this;
    }
    
    public function getPassword() : string {
        return $this->options['pass'];
    }
    
    public function setPassword(string $password) : MooseSmtpOptions {
        $this->options['pass'] = $password;
        return $this;
    }
    
    public function getConnectionTimeout() : int {
        return $this->options['timeout'];
    }
    
    public function setConnectionTimeout(int $connectionTimeout) : MooseSmtpOptions {
        if ($connectionTimeout < 0) {
            throw new \LogicException("Connection timeout must not be negative");
        }
        $this->options['timeout'] = $connectionTimeout;
        return $this;
    }
    
    public function getPort() : int {
        return $this->options['port'];
    }
    
    public function setPort(int $port) : MooseSmtpOptions {
        if ($port < 1 || $port > 65535) {
            throw new \LogicException("Port must be [1,65535]");
        }
        $this->options['port'] = $port;
        return $this;
    }
    
    public function getBindTo() : int {
        return $this->options['bindto'];
    }
    
    public function setBindTo(int $bindTo) : MooseSmtpOptions {
        if ($bindTo < 0) {
            throw new \LogicException("BindTo must not be negative");
        }
        $this->options['bindto'] = $bindTo;
        return $this;
    }
    
    public function getIsPersistent() : bool {
        return $this->options['persistent'];
    }
    
    public function setIsPersistent(bool $isPersistent) : MooseSmtpOptions {
        $this->options['persistent'] = $isPersistent;
        return $this;
    }
    
    /**
     * @return string 'ssl' or 'tls'
     */
    public function getIsSecure() : bool {
        return $this->options['secure'];
    }
       
    public function setIsSecure(bool $isSecure) : MooseSmtpOptions {
        $this->options['secure'] = $isSecure;
        return $this;
    }
}