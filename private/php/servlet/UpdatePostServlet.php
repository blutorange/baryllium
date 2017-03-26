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

use Dao\AbstractDao;
use Entity\AbstractEntity;

/**
 * Description of UpdatePost
 *
 * @author madgaksha
 */
class UpdatePostServlet extends AbstractRestServlet {
    protected function rest(array & $requestData, array &$responseData): int {
        /* @var $post \Entity\Post */
        /* @var $errors \Ui\Message[] */
        $content = $this->getParam('content', null);
        $pid = $this->getParamInteger('pid', AbstractEntity::INVALID_ID);
        $code = 200;
        if ($content === null) {
            $this->setError($responseData, 'Illegal request', 'No content given.');
            $code = 400;
        }
        else if ($pid <= AbstractEntity::INVALID_ID) {
            $this->setError($responseData, 'Illegal request', 'No pid or illegal pid given.');
            $code = 400;
        }
        else {
            $dao = AbstractDao::post($this->getEm());
            $post = $dao->findOneById($pid);
            if ($post === null) {
                $this->setError($responseData, 'Illegal request', "No such post with pid $pid.");
                $code = 404;
            }
            else if ($post->getUser()->getId() !== $this->getSessionHandler()->getUser()->getId()) {
                $this->setError($responseData, 'Illegal request', 'Not authorized to edit post.');
                $code = 403;
            }
            else if ($post->getContent() !== $content) {
                $post->setContent($content);
                $errors = $dao->persist($post, $this->getTranslator());
                if (sizeof($errors) > 0) {
                    $this->setError($responseData, $errors[0]->getMessage(), $errors[0]->getDetails());
                    $code = 500;
                }
                else {
                    $responseData['content'] = $content;
                }
            }
            else {
                $responseData['content'] = $content;
            }
        }
        return $code;
    }
}