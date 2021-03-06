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
    const URL_PARAM_CHALLENGE = 'challenge';
    const URL_PARAM_THUMBNAIL = 'tmb';
    const URL_PARAM_PASSWORD = 'password';
    const URL_PARAM_PASSWORD_REPEAT = 'password-repeat';
    const URL_PARAM_DEBUG_ENVIRONMENT = 'dbg-db-md';
    const URL_PARAM_REGISTER_SKIP_CHECK = 'skp-reg-ck';
    const URL_PARAM_AVATAR = 'avatar';
    const URL_PARAM_ACTION_AVATAR = '_avatar';
    const URL_PARAM_REMEMBERME = 'rememberLogin';
    const URL_PARAM_PRIVATE_KEY = 'pk';
    const URL_PARAM_LANGUAGE = 'lang';
    const URL_PARAM_SUBMIT_BUTTON = 'submitButton';
    const URL_PARAM_SUBMIT_BUTTON_DATA = 'submitButtonData';

    const CACHE_MOOSE_CONFIGURATION = 'moose.phinx';
    const CACHE_MOOSE_LOCALE = 'moose.locale.';
    
    const COOKIE_REMEMBERME = 'MOO_MOO_MOO';
    const COOKIE_FIELDS = 'fields';
    const COOKIE_OPTION_POST_COUNT = 'option.post.count';
    const COOKIE_OPTION_DASHBOARD_VIEW = 'option.dashboard.static';
    
    const ERROR_CLASS_ACCESS_DENIED = 'access-denied';
    
    const ENVIRONMENT_VARIABLE_PRIVATE_KEY = 'MOOSE_PK';
    
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
    const PATH_USER_SETTING = 'public/controller/usersetting.php';
    const PATH_REGISTER = 'public/controller/register.php';
    const PATH_LOGOUT = 'public/controller/logout.php';
    const PATH_SITE_SETTINGS_MAIL = 'public/controller/settings_mail.php';
    const PATH_SITE_SETTINGS_M3 = 'public/controller/mmm.php';
    const PATH_SITE_SETTINGS_DATABASE = 'public/controller/settings_database.php';
    const PATH_SITE_SETTINGS_TASKS = 'public/controller/settings_tasks.php';
    const PATH_SITE_SETTINGS_ENVIRONMENT = 'public/controller/settings_environment.php';
    const PATH_IMPORT_FOS = 'public/controller/setup_import.php';
    const PATH_SETUP = 'private/php/setup/setup.php';
    const PATH_USERLIST = 'public/controller/userlist.php';
    const PATH_PWRECOVERY = 'public/controller/pwrecovery.php';
    const PATH_PWRESET = 'public/controller/pwreset.php';
    const PATH_CONTACT = 'public/controller/contact.php';
    const PATH_LEGALESE = 'public/controller/legalese.php';
    const PATH_SCHEDULE = 'public/controller/schedule.php';
    const PATH_EXAM = 'public/controller/exam.php';
    const PATH_FILETREE = 'public/controller/filetree.php';
    
    const SERVLET_UNIVERSITY = 'public/servlet/university.php';
    const SERVLET_FIELD_OF_STUDY = 'public/servlet/fieldofstudy.php';
    const SERVLET_COURSE = 'public/servlet/course.php';
    const SERVLET_DOCUMENT = 'public/servlet/document.php';
    const SERVLET_POST = 'public/servlet/post.php';
    const SERVLET_THREAD = 'public/servlet/thread.php';
    const SERVLET_CHECK_STUDENT_ID = 'public/servlet/checkStudentId.php';
    const SERVLET_CHECK_STUDENT_ID_EXISTS = 'public/servlet/checkStudentIdExists.php';
    const SERVLET_SEED = 'public/servlet/seed.php';
    const SERVLET_USER = 'public/servlet/user.php';
    const SERVLET_LESSON = 'public/servlet/lesson.php';
    const SERVLET_OPAL = 'public/servlet/opal.php';
    const SERVLET_EXAM = 'public/servlet/exam.php';
    const SERVLET_USER_OPTION = 'public/servlet/useroption.php'; 
    
    const SESSION_OPAL_SESSION = 'opal_session';
    const SESSION_USER_ID = 'uid';
    const SESSION_TEMPORARY_ADMIN = 'temp_sadmin';
    const SESSION_COOKIE_AUTHED = 'cookie_authed';
    const SESSION_LANGUAGE = 'lang';
    
    const BUTTON_DELETE_ELEMENT = 'btnDeleteElement';
    const BUTTON_CLOSE_DIALOG = 'btnCloseDialog';
    const BUTTON_LOGIN_CLOSE_DIALOG = 'btnLoginCloseDialog';
    const BUTTON_UPLOAD_AVATAR = 'btnUploadAvatar';
    const BUTTON_OPEN_DIALOG = 'btnOpenDialog';
    const BUTTON_DOWNLOAD_DOCUMENT = 'btnDownloadDocument';
    const BUTTON_DOWNLOAD_OPAL = 'btnDownloadOpal';
    const BUTTON_UPDATE_DOCUMENT = 'btnUpdateDocument';
    const BUTTON_ADD_DIRECTORY = 'btnAddDirectory';
    const BUTTON_DELETE_DOCUMENT = 'btnDeleteDocument';
    const BUTTON_MOVE_DOCUMENT = 'btnMoveDocument';
    const BUTTON_LOGIN_DIALOG= 'btnLoginDialog';
    const BUTTON_UPDATE_EXAM = 'btnUpdateExam';
    const BUTTON_SUBMIT = 'btnSubmit';
    const BUTTON_UPDATE_SCHEDULE = 'btnUpdateSchedule';
    const BUTTON_UPDATE_PWCD = 'btnUpdatePwcd';
    const BUTTON_REMOVE_PWCD = 'btnRemovePwcd';
    const BUTTON_REFRESH_TREE = 'btnRefreshTree';
    const BUTTON_MARKDOWN_EDIT = 'btnMarkdownEdit';
    const BUTTON_DELETE_THREAD = 'btnDeleteThread';
    
    const HTTP_CHARSET_UTF8 = 'utf-8';
    const HTTP_HEADER_LOCATION = 'Location';
    
    const LOGIN_NAME_SADMIN = 'sadmin';
    
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