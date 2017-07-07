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

namespace Moose\Controller;

use Moose\Dao\Dao;
use Moose\Util\CmnCnst;
use Moose\Util\UiUtil;
use Moose\ViewModel\Message;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;
use function mb_substr;

/**
 * @author madgaksha
 */
class UserProfileController extends BaseController {
    
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        $user = $this->getContext()->getUser();
        
        $postCount = Dao::post($this->getEm())->countByUser($user);
        
        if ($user !== null) {
            $this->renderTemplate('t_userprofile', [
                'user' => $user,
                'postCount' => $postCount
            ]);
        }
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        $this->processAvatar($response, $request);
        $this->doGet($response, $request);
    }

    public function processAvatar(HttpResponseInterface $response, HttpRequestInterface $request) {
        // Retrieve uploaded avatar image and create scaled down version.
        /* @var $files UploadedFile[] */
        if ($request->getParam(CmnCnst::URL_PARAM_ACTION_AVATAR) === null) {
            return;
        }
        $files = $request->getFiles(CmnCnst::URL_PARAM_AVATAR);
        if (\sizeof($files) === 0) {
            $response->addMessage(Message::dangerI18n('request.illegal', 'profile.avatar.nofile', $this->getTranslator()));
            return;
        }
        if (mb_substr($files[0]->getMimeType(), 0, 5) !== 'image') {
            $response->addMessage(Message::dangerI18n('request.illegal', 'profile.avatar.noimage', $this->getTranslator()));
            return;
        }
        try {
            $data = UiUtil::generateThumbnailImage($files[0], 128, 128, 90, 'jpg');
            $avatar = UiUtil::toBase64('image/jpg', $data);
        }
        catch (Throwable $e) {
            Context::getInstance()->getLogger()->log("Could not create thumbnail from image file: $e");
            $response->addMessage(Message::dangerI18n('request.illegal', 'profile.avatar.badfile', $this->getTranslator()));
            return;
        }
        if ($avatar === null) {
            $response->addMessage(Message::dangerI18n('error.internal', 'profile.avatar.illegible', $this->getTranslator()));
            return;
        }
        $user = $this->getContext()->getUser();
        // Update avatar image.
        $user->setAvatar($avatar);
        $errors = [];
        if (!Dao::generic($this->getEm())->validateEntity($user,
                $this->getTranslator(), $errors)) {
            $this->getEm()->refresh($user);
        }
        $response->addMessages($errors);
    }
}