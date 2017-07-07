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
use Moose\Entity\User;
use Moose\Entity\UserOption;
use Moose\Model\UserOptionGetOptionModel;
use Moose\Util\CmnCnst;
use Moose\Util\PermissionsUtil;
use Moose\ViewModel\Message;
use Moose\Web\HttpResponse;
use Moose\Web\RequestWithUserTrait;
use Moose\Web\RestRequestInterface;
use Moose\Web\RestResponseInterface;
use ReflectionException;

/**
 * Description of UserOptionServlet
 *
 * @author madgaksha
 */
class UserOptionServlet extends AbstractEntityServlet {
    use RequestWithUserTrait;
    
    protected function getOption(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $data UserOptionGetOptionModel */
        $data = $this->getExactlyOneObject($request->getJson()->request ?? [], UserOptionGetOptionModel::class, ['uid', 'optionList']);
        $optionsMap = [];
        $user = $this->retrieveUserFromId($response, $this, $this, $data->getUid(), true);
        if ($user === null) {
            return;
        }
        PermissionsUtil::assertUserForUser($user, $this->getContext()->getUser(), true);
        $userOption = $user->getUserOption();
        foreach ($data->getOptionList() as $optionName => $optionDefaultValue) {
            try {
                $optionsMap[$optionName] = $userOption->getOption($optionName) ?? $optionDefaultValue;
            }
            catch (ReflectionException $re) {
                Context::getInstance()->getLogger()->log("Failed to retrieve option value for $optionName: $re");
                $response->setJson('options', []); 
                $response->setError(HttpResponse::HTTP_BAD_REQUEST,
                        Message::warningI18n('request.illegal', 'servlet.useroption.noexist', $this->getTranslator(), ['name' => $optionName]));
                return;
            }
        }
        $response->setKey('success', true);
        $response->setKey('options', $optionsMap);            
    }
    
    protected function getAll(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $data UserOptionGetOptionModel */
        $data = $this->getExactlyOneObject($request->getJson()->request ?? [], UserOptionGetOptionModel::class, ['uid', 'optionList']);
        $user = $this->retrieveUserFromId($response, $this, $this, $data->getUid(), true);
        if ($user === null) {
            return;
        }
        PermissionsUtil::assertUserForUser($user, $this->getContext()->getUser(), true);
        $userOption = $user->getUserOption();
        $optionsMap = [];
        foreach (UserOption::FIELDS as $optionName) {
            try {
                $optionsMap[$optionName] = $userOption->getOption($optionName);
            }
            catch (ReflectionException $re) {
                Context::getInstance()->getLogger()->log("Failed to retrieve option value for $optionName: $re");
                $response->setJson('options', []); 
                $response->setError(HttpResponse::HTTP_BAD_REQUEST,
                        Message::warningI18n('request.illegal', 'servlet.useroption.noexist', $this->getTranslator(), ['name' => $optionName]));
                return;
            }
        }
        $response->setKey('success', true);
        $response->setKey('options', $optionsMap);            
    }
    
    protected function postOption(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $data UserOptionGetOptionModel */
        /* @var $user User */
        $data = $this->getExactlyOneObject($request->getJson()->request ?? [], UserOptionGetOptionModel::class, ['uid', 'optionList']);
        $user = $this->retrieveUserFromId($response, $this, $this, $data->getUid(), true, true);
        if ($user === null) {
            return;
        }
        PermissionsUtil::assertUserForUser($user, $this->getContext()->getUser(), true, true);
        $userOption = $user->getUserOption();
        foreach ($data->getOptionList() as $optionName => $optionValue) {
            try {
                $userOption->setOption($optionName, $optionValue);
            }
            catch (ReflectionException $re) {
                Context::getInstance()->getLogger()->log("Failed to persist option value $optionValue for $optionName: $re");
                $response->setJson('options', []); 
                $response->setError(HttpResponse::HTTP_BAD_REQUEST,
                        Message::warningI18n('request.illegal', 'servlet.useroption.noexist', $this->getTranslator(), ['name' => $optionName]));
                return;
            }
        }
        $response->setKey('success', true);
    }

    public static function getRoutingPath(): string {
        return CmnCnst::SERVLET_USER_OPTION;
    }
}