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
 * Description of MooseDatabaseOptions
 *
 * @author madgaksha
 */
class MooseDatabaseOptions extends AbstractOptions {
    const TYPE_MYSQL = 'mysql';
    const TYPE_ORACLE = 'oracle';
    const TYPE_SLQSERVER = 'slqserver';
    const TYPE_SQLITE = 'sqlite';
    const TYPE_POSTGRESQL = 'postgres';
    const TYPE_ARRAY = [
        self::TYPE_MYSQL,
        self::TYPE_ORACLE,
        self::TYPE_POSTGRESQL,
        self::TYPE_POSTGRESQL,
        self::TYPE_SLQSERVER,
        self::TYPE_SQLITE
    ];
    
    public function __construct(array $options) {
        parent::__construct($options);
        $this->setCollation($this->options['collation'] ?? 'utf8_general_ci');
        $this->setDatabaseName($this::notNull('name', 'Option database name'));
        $this->setDatabaseType($this->options['driver'] ?? 'mysql');
        $this->setEncoding($this->options['encoding'] ?? 'utf8');
        $this->setHost($this::notNull('host', 'Option host'));
        $this->setPassword($this::notNull('pass', 'Option password'));
        $this->setPort(\intval($this->options['port'] ?? 3306));
        $this->setUsername($this::notNull('user', 'Option username'));        
    }
    
    public function setHost(string $host) : MooseDatabaseOptions {
        $this->options['host'] = $host;
        return $this;
    }
    
    public function setPort(int $port) : MooseDatabaseOptions {
        if ($port < 1 || $port > 65535) {
            throw new \LogicException("Invalid port $port, must be in [1-65535]");
        }
        $this->options['port'] = $port;
        return $this;
    }
    
    public function setDatabaseName(string $databaseName) : MooseDatabaseOptions {
        $this->options['name'] = $databaseName;
        return $this;
    }
    
    public function setUsername(string $username) : MooseDatabaseOptions {
        $this->options['user'] = $username;
        return $this;
    }
    
    public function setPassword(string $password) : MooseDatabaseOptions {
        $this->options['pass'] = $password;
        return $this;
    }
    
    public function setCollation(string $collation) : MooseDatabaseOptions {
        $this->options['collation'] = $collation;
        return $this;
    }
    
    public function setEncoding(string $encoding) : MooseDatabaseOptions {
        $this->options['charset'] = $encoding;
        return $this;
    }
    
    public function setDatabaseType(string $databaseType) : MooseDatabaseOptions {
        if (!\in_array($databaseType, self::TYPE_ARRAY)) {
            throw new \LogicException("Unknown database type $databaseType");
        }
        $this->options['driver'] = $databaseType;
        return $this;
    }
    
    public function getHost() : string {
        return $this->options['host'];
    }
    
    public function getPort() : int {
        return $this->options['port'];
    }
    
    public function getDatabaseName() : string {
        return $this->options['name'];
    }
    
    public function getUsername() : string {
        return $this->options['user'];
    }
    
    public function getPassword() : string {
        return $this->options['pass'];
    }
    
    public function getCollation() : string {
        return $this->options['collation'];
    }
    
    public function getEncoding() : string {
        return $this->options['charset'];
    }
    
    public function getDatabaseType() : string {
        return $this->options['driver'];
    }
    
    public function getPdoDriver() : string {
        $type = $this->getDatabaseType();
        switch ($type) {
            case self::TYPE_MYSQL:
                return 'pdo_mysql';
            case self::TYPE_ORACLE:
                return 'pdo_oci8';
            case self::TYPE_SQLITE:
                return 'pdo_sqlite';
            case self::TYPE_SLQSERVER:
                return 'pdo_sqlsrv';
            case self::TYPE_POSTGRESQL:
                return 'pdo_pgsql';
            default:
                throw new \LogicException("Unknown database type $type");
        }
    }
    
    public function getBasicDriver() : string {
        $type = $this->getDatabaseType();
        switch ($type) {
            case self::TYPE_MYSQL:
                return 'mysql';
            case self::TYPE_ORACLE:
                return 'oracle';
            case self::TYPE_SQLITE:
                return 'sqlite';
            case self::TYPE_SLQSERVER:
                return 'sqlsrv';
            case self::TYPE_POSTGRESQL:
                return 'pgsql';
            default:
                throw new \LogicException("Unknown database type $type");
        }
    }

    public function isDatabaseType(string $type): bool {
        return $this->getDatabaseType() === $type;
    }
}