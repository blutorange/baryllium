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

use Doctrine\DBAL\Types\ProtectedString;
use Exception;
use Moose\Context\Context;
use Moose\Controller\PermissionsException;
use Moose\Entity\University;
use Moose\Entity\User;
use Moose\Extension\Opal\OpalAuthorizationProviderInterface;
use Moose\Extension\Opal\OpalBaDresden;
use Moose\Extension\Opal\OpalException;
use Moose\Extension\Opal\OpalSession;
use Moose\Extension\Opal\OpalSessionInterface;
use Moose\Util\CmnCnst;
use Moose\ViewModel\Message;

/**
 * Common functions for requests working with OPAL.
 *
 * @author madgaksha
 */
trait RequestWithOpalTrait {
    protected function retrieveAuthProvider(User $user) : OpalAuthorizationProviderInterface {
        if ($user->isAnonymous()) {
            throw new PermissionsException();
        }
        switch ($user->getTutorialGroup()->getUniversity()->getIdentifier()) {
            case University::ID_BA_DRESDEN:
                $pwdCd = $user->getPasswordCampusDual();
                if (ProtectedString::isEmpty($pwdCd)) {
                    throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                        Message::dangerI18n('request.illegal',
                                'opal.unsupported.institution',
                                $this->getTranslator()));
                }
                return new OpalBaDresden($user->getStudentId(), $pwdCd);
            default:
                throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                        Message::dangerI18n('request.illegal',
                                'opal.badresden.nopass',
                                $this->getTranslator()));
        }
    }
    
    /**
     * 
     * @param Context $context
     * @param callable|array|string $callback
     * @return mixed The return of the callback.
     * @throws OpalException When there is an error while communication with OPAL.
     * @throws Exception The exception thrown by the callback.
     */
    protected function withOpal(Context $context, $callback) {
        $sessionStore = new ProtectedString($context->getSessionHandler()->fetch(CmnCnst::SESSION_OPAL_SESSION));
        $authorizationProvider = $this->retrieveAuthProvider($context->getUser());
        return OpalSession::open($authorizationProvider, function(OpalSessionInterface $session) use ($sessionStore, $context, $callback) {
            if (ProtectedString::isEmpty($sessionStore)) {
                $sessionStore = $session->store();
                $context->getSessionHandler()->store(CmnCnst::SESSION_OPAL_SESSION, $sessionStore->getString());
            }
            else {
                $session->restore($sessionStore);
            }
            return \call_user_func($callback, $session);
        }, $context->getLogger(), true);
    }
}
