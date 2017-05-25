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

use Moose\Context\Context;
use Moose\Context\EntityManagerProviderInterface;
use Moose\Context\TranslatorProviderInterface;
use Moose\Dao\AbstractDao;
use Moose\Entity\Post;
use Moose\Util\CmnCnst;
use Moose\ViewModel\Message;

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
        return $this->retrieveUserFromId($response, $request, $emp, $tp, $uid, $orCurrentUser);
    }
    
    public function retrieveUserFromId(BaseResponseInterface $response,
            EntityManagerProviderInterface $emp, TranslatorProviderInterface $tp,
            int $uid = null, bool $orCurrentUser = false) {
        if ($orCurrentUser && $uid === null) {
                $user = Context::getInstance()->getSessionHandler()->getUser();                
        }
        else {
            if ($uid === null) {
                $response->setError(
                        HttpResponse::HTTP_BAD_REQUEST,
                        Message::warningI18n('request.illegal',
                                'request.uid.missing', $tp->getTranslator()));
                return null;
            }
            $user = AbstractDao::user($emp->getEm())->findOneById($uid);
        }
        
        if ($user === null) {
            $response->setError(
                    HttpResponse::HTTP_NOT_FOUND,
                    Message::dangerI18n('request.illegal',
                            'request.uid.notfound', $tp->getTranslator(),
                            ['uid' => $uid]));
            return null;
        }
        
        return $user;
    }
}