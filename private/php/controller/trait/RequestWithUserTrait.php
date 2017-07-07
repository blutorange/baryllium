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

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Moose\Context\Context;
use Moose\Context\EntityManagerProviderInterface;
use Moose\Context\MooseSecurity;
use Moose\Context\TranslatorProviderInterface;
use Moose\Dao\Dao;
use Moose\Entity\ExpireToken;
use Moose\Entity\Post;
use Moose\Entity\User;
use Moose\Util\CmnCnst;
use Moose\Util\PlaceholderTranslator;
use Moose\ViewModel\Message;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Symfony\Component\HttpFoundation\Cookie;


/**
 * For handlers handling a request specifying a \Entity\User.
 * Posts are identified by the user id <code>uid</code>.
 * @author madgaksha
 */
trait RequestWithUserTrait {

    /**
     * @param BaseResponseInterface $response
     * @param HttpRequestInterface $request
     * @param EntityManagerProviderInterface $emp
     * @param TranslatorProviderInterface $tp
     * @return Post Or null when not found.
     */
    public function retrieveUser(BaseResponseInterface $response,
            HttpRequestInterface $request, EntityManagerProviderInterface $emp,
            TranslatorProviderInterface $tp, bool $orCurrentUser = false) {
        $uid = $request->getParamInt(CmnCnst::URL_PARAM_USER_ID, null);
        return $this->retrieveUserFromId($response, $emp, $tp, $uid, $orCurrentUser);
    }
    
    public function retrieveUserFromId(BaseResponseInterface $response,
            EntityManagerProviderInterface $emp, TranslatorProviderInterface $tp,
            int $uid = null, bool $orCurrentUser = false,
            bool $enforceSessionUser = false) {
        if ($orCurrentUser && $uid === null) {
                $user = Context::getInstance()->getUser();                
        }
        else {
            if ($uid === null) {
                $response->setError(
                        HttpResponse::HTTP_BAD_REQUEST,
                        Message::warningI18n('request.illegal',
                                'request.uid.missing', $tp->getTranslator()));
                return null;
            }
            $user = Dao::user($emp->getEm())->findOneById($uid);
        }
        
        if ($user === null) {
            throw new RequestException(
                    HttpResponse::HTTP_NOT_FOUND,
                    Message::dangerI18n('request.illegal',
                            'request.uid.notfound', $tp->getTranslator(),
                            ['uid' => $uid]));
        }
               
        if ($enforceSessionUser && $user->isCookieAuthed()) {
            throw new RequestException(HttpResponse::HTTP_FORBIDDEN,
                    Message::dangerI18n('accessdenied.message',
                            'accessdenied.cookieauth.detail',
                            $tp->getTranslator()));
        }
        
        return $user;
    }

    
    protected function createCookieAuth(HttpResponseInterface $response,
            MooseSecurity $security, EntityManagerInterface $em,
            PlaceholderTranslator $translator,
            User $user) {
        $token = ExpireToken::create($security->getRememberMeTimeout())
                ->setDataEntity($user, 'RMB');
        $value = $token->fetch() . '.' . $token->withChallenge()->getString();
        $cookie = new Cookie(
                CmnCnst::COOKIE_REMEMBERME,
                $value,
                (new DateTime())->add(new DateInterval('PT' . $security->getRememberMeTimeout(). 'S')),
                '/',
                null,
                $security->getSessionSecure(),
                $security->getHttpOnly(),
                false,  // URL encoding necessary for characters such as +.
                $security->getSameSite());
        $response->addCookie($cookie);
        $errors = Dao::expireToken($em)
                    ->persist($token, $translator);
        if (!empty($errors)) {
                $response->addRedirectUrlMessage('RememberFailure', Message::TYPE_WARNING);
        }
    }
}