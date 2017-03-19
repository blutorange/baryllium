<?php

/* Note: This license has also been called the "New BSD License" or "Modified
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

namespace Extension\CampusDual;

use Requests;
use Requests_Cookie_Jar;
use Requests_Response;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

/**
 * Static methods for the campus dual login, such as sending the requests.
 *
 * @author madgaksha
 */
class CampusDualHelper {

    const HEADER_SAPUSER = 'sap-user';
    const HEADER_SAPPASSWORD = 'sap-password';

    const HEADER_SAPEVENTQUEUE = "SAPEVENTQUEUE";
    const HEADER_SAPEVENTQUEUE_VALUE = "Form_Submit%7EE002Id%7EE004SL__FORM%7EE003%7EE002ClientAction%7EE004submit%7EE005ActionUrl%7EE004%7EE005ResponseData%7EE004full%7EE005PrepareScript%7EE004%7EE003%7EE002%7EE003";
    
    const COOKIE_LOGINXSRFERP = 'sap-login-XSRF_ERP';
    const COOKIE_SAPUSERCONTEXT = 'sap-usercontext';
    const COOKIE_PHPSESSID = 'PHPSESSID';
    const COOKIE_MYSAPSSO2 = 'MYSAPSSO2';
     
    const USER_AGENT = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.98 Safari/537.36';   
    const PATTERN_HASH = '/hash\s*=\s*["\']([0-9a-f]{16,64})[\'"]/i';
    
    private function __construct() {}
    
    /**
     * // TODO May need escaping.
     * @param $cookies A set of cookies, either a Requests_Cookie_Jar or an associative array with names and values.
     * @return string THe cookies in serialized form.
     */
    public static function serializeCookies($cookies) : string {
        $res = [];
        if ($cookies instanceof Requests_Cookie_Jar) {
            foreach ($cookies as $name => $cookie) {
                array_push($res, $cookie->format_for_header());
            }
        }
        else {
            foreach ($cookies as $name => $value) {
                array_push($res, sprintf('%s=%s', $name, $value));
            }
        }
        return implode('; ', $res);
    }
    
    public static function createLoginData(Requests_Response $response) : array {
        $html = $response->body ?? '';
        $data = array();
        
        // Get all (hidden) input fields we need to send.
        try {
            $crawler = (new Crawler($html))->filter("form[name=loginForm] input");
        }
        catch (Throwable $e) {
            error_log("Login page not valid html: $e");
            error_log($html);
            throw new CampusDualException("Cannot perform login, login page is not valid HTML.");            
        }
        
        // Add username and password.
        foreach ($crawler as $node) {
            $name = $node->getAttribute('name');
            $value = $node->getAttribute('value');
            $data[$name] = $value;
        };
        
        return $data;
    }

    public static function assertCode(Requests_Response $response, int $code) {
        if ($response->status_code !== $code) {
            throw new CampusDualException("Cannot perform action, server responded with a $response->status_code, but expected $code.");
        }
    }
    
    public static function loginGetPhpSessId(CampusDualSession $session) {
        // Obtaining a PHPSESSID.
        $response = Requests::get(CampusDualLoader::BASE_URL, [
            'User-Agent' => CampusDualHelper::USER_AGENT
        ], ['verify' => false]);
        self::assertCode($response, 200);
        $session->extractCookiePhpSessId($response);
    }

    public static function loginObtainToken(CampusDualSession $session) {
        // Obtaining a login token.
        $response = Requests::get(CampusDualLoader::URL_LOGINGET, [
            'User-Agent' => CampusDualHelper::USER_AGENT
        ]);
        self::assertCode($response, 200);
        $session->refreshCookieSapUserContext($response);
        $session->extractCookieLoginXsrfErp($response);
        $session->extractLoginData($response);
    }

    public static function loginSendCredentials(CampusDualSession $session, string $studentId, string $pass) {
        // Sending the login post request.
        $loginData = $session->getLoginData();
        $loginData[self::HEADER_SAPUSER] = (string) $studentId;
        $loginData[self::HEADER_SAPPASSWORD] = $pass;
        $loginData[self::HEADER_SAPEVENTQUEUE] = self::HEADER_SAPEVENTQUEUE_VALUE;
        $session->clearLoginData();
        $response = Requests::post(CampusDualLoader::URL_LOGINPOST,
                ['Cookie' => CampusDualHelper::serializeCookies([
                    self::COOKIE_SAPUSERCONTEXT => $session->getSapUserContext(),
                    self::COOKIE_LOGINXSRFERP => $session->getLoginXsrfErp(),
                ]),
                'User-Agent' => CampusDualHelper::USER_AGENT,
                'Origin' => CampusDualLoader::BASE_URL_SAP
                ],
                $loginData,
                ['follow_redirects' => false]);
        self::assertCode($response, 302);
        $session->clearLoginXsrfErp();
        $session->refreshCookieSapUserContext($response);
        $session->extractCookieMySapSs02($response);        
        $session->extractRedirectUrl($response);
    }

    public static function loginFollowRedirect(CampusDualSession $session) {
        // Following the redirect.
        $response = Requests::get(CampusDualLoader::getPathSap($session->getRedirectUrl()),
                ['Cookie' => CampusDualHelper::serializeCookies([
                    CampusDualHelper::COOKIE_SAPUSERCONTEXT => $session->getSapUserContext(),
                    CampusDualHelper::COOKIE_MYSAPSSO2 => $session->getMySapSsO2()]),
                'User-Agent' => CampusDualHelper::USER_AGENT
                ]);
        $session->clearSapUserContext();
        $session->clearRedirectUrl();
        self::assertCode($response, 200);
    }

    public static function loginRetrieveHashAndMeta(CampusDualSession $session) {
        // Retrieve the hash.
        $response = $session->getWithCredentials(CampusDualLoader::getPath('index/login'));
        self::assertCode($response, 200);
        $session->extractHash($response);
        $session->extractMeta($response);
    }

    public static function logout(CampusDualSession $session) {
        $response = $session->getWithCredentials(CampusDualLoader::getPath('index/logout'));
        self::assertCode($response, 200);
        $session->clear();
    }
}