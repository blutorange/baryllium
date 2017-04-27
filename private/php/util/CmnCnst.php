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

namespace Moose\Util;

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
    const URL_PARAM_STUDENTID  = 'studentid';
    const URL_PARAM_RETURNHTML= 'returnhtml';
    const URL_PARAM_ACTION = 'action';
    const URL_PARAM_POSTID= 'pid';
    const URL_PARAM_CONTENT = 'content';
    const URL_PARAM_SYSTEM_MESSAGE = 'sysmsg';
    const URL_PARAM_DOCUMENTS = 'documents';
    const URL_PARAM_COURSE_ID = 'cid';
    const URL_PARAM_USER_ID = 'uid';
    const URL_PARAM_FORUM_ID = 'fid';
    const URL_PARAM_THREAD_ID = 'tid';
    const URL_PARAM_DOCUMENT_ID = 'did';
    const URL_PARAM_OFFSET = 'off';
    const URL_PARAM_SORT = 'srt';
    const URL_PARAM_SORTDIR = 'sdr';
    const URL_PARAM_COUNT = 'cnt';
    const URL_PARAM_SEARCH = 'src';
    const URL_PARAM_TOKEN = 'token';
    const URL_PARAM_PASSWORD = 'password';
    const URL_PARAM_PASSWORD_REPEAT = 'password-repeat';
    const URL_PARAM_DEBUG_ENVIRONMENT = 'dbg-db-md';
    const URL_PARAM_REGISTER_SKIP_CHECK = 'skp-reg-ck';
    const URL_PARAM_AVATAR = 'avatar';
    const URL_PARAM_ACTION_AVATAR = '_avatar';

    const COOKIE_FIELDS = 'fields';
    const COOKIE_OPTION_POST_COUNT = 'option.post.count';

    const TEMPLATE_TC_POST = 'partials/component/tc_post';
    const TEMPLATE_PAGINABLE = "partials/component/paginable";
    const TEMPLATE_MARKDOWN = 'partials/form/markdown';
    const TEMPLATE_UNHANDLED_ERROR = 'unhandledError';
    
    const PATH_FORUM_THREAD = 'public/controller/forum.php?fid={%fid%}&off={%off%}&cnt={%cnt%}';
    const PATH_FORUM_POST = 'public/controller/thread.php?tid={%tid%}&off={%off%}&cnt={%cnt%}';
    const PATH_USERLIST_PROFILE = 'public/controller/userlist.php?off={%off%}&cnt={%cnt%}';
    const PATH_LOGIN_PAGE = 'public/controller/login.php';
    const PATH_DASHBOARD = 'public/controller/dashboard.php';
    const PATH_BOARD = 'public/controller/board.php';
    const PATH_FORUM = 'public/controller/forum.php';
    const PATH_THREAD = 'public/controller/thread.php';
    const PATH_PROFILE = 'public/controller/userprofile.php';
    const PATH_REGISTER = 'public/controller/register.php';
    const PATH_LOGOUT = 'public/controller/logout.php';
    const PATH_SITE_SETTINGS = 'public/controller/settings.php';
    const PATH_IMPORT_FOS = 'public/controller/setup_import.php';
    const PATH_SETUP = 'private/php/setup/setup.php';
    const PATH_USERLIST = 'public/controller/userlist.php';
    const PATH_PWRECOVERY = 'public/controller/pwrecovery.php';
    const PATH_PWRESET = 'public/controller/pwreset.php';
    
    const SERVLET_DOCUMENT = 'public/servlet/document.php';
    const SERVLET_POST = 'public/servlet/post.php';
    const SERVLET_THREAD = 'public/servlet/thread.php';
    const SERVLET_CHECK_STUDENT_ID = 'public/servlet/checkStudentId.php';
    const SERVLET_CHECK_STUDENT_ID_EXISTS = 'public/servlet/checkStudentIdExists.php';
    const SERVLET_SEED = 'public/servlet/seed.php';
    const SERVLET_USER = 'public/servlet/user.php';
    
    const BUTTON_DELETE_ELEMENT = 'btnDeleteElement';
    const BUTTON_CLOSE_DIALOG = 'btnCloseDialog';
    const BUTTON_UPLOAD_AVATAR = 'btnUploadAvatar';
    const BUTTON_OPEN_DIALOG = 'btnOpenDialog';
    const BUTTON_MARKDOWN_EDIT = 'btnMarkdownEdit';
    const BUTTON_DELETE_THREAD = 'btnDeleteThread';
    
    const HTTP_CHARSET_UTF8 = 'utf-8';
    const HTTP_HEADER_LOCATION = 'Location';
    
    const LOGIN_NAME_SADMIN = "sadmin";
    
    const MIN_PAGINABLE_COUNT = 10;
    
    const ID_DIALOG_DELETE_ENTITY = 'dialogDeleteEntity';
    
    const ENTITY_MANAGER_CUSTOM_1 = 0;
    const ENTITY_MANAGER_CUSTOM_2 = 1;
    const ENTITY_MANAGER_CUSTOM_3 = 2;
    const ENTITY_MANAGER_CUSTOM_4 = 3;
    
    const ENTITY_MANAGER_MAIL = 4;
    
    // In seconds
    const LIFETIME_PWCHANGE = 24*60*60;

}
