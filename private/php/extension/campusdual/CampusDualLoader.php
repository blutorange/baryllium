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

namespace Moose\Extension\CampusDual;

use Closure;
use DateTime;
use Doctrine\DBAL\Types\ProtectedString;
use Exception;
use Moose\Entity\Exam;
use Moose\Entity\FieldOfStudy;
use Moose\Entity\Lesson;
use Moose\Entity\TutorialGroup;
use Moose\Entity\User;
use Moose\Util\DebugUtil;
use Moose\Util\UiUtil;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;
use function mb_strpos;

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
     * @param Closure $consumer A function that is passed the CampusDualLoader and the $data.
     * @param mixed $data Passed as the second argument to the consumer.
     * @return mixed Whatever the consumer returns.
     * @throws CampusDualException When the login fails or the requested action could not be performed.
     * @throws Exception Whatever else the consumer throws.
     */
    public static function perform(string $studentId, ProtectedString $pass, $consumer) {
        $loader = new CampusDualLoader($studentId, $pass);
        try {
            return $consumer($loader);
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
    
    /**
     * @throws CampusDualException When the credentials are not correct.
     */
    public function assertValidity() {
        $this->assertOpen();
        $this->getLogin();
    }
    
    public function getUser() : User {
        $tutGroup = $this->getTutorialGroup();
        $raw = $this->getMetaRaw()['name'];
        $matches = [];
        if (\preg_match("/(.+?),(.+?)\\((\d{7})\\)/u", $raw, $matches) !== 1) {
            throw new CampusDualException("Could not extract username form $raw.");
        }
        $first = \trim($matches[2]);
        $last = \trim($matches[1]);
        $id = \trim($matches[3]);
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
     * @param mixed $start DateTime or unix timestamp in seconds. Default is 01/01/1970, 12:00.
     * @param mixed $end DateTime or unix timestamp in seconds. Default is 01/01/9999, 12:00.
     * @return array JSON with the data.
     * @throws CampusDualException When we cannot retrieve the data.
     */
    public function getTimeTableRaw($start = 1, $end = null) {
        $this->assertOpen();
        $end = $end ?? new DateTime('9999-01-01');
        $tStart = ($start instanceof DateTime) ? $start->getTimestamp() : $start;
        $tEnd = ($end instanceof DateTime) ? $end->getTimestamp() : $end;
        $future = \time() + 7*24*60*60;
        $session = $this->getLogin();
        $hash = $session->getHash();
        $url = self::getPath("room/json?userid=$this->studentId&hash=$hash&start=$tStart&end=$tEnd&_=$future");
        $response = $session->getWithCredentials($url);
        CampusDualHelper::assertCode($response, 200);
        $json = \json_decode($response->body);
        if ($json === null) {
            throw new CampusDualException('Failed to parse JSON, server returned invalid data.');
        }
        if (!\is_array($json)) {
            throw new CampusDualException('Expected array for time table.');
        }
        return $json;
    }
    
    /**
     * @param mixed $start DateTime or unix timestamp in seconds.  Default is 01/01/1970, 12:00.
     * @param mixed $end DateTime or unix timestamp in seconds. Default is 01/01/9999, 12:00.
     * @return Lesson[]
     * @throws CampusDualException When we cannot retrieve the data.
     */
    public function getTimeTable($start = 1, $end = null) {
        $json = $this->getTimeTableRaw($start, $end);
        return $this->getTimeTableInternal($json);
    }
    
    /**
     * Split for unit tests, as this method does not require a network connection.
     * @param object $json
     * @return Lesson[]
     */
    private function getTimeTableInternal($json) {
        return \array_map(function($jsonObject) {
            return Lesson::fromCampusDualJson($jsonObject);
        }, $json);
    }
    
    /**
     * @return Exam[] The exams as listed on the results page.
     */
    public function getExamResults() {
        $this->assertOpen();
        $url = self::getPath("acwork/index");
        $response = $this->getLogin()->getWithCredentials($url);
        CampusDualHelper::assertCode($response, 200);
        return $this->getExamResultsInternal($response->body ?? '');
    }
    
    /**
     * Split for unit tests, as this method does not require a network connection.
     * @return Exam[]
     */
    public function getExamResultsInternal(string $body) {
        $crawler = new Crawler($body);
        $container = $crawler->filter('#dacwork');
        if ($container->count() !== 1) {
            throw new CampusDualException('Failed to read exam results.');
        }
        return $this->processExamResultTr($container->filter('tr'));
    }

    /** @return Exam[] */
    private function processExamResultTr(Crawler $trList) : array {
        $examList = [];
        $trList->each(function(Crawler $tr) use (& $examList) {
            /* @var $tr Crawler */
            $classString = $tr->attr('class') ?? '';
            if (mb_strpos($classString, 'module') === false && mb_strpos($classString, 'child-of-node') !== false) {
                $tdList = $tr->filter('td');
                if ($tdList->count() !== 8) {
                    throw new CampusDualException("Failed to read exam, expected 8 td but got ${$tdList->count()}.");
                }
                
                $exam = Exam::make()
                    ->setMarkString($tdList->getNode(1)->textContent)
                    ->setMarked(UiUtil::formatToDate('d.m.Y', $tdList->getNode(4)->textContent))
                    ->setAnnounced(UiUtil::formatToDate('d.m.Y', $tdList->getNode(5)->textContent));
                $this->extractTitleAndExamId($exam, $tdList->getNode(0)->textContent);
                $examList []= $exam;
            }
        });
        return $examList;
    }
    
    private function extractTitleAndExamId(Exam $exam, string $text) {
        $matches = [];
        if (\preg_match('/^(.*)\\(\\w*([^-)]{3}-[^-)]{1,15}-[^-)]{1,4})\\w*\\)\\w*$/ui', $text, $matches) !== 1) {
            throw new CampusDualException('Failed to parse exam title and exam id.');
        }
        $exam->setTitle(\str_replace("\xc2\xa0", '', \trim($matches[1])));
        $exam->setExamId($matches[2]);
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
            DebugUtil::log("Failed to perform logout: " . $e);
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
        catch (CampusDualException $expected) {
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