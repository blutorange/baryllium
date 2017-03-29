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

namespace Servlet;

use Controller\HttpRequestInterface;
use Controller\HttpResponse;
use Dao\AbstractDao;
use Entity\Course;
use Entity\Document;
use Entity\Forum;
use Entity\User;
use Servlet\RestResponseInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Ui\Message;
use Util\CmnCnst;
use Util\PermissionsUtil;

/**
 * //TODO What to do when we cannot get a MIME type?
 * Description of UpdatePost
 * @author madgaksha
 */
class PutDocumentServlet extends AbstractRestServlet {
    protected function restPut(RestResponseInterface $response,
            HttpRequestInterface $request) {
        /* @var $course Course */
        /* @var $forum Forum */
        /* @var $files UploadedFile[] */
        $cid = $request->getParam(CmnCnst::URL_PARAM_COURSE_ID, null);
        if ($cid === null) {
            $response->setError(
                    HttpResponse::HTTP_BAD_REQUEST,
                    Message::danger('Illegal request', 'No course given.'));
            return;
        }

        $dao = AbstractDao::course($this->getEm());
        $course = $dao->findOneById($cid);
        if ($course === null) {
            $response->setError(
                    HttpResponse::HTTP_NOT_FOUND,
                    Message::danger('Illegal request',
                            "No such course with cid $cid."));
            return;
        }
        
        $user = $this->getSessionHandler()->getUser();
        if (!PermissionsUtil::assertForumForUser($course->getForum(), $user, false)) {
            $response->setError(
                HttpResponse::HTTP_FORBIDDEN,
                Message::danger('Illegal request', 'Not authorized to edit post.'));
            return;
        }
        
        $result = \array_map(function(UploadedFile $file) use ($user, $course, $dao) {
            $document = Document::fromUploadFile($file);
            $document->setUser($user);
            $document->setCourse($course);
            $dao->queue($document);
            return "/asdasdasd/" . $document->getId();
        }, $request->getFiles('file'));
        
        $errors = $dao->persistQueue($this->getTranslator());
        
        if (sizeof($errors) > 0) {
            $response->setError(
                    HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                    Message::danger('Could not persist document.',
                            $errors[0]->getMessage()));
            return;
        }
        
        $response->setJson($result);
    }
    
    protected function restPost(RestResponseInterface $response,
            HttpRequestInterface $request) {
        $this->restPut($response, $request);
    }
}