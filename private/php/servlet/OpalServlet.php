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

use Doctrine\DBAL\Types\ProtectedString;
use Moose\Entity\University;
use Moose\Entity\User;
use Moose\Extension\Opal\OpalAuthorizationException;
use Moose\Extension\Opal\OpalAuthorizationProviderInterface;
use Moose\Extension\Opal\OpalBaDresden;
use Moose\Extension\Opal\OpalException;
use Moose\Extension\Opal\OpalSession;
use Moose\Extension\Opal\OpalSessionInterface;
use Moose\Model\OpalGetNodeModel;
use Moose\Util\CmnCnst;
use Moose\ViewModel\Message;
use Moose\Web\HttpResponse;
use Moose\Web\RequestException;
use Moose\Web\RequestWithOpalTrait;
use Moose\Web\RestRequestInterface;
use Moose\Web\RestResponseInterface;

/**
 * Allows reading data from Opal. Uses the credentials once to establish a 
 * session with OPAL (JSESSIONID), and stores this ID in the PHP session, so
 * that authentication must be done only once.
 *
 * @author madgaksha
 */
class OpalServlet extends AbstractEntityServlet {
    use RequestWithOpalTrait;
    
    const FIELDS_RESPONSE_NODE = ['id', 'isDirectory', 'name', 'description', 'byteSize', 'modificationDate', 'mimeType'];
    
    public function getNode(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $model OpalGetNodeModel */
        $model = $this->getExactlyOneEntity(OpalGetNodeModel::class);
        $list = $this->withOpal($this->getContext(), function(OpalSessionInterface $session) use ($model) {
            /* @var $session OpalSessionInterface */
            try {
                return $session->getFiletreeReader()->listChildrenById($model->getNodeId());
            }
            catch (OpalAuthorizationException $e) {
                $this->getContext()->getLogger()->error($e, 'Failed to list node data, not authoruzed with OPAL');
                throw new RequestException(HttpResponse::HTTP_FORBIDDEN,
                        Message::dangerI18n(
                                'request.illegal', 'opal.auth.denied',
                                $this->getTranslator()));
            }
            catch (OpalException $e) {
                $this->getContext()->getLogger()->error($e, 'Failed to list node data');
                throw new RequestException(HttpResponse::HTTP_FORBIDDEN,
                        Message::dangerI18n(
                                'request.illegal', 'opal.error.general',
                                $this->getTranslator()));
            }
        });
        $response
                ->setKey('success', 'true')
                ->setKey('entity', $this->mapObjects2Json($list, self::FIELDS_RESPONSE_NODE, true));
    }


    public static function getRoutingPath(): string {
        return CmnCnst::SERVLET_OPAL;
    }
}