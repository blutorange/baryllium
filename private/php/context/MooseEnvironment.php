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
    private $smtpOptions;
    
    /** @var array */
    private $databaseOptions;
    
    /** @var string */
    private $name;

    private function __construct(array & $environment, string $name) {
        $top = $this->assertTop($environment);
        $this->logfile = $top['logfile'];
        $this->mailType = $this->sanitizeMailType($top['mail']);
        $this->smtpOptions = isset($top['smtp']) ? $top['smtp'] : [];
        $this->databaseOptions = $top['database'];
        $this->name = $name;
    }
    
    /**
     * @return string The path to the log file.
     */
    public function getLogFile() : string {
        return $this->logfile;
    }
    
    public function getSmtpOptions() : array {
        return $this->smtpOptions;
    }
    
    public function getDatabaseOptions() : array {
        return $this->databaseOptions;
    }

    public function isMailType(string $mailType) {
        return $this->mailType === $mailType;
    }
        
    private function &assertTop(array & $environment) : array {
        if (!isset($environment['mail']))
            throw new \LogicException("Cannot create environment, missing key environments/' . $this->name . '/mail.");
        if (!isset($environment['database']))
            throw new \LogicException("Cannot create environment, missing key environments/' . $this->name . '/database.");
        if (!isset($environment['logfile']) || empty(\trim($environment['logfile'])))
            $environment['logfile'] = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'baryllium.error.log';
        return $environment;
    }
    
    private function sanitizeMailType(string $mailType) : string {
        $mailType = \trim(\mb_convert_case($mailType, \MB_CASE_LOWER));
        if ($mailType !== self::MAIL_TYPE_PHP && $mailType !== self::MAIL_TYPE_SMTP)
            $mailType = self::MAIL_TYPE_PHP;
        return $mailType;
    }
    
    public function & convertToArray() : array {
        $base = [
            'logfile' => $this->logfile,
            'mail' => $this->mailType,
            'smtp' => $this->smtpOptions,
            'database' => $this->databaseOptions
        ];
        return $base;
    }

    public static function makeFromArray(array & $environment, string $name) : MooseEnvironment {
        return new MooseEnvironment($environment, $name);
    }
}