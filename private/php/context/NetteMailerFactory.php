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

/**
 * Use the Nette mailer.
 *
 * @author madgaksha
 */
class NetteMailerFactory implements MailerFactoryInterface {
    public function makeMailer(MooseEnvironment $environment, bool $isDevelopment) : IMailer {
        return $environment->ifMail([
            MooseEnvironment::MAIL_TYPE_PHP => function(MoosePhpMailOptions $phpOptions) {
                return new SendmailMailer();
            },
            MooseEnvironment::MAIL_TYPE_SMTP => function(MooseSmtpOptions $smtpOptions) {
                return $this->configureSmtp($smtpOptions);
            }
        ]);
    }
    
    private function configureSmtp(MooseSmtpOptions $smtpOptions) {
        $encryption = $smtpOptions->getIsSecure() ? 'ssl' : 'tls';
        $options = [
            'host' => $smtpOptions->getHost(),
            'username' => $smtpOptions->getUsername(),
            'password' => $smtpOptions->getPassword(),
            'secure' => $encryption,
            'timeout' => $smtpOptions->getConnectionTimeout(),
            'port' => $smtpOptions->getPort(),
            'persistent' => $smtpOptions->getIsPersistent()
        ];
        if ($smtpOptions->getBindTo() !== 0) {
            $options['context'] = [
                'socket' => [
                    'bindto' => $smtpOptions->getBindTo()
                ]
            ];
        }
        return new SmtpMailer($options);
    }
}
