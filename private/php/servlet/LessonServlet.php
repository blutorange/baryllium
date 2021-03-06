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
use Moose\Extension\CampusDual\CampusDualUtil;
use Moose\Model\LessonGetListModel;
use Moose\Util\CmnCnst;
use Moose\ViewModel\Message;
use Moose\Web\HttpResponse;
use Moose\Web\RequestException;
use Moose\Web\RequestWithCampusDualCredentialsTrait;
use Moose\Web\RestRequestInterface;
use Moose\Web\RestResponseInterface;

/**
 * Retrieves lessons for the given user. Used for displaying the schedule via
 * full calendar.
 * @author madgaksha
 */
class LessonServlet extends AbstractEntityServlet {
    
    const FIELDS_LIST_ACCESS = ['id', 'title', 'start', 'end', 'room'];
    
    use RequestWithCampusDualCredentialsTrait;
    
    /**
     * Updates the schedule for the current user's tutorial group.
     */
    protected function patchUpdate(RestResponseInterface $response, RestRequestInterface $request) {
        $errors = $this->withUserCredentials($request->getHttpRequest(), $this, function($user){
            return CampusDualUtil::updateScheduleForUser($user, $this->getEm(),
                    $this->getContext()->getLogger(), $this->getTranslator());
        });
        if (sizeof($errors) > 0) {
            throw new RequestException(HttpResponse::HTTP_BAD_REQUEST, $errors);
        }        
    }
    
    protected function getList(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $params LessonGetListModel */
        $params = $this->getExactlyOneObject($request->getJson()->request ?? [], LessonGetListModel::class, ['start','end']);
        $user = $this->getContext()->getUser();
        $tutorialGroup = $user->getTutorialGroup();
        if (empty($tutorialGroup)) {
            throw new RequestException(HttpResponse::HTTP_BAD_REQUEST,
                    Message::warningI18n('servlet.lesson.notutgroup', 'servlet.lesson.notutgroup.details', $this->getTranslator()));
        }
        $lessonList = Dao::lesson($this->getEm())->findAllByTutorialGroupAndRange($tutorialGroup, $params->getStart(), $params->getEnd());
        $response->setKey('success', 'true');
        $response->setKey('entity', $this->mapObjects2Json($lessonList, self::FIELDS_LIST_ACCESS, true));
    }

    public static function getRoutingPath(): string {
        return CmnCnst::SERVLET_LESSON;
    }
}