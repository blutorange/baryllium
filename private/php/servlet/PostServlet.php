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

use DateTime;
use Moose\Dao\AbstractDao;
use Moose\Util\CmnCnst;
use Moose\Util\PermissionsUtil;
use Moose\Util\UiUtil;
use Moose\ViewModel\Message;
use Moose\ViewModel\MessageInterface;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponse;
use Moose\Web\RequestWithPostTrait;
use Moose\Web\RestResponseInterface;

/**
 * Servlet for manipulating \Entity\Post entities.
 *
 * @author madgaksha
 */
class PostServlet extends AbstractRestServlet {
    
    use RequestWithPostTrait;
    
    protected function restPatch(RestResponseInterface $response,
            HttpRequestInterface $request) {
        /* @var $errors MessageInterface[] */
        
        // Retrieve parameters from the request.
        $content = $request->getParam(CmnCnst::URL_PARAM_CONTENT, null);
        $returnHTML = $request->getParamBool(CmnCnst::URL_PARAM_RETURNHTML,
                false);
        
        if ($content === null) {
            $response->setError(
                    HttpResponse::HTTP_BAD_REQUEST,
                    Message::danger('request.illegal', 'request.content.missing'));
            return;
        }
        
        if (($post = $this->retrievePostIfAuthorized(
                PermissionsUtil::PERMISSION_WRITE, $response, $request,
                $this, $this, $this->getSessionHandler()->getUser())) === null) {
            return;
        }
 
        // When there are any changes, update the entity.
        if ($post->getContent() !== $content) {
            $post->setContent($content);
            $post->setEditTime(new DateTime());
            $errors = AbstractDao::generic($this->getEm())->persist($post, $this->getTranslator());
            if (\sizeof($errors) > 0) {
                $response->setError(
                        HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                        Message::danger('Could not persist post.',
                                $errors[0]->getMessage()));
                return;
            }
        }
        
        // Render the post if requested.
        if ($returnHTML) {
            $emptyArray = [];
            $data = ['post' => $post];
            $html=UiUtil::renderTemplateToHtml(CmnCnst::TEMPLATE_TC_POST,
                    $this->getEngine(), $this->getTranslator(), $emptyArray,
                    $this->getLang(), $data);
            $response->setKey('html', $html);
        }
        
        // Respond with the updated content.
        $response->setKey('content', $content);
    }
    
    protected function restDelete(RestResponseInterface $response,
            HttpRequestInterface $request) {
        if (($post = $this->retrievePostIfAuthorized(
                PermissionsUtil::PERMISSION_WRITE, $response, $request,
                $this, $this, $this->getSessionHandler()->getUser())) === null) {
            return;
        }
        // First post of a thread cannot be deleted -> delete the thread instead.
        $postList = $post->getThread()->getPostList();
        if ($postList->get(0)->getId() === $post->getId()) {
            $response->setError(HttpResponse::HTTP_BAD_REQUEST, Message::warningI18n('request.illegal', 'request.post.delete.first', $this->getTranslator()));
            return;
        }
        AbstractDao::generic($this->getEm())->remove($post);
        $response->setKey('success', 'true');
    }
    
    public static function getRoutingPath(): string {
        return CmnCnst::SERVLET_POST;
    }
}
