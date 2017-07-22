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

use Moose\Context\Context;
use Moose\Dao\Dao;
use Moose\Dao\DocumentDao;
use Moose\Entity\Course;
use Moose\Entity\Document;
use Moose\Entity\Forum;
use Moose\Entity\User;
use Moose\Model\DocumentGetTreeModel;
use Moose\Model\DocumentPatchMetaModel;
use Moose\Model\DocumentPatchMoveModel;
use Moose\Model\DocumentPostMkdirModel;
use Moose\Util\CmnCnst;
use Moose\Util\CollectionUtil;
use Moose\Util\PermissionsUtil;
use Moose\ViewModel\Message;
use Moose\Web\HttpResponse;
use Moose\Web\RequestException;
use Moose\Web\RequestWithCourseTrait;
use Moose\Web\RequestWithDocumentTrait;
use Moose\Web\RestRequestInterface;
use Moose\Web\RestResponseInterface;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Yaml\Escaper;

/**
 * Uses the tree Doctrine extension, see here for further details:
 * https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/tree.md#basic-examples 
 * @author madgaksha
 */
class DocumentServlet extends AbstractEntityServlet {
    
    const FIELDS_RESPONSE_DOCUMENT = ['id', 'fileName', 'documentTitle', 'description', 'isDirectory', 'createTime', 'mime', 'mimeThumbnail', 'size'];
    
    use RequestWithCourseTrait;
    use RequestWithDocumentTrait;
    
    
    protected function postSingle(RestResponseInterface $response,
            RestRequestInterface $request) {
        /* @var $course Course */
        /* @var $forum Forum */
        /* @var $files UploadedFile[] */
       
        $user = $this->getContext()->getUser();
        $course = $this->retrieveCourseIfAuthorized(PermissionsUtil::PERMISSION_READWRITE, $request->getHttpRequest(),$this, $this, $user);
                             
        $courseDao = Dao::course($this->getEm());
        $documentDao = Dao::document($this->getEm());
        $documentList = \array_map(function(UploadedFile $file) use ($user, $course, $courseDao, $documentDao) {
            $document = Document::fromUploadFile($file)
                    ->setUploader($user)
                    ->setCourse($course)
                    ->setParent($documentDao->findOneByRootAndCourse($course));
            Context::getInstance()->getLogger()->log($document->getMime());
            $courseDao->queue($document)->queue($document->getData());
            return $document;
        }, $request->getHttpRequest()->getFiles(CmnCnst::URL_PARAM_DOCUMENTS));
       
        $errors = $courseDao->persistQueue($this->getTranslator(), true);
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
                $this, $this, $this->getContext()->getUser());
        if ($request->getQueryParamBool(CmnCnst::URL_PARAM_THUMBNAIL)) {
            $response->setJson($document->getData()->getThumbnailString());
            $extension = ExtensionGuesser::getInstance()->guess($document->getMimeThumbnail());
            $response->getHttpResponse()->setMime($document->getMimeThumbnail());
        }
        else {
            $response->setJson($document->getData()->getContentString());
            $extension = ExtensionGuesser::getInstance()->guess($document->getMime());
            $response->getHttpResponse()->setMime($document->getMime());
        }
        $name = \pathinfo($document->getFileName(), \PATHINFO_FILENAME) ?? 'file';
        $response->addHeader('Content-Disposition', 'attachment; filename=' . Escaper::escapeWithDoubleQuotes($name . '.' . $extension));
    }
    
    public function deleteSingle(RestResponseInterface $response,
            RestRequestInterface $request) {
        $document = $this->retrieveDocumentIfAuthorized(
                PermissionsUtil::PERMISSION_WRITE, $request->getHttpRequest(),
                $this, $this, $this->getContext()->getUser());
        if ($document->getLevel() < 2) {
            throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal',
                            'servlet.document.delete.course', $this->getTranslator()));
        }
        Dao::document($this->getEm())->remove($document);
        $response->setKey('success', 'true');
    }

    public function patchMove(RestResponseInterface $response, RestRequestInterface $request) {
        $entities = $this->getEntities(DocumentPatchMoveModel::class, ['oldDocumentId', 'newDocumentId']);
        $user = $this->getContext()->getUser();
        $dao = Dao::document($this->getEm());
        $count = 0;
        foreach ($entities as $model) {
            /* @var $model DocumentPatchMoveModel */
            /* @var $source Document */
            /* @var $target Document */
            $source = $dao->findOneById($model->getOldDocumentId());
            $target = $dao->findOneById($model->getNewDocumentId());
            $this->assertDocWrite($source, $model->getOldDocumentId(), $user);
            $this->assertDocWrite($target, $model->getNewDocumentId(), $user);
            $this->assertDocCustom($source);
            $this->assertDocNotRoot($target);
            $this->assertDocDirectory($target);
            $this->assertDocDifferent($target, $source);
            if (!$target->isSame($source->getParent())) {
                $source->setParent($target);
                $source->setCourse($target->getCourse());
                ++$count;
            }
        }
        $response->setKey('success', 'true');
        $response->setKey('rowsAffected', $count);
    }
       
    public function putMkdir(RestResponseInterface $response, RestRequestInterface $request) {
        $entities = $this->getEntities(DocumentPostMkdirModel::class, ['documentId', 'documentTitle']);
        $user = $this->getContext()->getUser();
        $dao = Dao::document($this->getEm());
        foreach ($entities as $model) {
            /* @var $model DocumentPostMkdirModel */
            /* @var $parent Document */
            $parent = $dao->findOneById($model->getDocumentId());
            $this->assertDocWrite($parent, $model->getDocumentId(), $user);
            $this->assertDocDirectory($parent, $model->getDocumentId());
            $this->assertDocNotRoot($parent, $model->getDocumentId());
            $child = Document::createDirectory($model->getDocumentTitle())
                    ->setDescription($model->getDescription())#
                    ->setUploader($user)
                    ->setCourse($parent->getCourse())
                    ->setParent($parent);
            $dao->queue($child)->queue($child->getData());
        }
        $errors = $dao->persistQueue($this->getTranslator());
        if (!empty($errors)) {
            throw new RequestException(HttpResponse::HTTP_INTERNAL_SERVER_ERROR, $errors);
        }
        $response->setKey('success', 'true');
        $response->setKey('rowsAffected', sizeof($entities));
    }
    
    public function getTree(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $entities DocumentGetTreeModel[] */
        $entities = $this->getEntities(DocumentGetTreeModel::class, ['documentId']);
        $dao = Dao::document($this->getEm());
        $user = $this->getContext()->getUser();
        $nodeList = \array_map(function(DocumentGetTreeModel $model) use ($user, $dao) {
            /* @var $document Document */
            $documents = $this->getRootDocuments($user, $dao, $model->getDocumentId());
            $mapped = \array_map(function(Document $document) use ($model, $dao) {
                $objects = $this->prepareTreeNode($document, $model->getDepth(), $model->getExpand(), $dao);
                if ($model->getIncludeParent()) {
                    return $objects;
                }
                return $objects['fields']['children'] ?? [];
            }, $documents);
            if ($model->getDocumentId() !== null) {
                return $mapped[0];
            }
            return $mapped;
        }, $entities);
        $response->setKey('success', true)->setKey('entity', $nodeList);
    }
    
    /**
     * 
     * @param Document $document
     * @param int $depth
     * @param DocumentDao $dao
     * @return object
     */
    private function prepareTreeNode(Document $document, int $depth, array $expand, DocumentDao $dao) {
        /* @var $childDocuments Document[] */     
        $object = $this->mapObject2Json($document, self::FIELDS_RESPONSE_DOCUMENT, true);
        $childDocuments = $dao->getRepository()->children($document, true);
        if ($depth > 0 || \key_exists($document->getId(), $expand)) {
            $childObjects = \array_map(function(Document $child) use ($dao, $depth, $expand) {
                return $this->prepareTreeNode($child, $depth - 1, $expand, $dao);                
            }, $childDocuments);
            $object['fields']['children'] = CollectionUtil::sortByField($childObjects, 'documentTitle', true, $this->getLang());
        }
        else {
            $object['fields']['children'] = [];
        }
        $object['fields']['childCount'] = \sizeof($childDocuments);
        return $object;
    }
    
    private function getRootDocuments(User $user, DocumentDao $dao, $documentId = null) : array {
        if ($documentId === null) {
            if ($user->getIsSiteAdmin()) {
                $documents = $dao->findAllByRoot();
            }
            else {
                $documents = $dao->findAllByRootAndFieldOfStudy($user->getTutorialGroup()->getFieldOfStudy());
            }
            return CollectionUtil::sortByField($documents, 'documentTitle', true, $this->getLang());
        }
        else {
            return [$this->retrieveDocumentFromIdIfAuthorized($documentId, PermissionsUtil::PERMISSION_READ, $this, $this, $user)];
        }
    }

    protected function patchMeta(RestResponseInterface $response,
        RestRequestInterface $request) {
        /* @var $entities DocumentPatchMetaModel[] */
        /* @var $entity DocumentPatchMetaModel */
        /* @var $dbEntity Document */
        $user = $this->getContext()->getUser();
        $entities = $this->getEntities(DocumentPatchMetaModel::class, ['id']);
        $dao = Dao::document($this->getEm());
        foreach ($entities as $entity) {
            $dbEntity = $dao->findOneById($entity->getId());
            PermissionsUtil::assertDocumentForUser($dbEntity, $user, PermissionsUtil::PERMISSION_WRITE, true);
            if ($entity->getDocumentTitle() !== null) {
                $dbEntity->setDocumentTitle($entity->getDocumentTitle());
            }
            if ($entity->getDescription() !== null) {
                $dbEntity->setDescription($entity->getDescription());
            }
        }
        $response->setKey('success', true);
    }
    
        private function assertDocWrite(Document $document = null, int $did = null, User $user = null) {
        if ($document === null) {
            throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal',
                            'servlet.document.nosuchdoc',
                            $this->getTranslator()), [
                                'did' => $did
                            ]);
        }
        PermissionsUtil::assertDocumentForUser($document, $user,
                    PermissionsUtil::PERMISSION_WRITE, true);
    }
    
    private function assertDocNotRoot(Document $document) {
        if ($document->getLevel() < 1) {
            throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal',
                            'servlet.document.root',
                            $this->getTranslator()), [
                                'did' => $document->getId()
                            ]);
        }
    }
    
    private function assertDocCustom(Document $document) {
        if ($document->getLevel() < 2) {
            throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal',
                            'servlet.document.notcustom',
                            $this->getTranslator()), [
                                'did' => $document->getId()
                            ]);
        }
    }

    private function assertDocDirectory(Document $document) {
        if (!$document->getIsDirectory()) {
            throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal',
                            'servlet.document.nodir',
                            $this->getTranslator()), [
                                'did' => $document->getId()
                            ]);
        }
    }  
    
    private function assertDocDifferent(Document $document1, Document $document2) {
        if ($document1->isSame($document2)) {
            throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal',
                            'servlet.document.equals',
                            $this->getTranslator()), [
                                'did1' => $document1->getId(),
                                'did1' => $document2->getId()
                            ]);
        }
    }   
    

    public static function getRoutingPath(): string {
        return CmnCnst::SERVLET_DOCUMENT;
    }


}