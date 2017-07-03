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
use Moose\Entity\Course;
use Moose\Extension\CampusDual\CampusDualException;
use Moose\Util\DebugUtil;
use Moose\Util\PermissionsUtil;
use Moose\ViewModel\Message;
use Moose\Web\RequestException;

/**
 * For handlers handling a request specifying a \Entity\Course.
 * Courses are identified either by the course id <code>cid</code>
 * or the forum id <code>fid</code>.
 * @author madgaksha
 */
trait RequestWithCampusDualCredentialsTrait {
    /**
     *
     * @param BaseResponseInterface $response
     * @param HttpRequestInterface $request
     * @param EntityManagerProviderInterface $emp
     * @param TranslatorProviderInterface $tp
     * @return Course Or null when not found.
     */
    public function withUserCredentials(HttpRequestInterface $request, TranslatorProviderInterface $tp, callable $callback) {
        $user = Context::getInstance()->getUser();
        if (!PermissionsUtil::assertCampusDualForUser($user, false)) {
            throw new RequestException(HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                    Message::dangerI18n('illegalrequest.message', 'servlet.lesson.update.nocredentials', $tp->getTranslator()));
        }
        try {
            \call_user_func($callback, $user);
        }
        catch (CampusDualException $e) {
            DebugUtil::log("Failed to update schedule: $e");
            if ($e->is(CampusDualException::FLAG_ACCESS_DENIED)) {
                $message = Message::dangerI18n('illegalrequest.message', 'servlet.lesson.update.badcredentials', $tp->getTranslator());
            }
            else {
                $message = Message::dangerI18n('error.internal', 'servlet.lesson.update.failure', $$tp->getTranslator());
            }
            throw new RequestException(HttpResponse::HTTP_INTERNAL_SERVER_ERROR, $message);
        }
    }
}
