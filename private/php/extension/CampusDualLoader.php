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

use DateTime;
use Doctrine\DBAL\Types\ProtectedString;
use Entity\FieldOfStudy;
use Entity\TutorialGroup;
use Entity\User;
use Throwable;

/**
 * For querying data from CapusDual.
 *
 * @author madgaksha
 */
class CampusDualLoader {
    const BASE_URL = 'https://selfservice.campus-dual.de';
    const BASE_URL_SAP = 'https://erp.campus-dual.de';
    
    const URL_LOGINGET = 'https://erp.campus-dual.de/sap/bc/webdynpro/sap/zba_initss?sap-client=100&uri=https://selfservice.campus-dual.de/index/login';
    const URL_LOGINPOST = 'https://erp.campus-dual.de/sap/bc/webdynpro/sap/zba_initss?uri=https%3a%2f%2fselfservice.campus-dual.de%2findex%2flogin';

    const SELECTOR_STUDY_GROUP = '#studinfo table td';
    
    private $studentId;
    /** @var ProtectedString */
    private $pass;
    private $session;
    private $closed;

    /**
     * Makes sure all sensitive data are removed and we are signed out properly.
     * @param string $studentId Username.
     * @param string $pass Password.
     * @param type $consumer A function that is passed the CampusDualLoader and the $data.
     * @param type $data Passed as the second argument to the consumer.
     * @return type Whatever the consumer returns.
     */
    public static function perform(string $studentId, ProtectedString $pass, $consumer, $data = null) {
        $loader = new CampusDualLoader($studentId, $pass);
        try {
            return $consumer($loader, $data);
        } finally {
            $loader->close();
        }
    }
    
    private function __construct(string $studentId, ProtectedString $pass) {
        $this->studentId = $studentId;
        $this->pass = $pass;
        $this->closed = false;
    }
    
    private function assertOpen() {
        if ($this->closed) {
            throw new CampusDualException('Unable to perform action, CampusDual session closed already.');
        }
    }
    
    public function getMetaRaw() {
        $this->assertOpen();
        return $this->getLogin()->getMeta();
    }
    
    
    public function getUser() : User {
        $tutGroup = $this->getTutorialGroup();
        $raw = $this->getMetaRaw()['name'];
        $matches = [];
        if (preg_match("/(.+?),(.+?)\\((\d{7})\\)/u", $raw, $matches) !== 1) {
            throw new CampusDualException("Could not extract username form $raw.");
        }
        $first = trim($matches[2]);
        $last = trim($matches[1]);
        $id = trim($matches[3]);
        if ($this->studentId !== $id) {
            throw new CampusDualException("Student ID does not match.");
        }
        $user = new User();
        $user->setFirstName($first);
        $user->setLastName($last);
        $user->setStudentId($this->studentId);
        $user->setTutorialGroup($tutGroup);
        return $user;
    }
    
    public function getTutorialGroup() : TutorialGroup {
        $rawTut = $this->getMetaRaw()['tutgroup'];
        $tutgroup = TutorialGroup::valueOf($rawTut);
        $shortName = TutorialGroup::shortName($rawTut);
        $rawFos = $this->getMetaRaw()['fos'];
        $fos = FieldOfStudy::valueOf($rawFos);
        $fos->setShortName($shortName);
        $tutgroup->setFieldOfStudy($fos);
        return $tutgroup;
    }

    /**
     * 
     * @param mixed $start DateTime or unix timestamp.
     * @param mixed $end DateTime or unix timestamp.
     * @return type JSON with the data.
     * @throws CampusDualException When we cannot retrieve the data.
     */
    public function getTimeTableRaw($start, $end) {
        $this->assertOpen();
        $tStart = ($start instanceof DateTime) ? $start->getTimestamp() : $start;
        $tEnd = ($end instanceof DateTime) ? $end->getTimestamp() : $end;
        $future = time() + 7*24*60*60;
        $session = $this->getLogin();
        $hash = $session->getHash();
        $url = $this->getPath("room/json?userid=$this->studentId&hash=$hash&start=$tStart&end=$tEnd&_=$future");
        $response = $session->getWithCredentials($url);
        CampusDualHelper::assertCode($response, 200);
        $json = json_decode($response->body);
        if ($json === null) {
            throw new CampusDualException('Failed to parse JSON, server returned invalid data.');
        }
        return $json;
    }
    
    public function close() {
        if ($this->closed) {
            return;
        }
        try {
            if ($this->session !== null) {
                CampusDualHelper::logout($this->session);
            }
        }
        catch (Throwable $e) {
            error_log("Failed to perform logout: " . $e);
        }
        finally {
            $this->clear();
        }
    }
    
    /**
     * @return CampusDualSession Data required for authentication.
     */
    private function getLogin() : CampusDualSession {
        if ($this->session === null) {
            $this->session = $this->doLogin();
        }
        return $this->session;
    }

    /**
     * The login process consists of five steps:
     * <ol>
     *  <li>
     *    Sending a GET to <code>https://selfservice.campus-dual.de/</code> and
     *    fetching the <code>PHPSESSID</code> cookie. (path=/).
     *  </li>
     *  <li>Sending a GET to the SAP login page. This creates a new login cookie
     *      token <code>sap-login-XSRF_ERP</code> (path=/; domain=.erp.campus-dual.de)
     *      we need to use in the next step. The response body also contains a
     *      form with several hidden field. These fields need to be submitted as
     *      well. We should also save the cookie <code>sap-usercontext</code>
     *      for later use.
     *  </li>
     *  <li>Sending a POST to the SAP login page, with all the data we got
     *      above, as well as the username (eg 3002984) and the password. The
     *      server responds with a 302 REDIRECT and gives us the session cookie
     *      <coode>MYSAPSSO2</code> (path=/; domain=.campus-dual.de). We can now
     *      discard the login cookie <code>sap-login-XSRF_ERP</code>.
     *  </li>
     *  <li>Sending a GET to the redirect URL from above. Here we need to send
     *      the <coode>MYSAPSSO2</code> and <code>sap-usercontext</code>
     *      cookies. May not be strictly necessary, but not doing so might be 
     *      a good way of telling the system that we are a robot.
     *  </li>
     *  <li>JavaScript would now redirect us back to the CampusDual home page.
     *      We send a GET with the MYSAPSSO2 and PHPSESSID cookies to
     *      <code>https://selfservice.campus-dual.de/index/login</code>
     *      To retrieve other data here, we need a hash that can be found only
     *      within the JavaScript contained in the response we get. The line
     *      looks as follows:
     *      <code>hash="80b9f7eee12f785849dd092e81c7d3ff";user="3002591";</code>
     *      We retrieve this hash with a regex...
     *  </li>
     * </ol>
     * <p>
     * Also, we need to disable SSL verification as the page currently does not
     * use valid certificates... just try going to the login path with Chrome
     * on Android and watch the security warnings :(
     * </p><p>
     * Furthermore, we need to set the user agent to some modern browser or we
     * receive a 500 <code>Dieser Browser wird nicht unterstützt</code>.
     * </p>
     * @throws CampusDualException
     */
    private function doLogin() : CampusDualSession {
        $session = new CampusDualSession();
        
        try {
            CampusDualHelper::loginGetPhpSessId($session);
            CampusDualHelper::loginObtainToken($session);
            CampusDualHelper::loginSendCredentials($session, $this->studentId, $this->pass);               
            CampusDualHelper::loginFollowRedirect($session);
            CampusDualHelper::loginRetrieveHashAndMeta($session);
        }
        catch (CampusDualSession $expected) {
            throw $expected;
        }
        catch (Throwable $unexpected) {
            throw new CampusDualException("Unexpected exception occurred during login: " . $unexpected);
        }

        return $session;
    }   

    private function clear() {
        if ($this->session !== null) {
            $this->session->clear();
        }
        $this->session = null;
        $this->studentId = null;
        $this->pass = null;
        $this->closed = true;
    }

    
    public static function getPath(string $path) : string {
        return self::BASE_URL . '/' . $path;
    }
    
    public static function getPathSap(string $path) : string {
        return self::BASE_URL_SAP . '/' . $path;
    }
}