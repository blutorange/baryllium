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
use Entity\Course;
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
 * For handlers handling a request specifying a \Entity\Course.
 * Courses are identified either by the course id <code>cid</code>
 * or the forum id <code>fid</code>.
 * @author madgaksha
 */
trait RequestWithCourseTrait {
    /**
     * 
     * @param BaseResponseInterface $response
     * @param HttpRequestInterface $request
     * @param EntityManagerProviderInterface $emp
     * @return Course Or null when not found.
     */
    public function retrieveCourse(BaseResponseInterface $response,
            HttpRequestInterface $request, EntityManagerProviderInterface $emp,
            TranslatorProviderInterface $tp) {
        $cid = $request->getParamInt(CmnCnst::URL_PARAM_COURSE_ID, null);
        $fid = $request->getParamInt(CmnCnst::URL_PARAM_FORUM_ID, null);

        if ($cid === null && $fid === null) {
            $response->setError(
                    HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal',
                            'request.cidfid.missing', $tp->getTranslator()));
            return null;
        }

        if ($cid !== null && $fid !== null) {
            $response->setError(
                    HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal',
                            'request.cidfid.both', $tp->getTranslator()));
            return null;
        }

        if ($fid !== null) {
            $course = AbstractDao::course($emp->getEm())->findOneByForumId($fid);
        }
        else {
            $course = AbstractDao::course($emp->getEm())->findOneById($cid);
        }

        if ($course === null) {
            $response->setError(
                    HttpResponse::HTTP_NOT_FOUND,
                    Message::dangerI18n('request.illegal',
                        'request.cidfid.notfound', $tp->getTranslator(),
                        ['cid' => $cid ?? -1, 'fid' => $fid ?? -1]));
            return null;
        }

        return $course;
    }
    
    public function retrieveCourseIfAuthorized(int $permType,
            BaseResponseInterface $response, HttpRequestInterface $request,
            EntityManagerProviderInterface $emp, TranslatorProviderInterface $tp,
            User $user) {
        $course = $this->retrieveCourse($response, $request, $emp, $tp);
        if ($course === null) {
            return null;
        }
        if (!PermissionsUtil::assertForumForUser($course->getForum(), $user, $permType, false)) {
            $response->setError(
                HttpResponse::HTTP_FORBIDDEN,
                Message::dangerI18n('request.illegal', 'request.access.denied'));
            return;
        }
        return $course;
    }
}
