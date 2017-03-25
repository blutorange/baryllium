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
use Requests_Response;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Contains all data of a campus dual session, such as session cookies.
 *
 * @author madgaksha
 */
class CampusDualSession {
    const DEFAULT_SAPUSERCONTEXT = "sap-language=DE&sap-client=100";
    
    private $hash;
    private $phpSessId;
    private $sapUserContext;
    private $loginXsrfErp;
    private $mySapSsO2;
    private $loginData;
    private $redirectUrl;
    private $meta;

    public function __construct() {
    }
    public function getPhpSessId() {
        if (empty($this->phpSessId)) {
            throw new CampusDualException("PHPSESSID requested, but none set.");
        }
        return $this->phpSessId;
    }

    public function getSapUserContext() {
        $cookie = $this->sapUserContext;
        if (empty($cookie)) {
            return self::$DEFAULT_SAPUSERCONTEXT;
        }
        return $cookie;
    }

    public function getLoginXsrfErp() {
        if (empty($this->loginXsrfErp)) {
            throw new CampusDualException("loginXSRF_ERP requested, but none set.");
        }
        return $this->loginXsrfErp;
    }

    public function getMySapSsO2() {
        if (empty($this->mySapSsO2)) {
            throw new CampusDualException("MYSAPSS02 requested, but none set.");
        }        
        return $this->mySapSsO2;
    }

    public function setPhpSessId(string $phpSessId = null) {
        $this->phpSessId = $phpSessId;
    }

    public function setSapUserContext(string $sapUserContext = null) {
        $this->sapUserContext = $sapUserContext;
    }

    public function setLoginXsrfErp(string $loginXsrfErp = null) {
        $this->loginXsrfErp = $loginXsrfErp;
    }

    public function setMySapSsO2(string $MySapSsO2 = null) {
        $this->mySapSsO2 = $MySapSsO2;
    }
    
    public function getHash() {
        if (empty($this->hash)) {
            throw new CampusDualException("Hash requested, but none set.");
        }
        return $this->hash;
    }

    public function setHash(string $hash = null) {
        $this->hash = $hash;
    }
    
    public function getLoginData() {
        if ($this->loginData === null) {
            throw new CampusDualException("Login data requested, but none set.");
        }
        return $this->loginData;
    }

    public function setLoginData(array $loginData = null) {
        $this->loginData = $loginData;
    }
    
    public function getRedirectUrl() {
        if (empty($this->redirectUrl)) {
            throw new CampusDualException("Redirect URL requested, but none set.");
        }
        return $this->redirectUrl;
    }

    public function setRedirectUrl(string $redirectUrl = null) {
        $this->redirectUrl = $redirectUrl;
    }
    
    public function getMeta() {
        if ($this->meta === null) {
            throw new CampusDualException("Meta requested, but none set.");
        }
        return $this->meta;
    }

    public function setMeta(array $meta = null) {
        $this->meta = $meta;
    }
    
    public function clearRedirectUrl() {
        $this->setRedirectUrl(null);
    }
    
    public function clearHash() {
        $this->setHash(null);
    }
    
    public function clearMeta() {
        $this->setMeta(null);
    }
    
    public function clearPhpSessId() {
        $this->setPhpSessId(null);
    }
    
    public function clearMySapSs02() {
        $this->setMySapSsO2(null);
    }

    public function clearLoginData() {
        $this->setLoginData(null);
    }

    public function clearLoginXsrfErp() {
        $this->setLoginXsrfErp(null);
    }
    
    public function clearSapUserContext() {
        $this->setSapUserContext(null);
    }
    
    public function clear() {
        $this->clearLoginXsrfErp();
        $this->clearSapUserContext();
        $this->clearLoginData();
        $this->clearRedirectUrl();
        $this->clearHash();
        $this->clearMySapSs02();
        $this->clearPhpSessId();
        $this->clearMeta();
    }
   
    public function extractCookiePhpSessId(Requests_Response $response) {
        if (!$this->refreshCookiePhpSessId($response)) {
            throw new CampusDualException('Cannot perform login, server did not provide a PHPSESSID cookie.');
        }
    }
    
    public function refreshCookiePhpSessId(Requests_Response $response) : bool {
        $cookiePhpSessId = $response->cookies[CampusDualHelper::COOKIE_PHPSESSID];
        if ($cookiePhpSessId !== null && !empty($cookiePhpSessId->value)) {
            $this->setPhpSessId($cookiePhpSessId->value);
            return true;
        }
        return false;
    }
    
    public function extractCookieLoginXsrfErp(Requests_Response $response) {
        $cookieXsrfErp = $response->cookies[CampusDualHelper::COOKIE_LOGINXSRFERP];
        if ($cookieXsrfErp === null || empty($cookieXsrfErp->value)) {
            throw new CampusDualException('Cannot perform login, server did not provide a login cookie.');
        }
        $this->setLoginXsrfErp($cookieXsrfErp->value);
    }
    
    public function extractCookieMySapSs02(Requests_Response $response) {
        if (!$this->refreshCookieMySapSs02($response)) {
            throw new CampusDualException('Cannot perform login, server did not provide a MYSAPSS02 cookie.');
        }
    }
    
    public function refreshCookieMySapSs02(Requests_Response $response) : bool {
        $cookieMySapSs02 = $response->cookies[CampusDualHelper::COOKIE_MYSAPSSO2];
        if ($cookieMySapSs02 !== null && !empty($cookieMySapSs02->value)) {
            $this->setMySapSsO2($cookieMySapSs02->value);
            return true;
        }
        return false;
    }

    public function refreshCookieSapUserContext(Requests_Response $response) : bool {
        $sapUserContext = $response->cookies[CampusDualHelper::COOKIE_SAPUSERCONTEXT];
        if ($sapUserContext !== null && !empty($sapUserContext->value)) {
            $this->setSapUserContext($sapUserContext->value);
            return true;
        }
        return false;
    }

    public function refreshHash(Requests_Response $response) : bool {
        $body = $response->body;
        $matches = [];
        if (preg_match(CampusDualHelper::PATTERN_HASH, $body, $matches)) {
            $this->setHash($matches[1]);
            return true;
        }
        return false;
    }
    
    public function extractHash(Requests_Response $response) {
        if (!$this->refreshHash($response)) {
            throw new CampusDualException('Did not find a hash.');
        }
    }
    
    public function getWithCredentials(string $url) : Requests_Response {
        $response = Requests::get($url,
                ['Cookie' => CampusDualHelper::serializeCookies([
                    CampusDualHelper::COOKIE_PHPSESSID => $this->getPhpSessId(),
                    CampusDualHelper::COOKIE_MYSAPSSO2 => $this->getMySapSsO2()
                ]),
                'User-Agent' => CampusDualHelper::USER_AGENT
                ],
                ['verify' => false]);
        $this->refreshCookiePhpSessId($response);
        $this->refreshCookieMySapSs02($response);
        return $response;
    }

    public function extractLoginData(Requests_Response $response) {
        $this->setLoginData(CampusDualHelper::createLoginData($response));
    }

    public function extractRedirectUrl(Requests_Response $response) {
        $url = $response->headers['location'];
        if (empty($url)) {
            throw new CampusDualException("Cannot perform login, server did not provide a location to hte login post request.");
        }
        $this->setRedirectUrl($url);
    }
    
    public function extractMeta(Requests_Response $response) {
        $crawler = (new Crawler($response->body))->filter(CampusDualLoader::SELECTOR_STUDY_GROUP);
        $count = $crawler->count();
        if ($count !== 1) {
            throw new CampusDualException("Expected one header td, but found $count.");
        }
        foreach ($crawler as $node) {
            $children = $node->childNodes;
            $len = $children->length;
            if ($len !== 7) {
                throw new CampusDualException("Expected exactly seven header td child nodes, but found $len.");
            }
            $meta = [
                'name' => $children->item(2)->textContent,
                'tutgroup' => $children->item(4)->textContent,
                'fos' => $children->item(6)->textContent
            ];
            $this->setMeta($meta);
            return;
        }
        throw new CampusDualException("Could not extract header, unknown error.");
    }
}