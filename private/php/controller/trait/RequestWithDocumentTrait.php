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
use Moose\Controller\PermissionsException;
use Moose\Dao\Dao;
use Moose\Entity\Document;
use Moose\Entity\User;
use Moose\Util\CmnCnst;
use Moose\Util\PermissionsUtil;
use Moose\ViewModel\Message;

/**
 * For handlers handling a request specifying a \Entity\Document.
 * Posts are identified by the document id <code>did</code>.
 * @author madgaksha
 */
trait RequestWithDocumentTrait {

    /**
     * @param BaseResponseInterface $response
     * @param HttpRequestInterface $request
     * @param EntityManagerProviderInterface $emp
     * @param TranslatorProviderInterface $tp
     * @return Document Or null when not found.
     * @throws RequestException
     */
    public function retrieveDocument(HttpRequestInterface $request, EntityManagerProviderInterface $emp,
            TranslatorProviderInterface $tp) : Document {
        $did = $request->getParamInt(CmnCnst::URL_PARAM_DOCUMENT_ID, null);
        return $this->retrieveDocumentFromId($did, $emp, $tp);
    }
    
    public function retrieveDocumentFromId(int $did = null,
            EntityManagerProviderInterface $emp, TranslatorProviderInterface $tp) : Document {
        if ($did === null) {
            throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('request.illegal',
                            'request.did.missing', $tp->getTranslator()));
        }
        
        $document = Dao::document($emp->getEm())->findOneById($did);
        
        if ($document === null) {
            throw new RequestException(HttpResponse::HTTP_NOT_FOUND,
                    Message::dangerI18n('request.illegal',
                            'request.did.notfound', $tp->getTranslator(),
                            ['did' => $did]));
        }
        
        return $document;
    }

    /**
     * @param int $permType
     * @param BaseResponseInterface $response
     * @param HttpRequestInterface $request
     * @param EntityManagerProviderInterface $emp
     * @param TranslatorProviderInterface $tp
     * @param User $user
     * @return Document Or null when not found.
     * @throws RequestException
     */
    public function retrieveDocumentIfAuthorized(int $permType,
            HttpRequestInterface $request, EntityManagerProviderInterface $emp,
            TranslatorProviderInterface $tp, User $user) {
        $document = $this->retrieveDocument($request, $emp, $tp);
        PermissionsUtil::assertDocumentForUser($document, $user, $permType, true);
        return $document;
    }
    
    public function retrieveDocumentFromIdIfAuthorized(int $did, int $permType,
            EntityManagerProviderInterface $emp, TranslatorProviderInterface $tp,
            User $user) : Document {
        $document = $this->retrieveDocumentFromId($did, $emp, $tp);
        if (!PermissionsUtil::assertDocumentForUser($document, $user,
                $permType, false)) {
            throw new PermissionsException();
        }
        return $document;
    }
}