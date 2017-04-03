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

use Nette\Mail\IMailer;
use Nette\Mail\SendmailMailer;
use Nette\Mail\SmtpMailer;
use const MB_CASE_LOWER;
use function mb_convert_case;

/**
 * Description of EntityManagerFactory
 *
 * @author madgaksha
 */
class NetteMailerFactory implements MailerFactoryInterface {
    public function makeMailer(array $environment, bool $isDevelopment) : IMailer {
        $type = mb_convert_case(\trim($environment['mail']), MB_CASE_LOWER);
        if ($type !== 'smtp') {
            return new SendmailMailer();
        }
        $smtp = $environment['smtp'];
        $bindto = \array_key_exists('bindto', $smtp) ? $smtp['bindto'] : '0';
        $secure = \array_key_exists('secure', $smtp) ? !!$smtp['secure'] : true;
        $secure = $secure ? 'ssl' : 'tls';
        $port = \array_key_exists('port', $smtp) ? \intval($smtp['port']) : 0;
        $timeout = \array_key_exists('timeout', $smtp) ? \intval($smtp['timeout']) : 0;
        $options = [
            'host' => $smtp['host'],
            'username' => $smtp['user'],
            'password' => $smtp['pass'],
            'secure' => $secure,
            'timeout' => $timeout > 0 ? $timeout : 20,
            'port' => $port > 0 ? $port : ($secure ? 465 : 25),
        ];
        if (\array_key_exists('persistent', $smtp) && $smtp['persistent']) {
            $options['persistent'] = true;
        }
        if (!empty($bindto) && $bindto !== '0') {
            $options['context'] = [
                'socket' => [
                    'bindto' => $smtp['bindto']
                ]
            ];
        }
        return new SmtpMailer($options);
    }
}
