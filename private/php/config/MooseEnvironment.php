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

use InvalidArgumentException;
use LogicException;
use Moose\Log\Logger;
use const MB_CASE_LOWER;
use function mb_convert_case;

/**
 * Description of MooseEnvironment
 *
 * @author madgaksha
 */
class MooseEnvironment {

    const MAIL_TYPE_SMTP = 'smtp';
    const MAIL_TYPE_PHP = 'php';
    
    /** @var string */
    private $logfile;
    
    /** @var string */
    private $mailType;
    
    /** @var array */
    private $mailOptions;
    
    /** @var MooseMailOptions */
    private $databaseOptions;
    
    /** @var string */
    private $name;
    
    /** @var int */
    private $logLevel;

    private function __construct(array & $environment, string $name) {
        $top = $this->assertTop($environment);
        $this->logfile = $top['logfile'];
        $this->mailType = $this->sanitizeMailType($top['mail']);
        $this->logLevel = $this->sanitizeLogLevel($top['loglevel']);
        switch ($this->mailType) {
            case self::MAIL_TYPE_SMTP:   
                $this->mailOptions = new MooseSmtpOptions($top['smtp'] ?? []);
                break;
            case self::MAIL_TYPE_PHP:
                $this->mailOptions = new MoosePhpMailOptions([]);
                break;
        }
        $this->databaseOptions = new MooseDatabaseOptions($top['database'] ?? []);
        $this->name = $name;
    }
    
    /**
     * @return string The path to the log file.
     */
    public function getLogFile() : string {
        return $this->logfile;
    }
    
    public function setLogFile(string $logfile) : MooseEnvironment {
        $this->logfile = $logfile;
        return $this;
    }
    
    public function getMailOptions() : MooseMailOptions {
        return $this->mailOptions;
    }
    
    /**
     * @param array $callbacks Array of (callable|array|string)
     * If mail type is SMTP, it is passed MooseSmtpOptions as the first argument.
     * If mail type is PHP, it is passed MoosePhpOptions as the first argument.
     * @return mixed|null The return of the callback, or null when no callback
     * for the mail type was given.
     */
    public function ifMail(array $callbacks) {
        $callback = $callbacks[$this->mailType];
        if ($callback !== null) {
            return \call_user_func($callback, $this->mailOptions);
        }
        return null;
    }
    
    public function getDatabaseOptions() : MooseDatabaseOptions {
        return $this->databaseOptions;
    }

    public function isMailType(string $mailType) {
        return $this->mailType === $mailType;
    }
        
    private function &assertTop(array & $environment) : array {
        if (!isset($environment['mail']))
            throw new LogicException("Cannot create environment, missing key environments/' . $this->name . '/mail.");
        if (!isset($environment['database']))
            throw new LogicException("Cannot create environment, missing key environments/' . $this->name . '/database.");
        if (!isset($environment['logfile']) || empty(\trim($environment['logfile'])))
            $environment['logfile'] = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'baryllium.error.log';
        if (!isset($environment['loglevel'])) {
            $environment['loglevel'] = Logger::LEVEL_WARNING;
        }
        return $environment;
    }
    
    private function sanitizeMailType(string $mailType) : string {
        $mailType = \trim(mb_convert_case($mailType, MB_CASE_LOWER));
        if ($mailType !== self::MAIL_TYPE_PHP && $mailType !== self::MAIL_TYPE_SMTP) {
            $mailType = self::MAIL_TYPE_PHP;
        }
        return $mailType;
    }
    
    public function & convertToArray() : array {
        $base = [
            'logfile' => $this->logfile,
            'mail' => $this->mailType,
            'smtp' => $this->mailOptions->convertToArray(),
            'database' => $this->databaseOptions->convertToArray(),
            'loglevel' => $this->logLevel
        ];
        return $base;
    }

    public static function makeFromArray(array & $environment, string $name) : MooseEnvironment {
        return new MooseEnvironment($environment, $name);
    }

    public function setMailTypeSmtp(MooseSmtpOptions $options) : MooseEnvironment {
        $this->setMailType(self::MAIL_TYPE_SMTP);
        $this->mailOptions = $options;
        return $this;
    }
    
    public function setMailTypePhp(MoosePhpMailOptions $options = null) : MooseEnvironment {
        $this->setMailType(self::MAIL_TYPE_PHP);
        $this->mailOptions = $options;
        return $this;
    }
    
    private function setMailType(string $type) : MooseEnvironment {
        if ($type === self::MAIL_TYPE_SMTP) {
            $this->mailType = $type;
        }
        else if ($type === self::MAIL_TYPE_PHP) {
            $this->mailType = $type;
        }
        else {
            throw new InvalidArgumentException("Unknown mail type " . $type);
        }
        return $this;
    }

    public function getLogLevel() : int {
        return $this->logLevel;
    }
    
    public function setLogLevel(int $level) : MooseEnvironment {
        $this->logLevel = $this->sanitizeLogLevel($level);
        return $this;
    }

    public function sanitizeLogLevel($level) {
        $lvl = \intval($level);
        if (!\in_array($lvl, Logger::LEVEL_NAMES)) {
            return Logger::LEVEL_WARNING;
        }
        return $lvl;
    }

    public function getLogLevelName() : string {
        return Logger::LEVEL_NAMES[$this->getLogLevel()];
    }

    public function setLogLevelName(string $logLevel) {
        $this->setLogLevel(Logger::LEVEL_INTS[$logLevel]);
    }

}