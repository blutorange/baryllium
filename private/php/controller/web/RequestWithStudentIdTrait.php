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

use Moose\Context\EntityManagerProviderInterface;
use Moose\Context\TranslatorProviderInterface;
use Moose\Dao\AbstractDao;
use Moose\Entity\User;
use Moose\Util\CmnCnst;
use Moose\ViewModel\Message;
use Moose\ViewModel\MessageInterface;

/**
 * For handlers handling a request with a student ID. Retrieves the \Entity\User
 * based on the student ID from the request.
 * @author madgaksha
 */
trait RequestWithStudentIdTrait {
    /**
     * @param BaseResponseInterface $response
     * @param HttpRequestInterface $request
     * @param EntityManagerProviderInterface $emp
     * @return User Or null when not found.
     */
    public function retrieveUser(BaseResponseInterface $response,
            HttpRequestInterface $request, EntityManagerProviderInterface $emp,
            TranslatorProviderInterface $tp) {    
        $studentId = $this->retrieveStudentId($response, $request, $tp);
        if ($studentId === null) {
            $response->setError(HttpResponse::HTTP_BAD_REQUEST,
                Message::warningI18n('request.illegal',
                        'request.studentid.missing', $tp->getTranslator()));
            return null;
        }
        $sadmin = $studentId === CmnCnst::LOGIN_NAME_SADMIN;
        $dao = AbstractDao::user($emp->getEm());
        return $sadmin ? $dao->findOneSiteAdmin() : $dao->findOneByStudentId($studentId);
    }
    
    /**
     * 
     * @param BaseResponseInterface $response
     * @param HttpRequestInterface $request
     * @return string Or null when not found.
     */
    public function retrieveStudentId(BaseResponseInterface $response,
            HttpRequestInterface $request, TranslatorProviderInterface $tp,
            bool $allowSiteAdmin = true) {
        $raw = \trim($request->getParam(CmnCnst::URL_PARAM_STUDENTID, ''));
        $match = [];
        if ($allowSiteAdmin && $raw === CmnCnst::LOGIN_NAME_SADMIN) {
            return CmnCnst::LOGIN_NAME_SADMIN;
        }
        if (\preg_match("/(\d{7})/u", $raw, $match) !== 1) {
            $response->setError(HttpResponse::HTTP_BAD_REQUEST,
                Message::warningI18n('request.illegal',
                        'request.studentid.missing', $tp->getTranslator()));
            return null;
        }
        return $match[1];
    }
}