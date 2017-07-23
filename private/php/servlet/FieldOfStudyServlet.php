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
use Moose\Entity\FieldOfStudy;
use Moose\FormModel\TableGetListModel;
use Moose\Util\CmnCnst;
use Moose\Util\PermissionsUtil;
use Moose\Web\HttpRequest;
use Moose\Web\RequestWithUserTrait;
use Moose\Web\RestRequestInterface;
use Moose\Web\RestResponseInterface;

/**
 * For adding, querying, modifying and deleting University entities.
 *
 * @author madgaksha
 */
class FieldOfStudyServlet extends AbstractEntityServlet {
    
    use RequestWithUserTrait;
    
    const FIELD_GETLIST_SORT = [
        'shortName',
        'discipline',
        'subDiscipline'
    ];
    
    const FIELDS_GETLIST_SEARCH = [
        'shortName' => 'like',
        'discipline' => 'like',
        'subDiscipline'=> 'like'
    ];
    
    const FIELDS_GETLIST_ACCESS = [
        'id',
        'shortName',
        'discipline',
        'subDiscipline'
    ];

    protected function getList(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $fosList FieldOfStudy[] */
        $model = TableGetListModel::fromRequest($request->getHttpRequest(),
                $this->getTranslator(), self::FIELD_GETLIST_SORT,
                self::FIELDS_GETLIST_SEARCH, HttpRequest::PARAM_QUERY);
        $user = $this->getContext()->getUser();
        $currentUniversity = $this->getContext()->getUser();
        if ($user === null) {
            return;
        }        
        PermissionsUtil::assertUserForUser($user, $currentUniversity, true);
        
        $dao = Dao::fieldOfStudy($this->getEm());
        $fosList = $dao->findN($model->getSort(), $model->getIsAscending(),
                $model->getCount(), $model->getOffset(), $model->getSearch());
        $totalFiltered = $dao->countAll($model->getSearch());
        $total = $dao->countAll();
        
        $response->setKey('success', 'true');
        $response->setKey('countTotal', $total);
        $response->setKey('countFiltered', $totalFiltered);
        $response->setKey('entity', $this->mapObjects2Json($fosList,
                self::FIELDS_GETLIST_ACCESS, true));
    }

    public static function getRoutingPath(): string {
        return CmnCnst::SERVLET_FIELD_OF_STUDY;
    }
}
