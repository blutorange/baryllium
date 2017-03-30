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

namespace Moose\Servlet;

use Dao\AbstractDao;
use Entity\Course;
use Entity\Document;
use Entity\Forum;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponse;
use Moose\Web\RequestWithCourseTrait;
use Moose\Web\RequestWithDocumentTrait;
use Moose\Web\RestResponseInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Util\CmnCnst;
use Util\PermissionsUtil;

/**
 * //TODO What to do when we cannot get a MIME type?
 * Description of UpdatePost
 * @author madgaksha
 */
class DocumentServlet extends AbstractRestServlet {
    
    use RequestWithCourseTrait;
    use RequestWithDocumentTrait;
    
    protected function restPut(RestResponseInterface $response,
            HttpRequestInterface $request) {
        /* @var $course Course */
        /* @var $forum Forum */
        /* @var $files UploadedFile[] */
       
        $user = $this->getSessionHandler()->getUser();
        if (($course = $this->retrieveCourseIfAuthorized(
                PermissionsUtil::PERMISSION_READWRITE, $response, $request,
                $this, $this, $user)) === null) {
            return;
        }
                             
        $dao = AbstractDao::course($this->getEm());
        $documentList = \array_map(function(UploadedFile $file) use ($user, $course, $dao) {
            $document = Document::fromUploadFile($file);
            $document->setUploader($user);
            $document->setCourse($course);
            $dao->queue($document);
            return $document;
        }, $request->getFiles(CmnCnst::URL_PARAM_DOCUMENTS));
       
        $errors = $dao->persistQueue($this->getTranslator(), true);
        
        if (\sizeof($errors) > 0) {
            $response->setError(
                    HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                    $errors[0]);
            return;
        }
        
        $linkList = array_map(function(Document $document) {
            return $this->getContext()->getServerPath($this->getRoutingPath() . "?" . CmnCnst::URL_PARAM_DOCUMENT_ID. '=' . $document->getId());
        }, $documentList);
        
        
        $response->setJson($linkList);
    }
        
    public function restPost(RestResponseInterface $response,
            HttpRequestInterface $httpRequest) {
        return $this->restPut($response, $httpRequest);
    }
    
    public function restGet(RestResponseInterface $response,
            HttpRequestInterface $httpRequest) {
        $document = $this->retrieveDocumentIfAuthorized(
                PermissionsUtil::PERMISSION_READ, $response, $httpRequest,
                $this, $this, $this->getSessionHandler()->getUser());
        if ($document === null) {
            return;
        }
        $response->setJson($document->getContentString());
        $response->getHttpResponse()->setMime($document->getMime());
    }
    
    public function restDelete(RestResponseInterface $response,
            HttpRequestInterface $httpRequest) {
        $document = $this->retrieveDocumentIfAuthorized(
                PermissionsUtil::PERMISSION_WRITE, $response, $httpRequest,
                $this, $this, $this->getSessionHandler()->getUser());
        if ($document === null) {
            return;
        }
        AbstractDao::document($this->getEm())->remove($document);
        $response->setKey('success', 'true');
    }


    public static function getRoutingPath(): string {
        return CmnCnst::SERVLET_DOCUMENT;
    }

}