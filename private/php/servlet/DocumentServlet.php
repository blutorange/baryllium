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

use Moose\Dao\Dao;
use Moose\Dao\DocumentDao;
use Moose\Entity\Course;
use Moose\Entity\Document;
use Moose\Entity\Forum;
use Moose\Util\CmnCnst;
use Moose\Util\PermissionsUtil;
use Moose\ViewModel\ARestServletModel;
use Moose\Web\HttpResponse;
use Moose\Web\RequestException;
use Moose\Web\RequestWithCourseTrait;
use Moose\Web\RequestWithDocumentTrait;
use Moose\Web\RestRequestInterface;
use Moose\Web\RestResponseInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * //TODO What to do when we cannot get a MIME type?
 * @author madgaksha
 */
class DocumentServlet extends AbstractEntityServlet {
    
    use RequestWithCourseTrait;
    use RequestWithDocumentTrait;
    
    protected function postSingle(RestResponseInterface $response,
            RestRequestInterface $request) {
        /* @var $course Course */
        /* @var $forum Forum */
        /* @var $files UploadedFile[] */
       
        $user = $this->getSessionHandler()->getUser();
        $course = $this->retrieveCourseIfAuthorized(PermissionsUtil::PERMISSION_READWRITE, $response, $request->getHttpRequest(),$this, $this, $user);
                             
        $dao = Dao::course($this->getEm());
        $documentList = \array_map(function(UploadedFile $file) use ($user, $course, $dao) {
            $document = Document::fromUploadFile($file)
                    ->setUploader($user)
                    ->setCourse($course);
            $dao->queue($document)->queue($document->getData());
            return $document;
        }, $request->getHttpRequest()->getFiles(CmnCnst::URL_PARAM_DOCUMENTS));
       
        $errors = $dao->persistQueue($this->getTranslator(), true);
        if (!empty($errors) > 0) {
            throw new RequestException(HttpResponse::HTTP_INTERNAL_SERVER_ERROR, $errors[0]);
        }
        
        $linkList = \array_map(function(Document $document) {
            $documentPath = self::getRoutingPath() . '?' . CmnCnst::URL_PARAM_ACTION . '=single&' . CmnCnst::URL_PARAM_DOCUMENT_ID. '=' . $document->getId();
            return $this->getContext()->getServerPath($documentPath);
        }, $documentList);
        
        $response->setJson($linkList);
    }
    
    public function getSingle(RestResponseInterface $response, RestRequestInterface $request) {
        $document = $this->retrieveDocumentIfAuthorized(
                PermissionsUtil::PERMISSION_READ, $request->getHttpRequest(),
                $this, $this, $this->getSessionHandler()->getUser());
        if ($request->getQueryParamBool(CmnCnst::URL_PARAM_THUMBNAIL)) {
            $response->setJson($document->getData()->getThumbnailString());
            $response->getHttpResponse()->setMime($document->getData()->getMimeThumbnail());
        }
        else {
            $response->setJson($document->getData()->getContentString());
            $response->getHttpResponse()->setMime($document->getData()->getMime());
        }
    }
    
    public function deleteSingle(RestResponseInterface $response,
            RestRequestInterface $request) {
        $document = $this->retrieveDocumentIfAuthorized(
                PermissionsUtil::PERMISSION_WRITE, $request->getHttpRequest(),
                $this, $this, $this->getSessionHandler()->getUser());
        if ($document === null) {
            return;
        }
        Dao::document($this->getEm())->remove($document);
        $response->setKey('success', 'true');
    }
    
    public function getTree(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $entities DocumentGetTreeModel[] */
        $entities = $this->getEntities(DocumentGetTreeModel::class, ['documentId', 'depth']);
        $dao = Dao::document($this->getEm());
        $user = $this->getSessionHandler()->getUser();
        $nodeList = \array_map(function(DocumentGetTreeModel $model) use ($user, $dao) {
            $document = $this->retrieveDocumentFromIdIfAuthorized($model->getDocumentId(), PermissionsUtil::PERMISSION_READ, $this, $this, $user);
            return $this->prepareTreeNode($document, $model->getDepth(), $dao);
        }, $entities);
        $response->setKey('success', true);
        $response->setKey('nodes', $nodeList);
    }
    
    private function prepareTreeNode(Document $document, int $depth, DocumentDao $dao) {
        // TODO 
        // https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/tree.md#basic-examples
        $dao->getRepository()->children($document);
    }

    public static function getRoutingPath(): string {
        return CmnCnst::SERVLET_DOCUMENT;
    }
}

class DocumentGetTreeModel extends ARestServletModel {
    private $documentId;
    private $depth;
    public function getDocumentId() {
        return $this->documentId;
    }
    public function getDepth() {
        return $this->depth;
    }
    public function setDocumentId($documentId) {
        $this->documentId = $this->paramInt($documentId);
        return $this;
    }
    public function setDepth($depth) {
        $this->depth = $this->paramInt($depth);
        return $this;
    }
}