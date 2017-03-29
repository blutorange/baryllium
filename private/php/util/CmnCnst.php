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

namespace Util;

/**
 * Description of CmnCnst
 *
 * @author madgaksha
 */
class CmnCnst {
    private function __construct() {}

    const URL_PARAM_NEW_POST_CONTENT = 'content';
    const URL_PARAM_NEW_THREAD_TITLE = 'title';
    const URL_PARAM_REDIRECT_URL = 'redirecturl';
    const URL_PARAM_LOGIN_PASSWORD = 'password';
    const URL_PARAM_LOGIN_STUDENTID  = 'studentid';
    const URL_PARAM_RETURNHTML= 'returnhtml';
    const URL_PARAM_POSTID= 'pid';
    const URL_PARAM_CONTENT = 'content';
    const URL_PARAM_COURSE_ID = 'cid';

    const TEMPLATE_TC_POST = 'partials/component/tc_post';
    const TEMPLATE_PAGINABLE = "partials/component/paginable";
    const TEMPLATE_MARKDOWN = 'partials/form/markdown';

    const HTTP_HEADER_LOCATION = 'Location';
    
    const PATH_FORUM_POST = 'public/controller/post.php?tid={%tid%}&off={%offset%}&cnt={%count%}';
    const PATH_FORUM_THREAD = 'public/controller/thread.php?fid={%fid%}&off={%offset%}&cnt={%count%}';
    const PATH_LOGIN_PAGE = 'public/controller/login.php';
    const PATH_DASHBOARD = 'public/controller/dashboard.php';
    const PATH_FORUM = 'public/controller/forum.php';
    const PATH_USERPROFILE = 'public/controller/userprofile.php';
    
    const HTTP_CHARSET_UTF8 = 'utf-8';
    
    const LOGIN_NAME_SADMIN = "sadmin";
    
    const ENTITY_MANAGER_CUSTOM_1 = 0;
    const ENTITY_MANAGER_CUSTOM_2 = 1;
    const ENTITY_MANAGER_CUSTOM_3 = 2;
    const ENTITY_MANAGER_CUSTOM_4 = 3;

}
