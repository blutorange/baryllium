<?php

namespace Extension\CampusDual;

use DateTime;
use Requests;
use Requests_Cookie_Jar;
use Requests_Response;
use Symfony\Component\DomCrawler\Crawler;
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
    
    private $snumber;
    private $pass;
    private $session;
    private $closed;

    /**
     * Makes sure all sensitive data are removed and we are signed out properly.
     * @param int $snumber Username.
     * @param string $pass Password.
     * @param type $consumer A function that is passed the CampusDualLoader and the $data.
     * @param type $data Passed as the second argument to the consumer.
     * @return type Whatever the consumer returns.
     */
    public static function perform(int $snumber, string $pass, $consumer, $data = null) {
        $loader = new CampusDualLoader($snumber, $pass);
        try {
            return $consumer($loader, $data);
        } finally {
            $loader->close();
        }
    }
    
    private function __construct(int $snumber, string $pass) {
        $this->snumber = $snumber;
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
    
    public function getStudyGroup() : \Model\StudyGroup {
        $raw = $this->getMetaRaw()['sgroup'];
        return \Model\StudyGroup::valueOf($raw);
        
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
        if ($tStart === null) {
            error_log('No start time given, using now.');
            $tStart = time();
        }
        if ($tEnd === null) {
            error_log('No end time given, using now plus a week.');
            $tEnd = time() + 7*24*60*60;
        }
        $future = time() + 7*24*60*60;
        $session = $this->getLogin();
        $hash = $session->getHash();
        $url = $this->getPath("room/json?userid=$this->snumber&hash=$hash&start=$tStart&end=$tEnd&_=$future");
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
        if ($this->session === null) {
            $this->snumber = null;
            $this->pass = null;
            $this->closed = true;
            return;
        }
        try {
            CampusDualHelper::logout($this->session);
        }
        catch (Throwable $e) {
            error_log("Failed to perform logout: " . $e);
        }
        finally {
            if ($this->session !== null) {
                $this->session->clear();
            }
            $this->session = null;
            $this->snumber = null;
            $this->pass = null;
            $this->closed = true;
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
            CampusDualHelper::loginSendCredentials($session, $this->snumber, $this->pass);               
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

    public static function getPath(string $path) : string {
        return self::BASE_URL . '/' . $path;
    }
    
    public static function getPathSap(string $path) : string {
        return self::BASE_URL_SAP . '/' . $path;
    }
}

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
                'sgroup' => $children->item(4)->textContent,
                'course' => $children->item(6)->textContent
            ];
            $this->setMeta($meta);
            return;
        }
        throw new CampusDualException("Could not extract header, unknown error.");
    }
}

class CampusDualHelper {

    const HEADER_SAPUSER = 'sap-user';
    const HEADER_SAPPASSWORD = 'sap-password';

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
        $response = Requests::get(CampusDualLoader::BASE_URL, array(), ['verify' => false]);
        self::assertCode($response, 200);
        $session->extractCookiePhpSessId($response);
    }

    public static function loginObtainToken(CampusDualSession $session) {
        // Obtaining a login token.
        $response = Requests::get(CampusDualLoader::URL_LOGINGET);
        self::assertCode($response, 200);
        $session->refreshCookieSapUserContext($response);
        $session->extractCookieLoginXsrfErp($response);
        $session->extractLoginData($response);
    }

    public static function loginSendCredentials(CampusDualSession $session, int $snumber, string $pass) {
        // Sending the login post request.
        $loginData = $session->getLoginData();
        $loginData[self::HEADER_SAPUSER] = (string) $snumber;
        $loginData[self::HEADER_SAPPASSWORD] = $pass;
        $session->clearLoginData();
        $response = Requests::post(CampusDualLoader::URL_LOGINPOST,
                ['Cookie' => CampusDualHelper::serializeCookies([
                    self::COOKIE_SAPUSERCONTEXT => $session->getSapUserContext(),
                    self::COOKIE_LOGINXSRFERP => $session->getLoginXsrfErp(),
                ])],
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