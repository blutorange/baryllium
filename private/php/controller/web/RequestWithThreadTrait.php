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

use Dao\AbstractDao;
use Entity\Thread;
use Entity\User;
use Moose\Context\EntityManagerProviderInterface;
use Moose\Context\TranslatorProviderInterface;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponse;
use Ui\Message;
use Util\CmnCnst;
use Util\PermissionsUtil;
use Moose\Web\BaseResponseInterface;

/**
 * For handlers handling a request specifying a \Entity\Thread.
 * Posts are identified by the thread id <code>tid</code>.
 * @author madgaksha
 */
trait RequestWithThreadTrait {

    /**
     * @param BaseResponseInterface $response
     * @param HttpRequestInterface $request
     * @param EntityManagerProviderInterface $emp
     * @return Thread Or null when not found.
     */
    public function retrieveThread(BaseResponseInterface $response,
            HttpRequestInterface $request, EntityManagerProviderInterface $emp,
            TranslatorProviderInterface $tp) {
        $tid = $request->getParamInt(CmnCnst::URL_PARAM_THREAD_ID, null);

        if ($tid === null) {
            $response->setError(
                    HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal',
                            'request.tid.missing.', $tp->getTranslator()));
            return null;
        }
        
        $thread = AbstractDao::thread($emp->getEm())->findOneById($tid);
        
        if ($thread === null) {
            $response->setError(
                    HttpResponse::HTTP_NOT_FOUND,
                    Message::dangerI18n('request.illegal',
                            'request.tid.notfound', $tp->getTranslator(),
                            ['tid' => $tid]));
            return null;
        }
        
        return $thread;
    }
    
    /**
     * @param BaseResponseInterface $response
     * @param HttpRequestInterface $request
     * @param EntityManagerProviderInterface $emp
     * @param User $user
     * @return Thread Or null when not found.
     */
    public function retrieveThreadIfAuthorized(int $permType,
            BaseResponseInterface $response, HttpRequestInterface $request,
            EntityManagerProviderInterface $emp, TranslatorProviderInterface $tp,
            User $user) {
        $thread = $this->retrieveThread($response, $request, $emp, $tp);
        if ($thread === null) {
            return null;
        }
        if (!PermissionsUtil::assertThreadForUser($thread, $user, $permType, false)) {
            $response->setError(
                HttpResponse::HTTP_FORBIDDEN,
                Message::dangerI18n('request.illegal', 'request.access.denied',
                        $tp->getTranslator()));
        }
        return $thread;
    }
}