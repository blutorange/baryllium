<?php

namespace Extension\CampusDual;

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
    private static $BASE_URL = 'https://selfservice.campus-dual.de';
    private static $BASE_URL_SAP = 'https://erp.campus-dual.de';
    
    private static $URL_LOGINGET = 'https://erp.campus-dual.de/sap/bc/webdynpro/sap/zba_initss?sap-client=100&uri=https://selfservice.campus-dual.de/index/login';
    private static $URL_LOGINPOST = 'https://erp.campus-dual.de/sap/bc/webdynpro/sap/zba_initss?uri=https%3a%2f%2fselfservice.campus-dual.de%2findex%2flogin';
       
    private $snumber;
    private $pass;
    
    public function __construct(int $snumber, string $pass) {
        $this->snumber = $snumber;
        $this->pass = $pass;
    }
    
    /**
     * @throws CampusDualException When credentials are invalid, the server is down, or no course can be found.
     */
    public function getCourse() : Course {
        $session = $this->getLogin();
        var_dump($session);
    }
    
    /**
     * Later we can cache the login.
     * @return CampusDualSession Data required for authentication.
     */
    private function getLogin() : CampusDualSession {
        return $this->doLogin();
    }

    /**
     * The login process consists of three steps:
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
        
        
        // Obtaining a PHPSESSID.
        $responseDual = Requests::get(self::$BASE_URL, array(), ['verify' => false]);
        $this->assertCode($responseDual, 200);
        $session->extractCookiePhpSessId($responseDual);
        
        // Obtaining a login token.
        $responseGet = Requests::get(self::$URL_LOGINGET);        
        $this->assertCode($responseGet, 200);
        $session->refreshCookieSapUserContext($responseGet);
        $session->extractCookieLoginXsrfErp($responseGet);
        
        // Sending the login post request.
        $responsePost = Requests::post(self::$URL_LOGINPOST,
                ['Cookie' => CampusDualHelper::serializeCookies($responseGet->cookies)],
                CampusDualHelper::createLoginData($responseGet, $this->snumber, $this->pass),
                ['follow_redirects' => false]);
        $this->assertCode($responsePost, 302);
        $session->clearCookieLoginXsrfErp();
        $session->refreshCookieSapUserContext($responsePost);
        $session->extractCookieMySapSs02($responsePost);
        
        // Following the redirect.
        $redirectUrl = $responsePost->headers['location'];
        if (empty($redirectUrl)) {
            throw new CampusDualException("Cannot perform login, server did not provide a location to hte login post request.");
        }
        $responseRedirect = Requests::get($this->getPathSap($redirectUrl),
                ['Cookie' => CampusDualHelper::serializeCookies([
                    CampusDualHelper::$COOKIE_SAPUSERCONTEXT => $session->getSapUserContext(),
                    CampusDualHelper::$COOKIE_MYSAPSSO2 => $session->getMySapSsO2()]),
                'User-Agent' => CampusDualHelper::$USER_AGENT
                ]);
        
        $session->clearCookieSapUserContext();
        $this->assertCode($responseRedirect, 200);
        
        // Retrieving the hash.
        $responseFinal = $session->getWithCredentials($this->getPath('index/login'));
        $this->assertCode($responseFinal, 200);
        $session->extractHash($responseFinal);

        var_dump("===FINAL===");
        var_dump($session);
        var_dump($responseFinal);
        var_dump($responseFinal->body);

        
        return $session;
    }

    private function getPath(string $path) : string {
        return self::$BASE_URL . '/' . $path;
    }
    
    private function getPathSap(string $path) : string {
        return self::$BASE_URL_SAP . '/' . $path;
    }
    
    private function assertCode(Requests_Response $response, int $code) {
        if ($response->status_code !== $code) {
            throw new CampusDualException("Cannot perform action, server responded with a $response->status_code, but expected $code.");
        }
    }
}

class CampusDualSession {
    private static $DEFAULT_SAPUSERCONTEXT = "sap-language=DE&sap-client=100";
    
    private $hash;
    private $phpSessId;
    private $sapUserContext;
    private $loginXsrfErp;
    private $mySapSsO2;

    public function __construct() {
    }
    public function getPhpSessId() {
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
        return $this->loginXsrfErp;
    }

    public function getMySapSsO2() {
        return $this->mySapSsO2;
    }

    public function setPhpSessId($phpSessId) {
        $this->phpSessId = $phpSessId;
    }

    public function setSapUserContext($sapUserContext) {
        $this->sapUserContext = $sapUserContext;
    }

    public function setLoginXsrfErp($loginXsrfErp) {
        $this->loginXsrfErp = $loginXsrfErp;
    }

    public function setMySapSsO2($MySapSsO2) {
        $this->mySapSsO2 = $MySapSsO2;
    }
    
    public function getHash() {
        return $this->hash;
    }

    public function setHash($hash) {
        $this->hash = $hash;
    }
    
    public function clearCookieLoginXsrfErp() {
        $this->setLoginXsrfErp(null);
    }
    
    public function clearCookieSapUserContext() {
        $this->setSapUserContext(null);
    }
    
    public function extractCookiePhpSessId(Requests_Response $response) {
        if (!$this->refreshCookiePhpSessId($response)) {
            throw new CampusDualException('Cannot perform login, server did not provide a PHPSESSID cookie.');
        }
    }
    
    public function refreshCookiePhpSessId(Requests_Response $response) : bool {
        $cookiePhpSessId = $response->cookies[CampusDualHelper::$COOKIE_PHPSESSID];
        if ($cookiePhpSessId !== null && !empty($cookiePhpSessId->value)) {
            $this->setPhpSessId($cookiePhpSessId->value);
            return true;
        }
        return false;
    }
    
    public function extractCookieLoginXsrfErp(Requests_Response $response) {
        $cookieXsrfErp = $response->cookies[CampusDualHelper::$COOKIE_LOGIN];
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
        $cookieMySapSs02 = $response->cookies[CampusDualHelper::$COOKIE_MYSAPSSO2];
        if ($cookieMySapSs02 !== null && !empty($cookieMySapSs02->value)) {
            $this->setMySapSsO2($cookieMySapSs02->value);
            return true;
        }
        return false;
    }

    public function refreshCookieSapUserContext(Requests_Response $response) : bool {
        $sapUserContext = $response->cookies[CampusDualHelper::$COOKIE_SAPUSERCONTEXT];
        if ($sapUserContext !== null && !empty($sapUserContext->value)) {
            $this->setSapUserContext($sapUserContext->value);
            return true;
        }
        return false;
    }

    public function refreshHash(Requests_Response $response) : bool {
        $body = $response->body;
        $matches = [];
        if (preg_match(CampusDualHelper::$PATTERN_HASH, $body, $matches)) {
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
                    CampusDualHelper::$COOKIE_PHPSESSID => $this->getPhpSessId(),
                    CampusDualHelper::$COOKIE_MYSAPSSO2 => $this->getMySapSsO2()
                ]),
                'User-Agent' => CampusDualHelper::$USER_AGENT
                ],
                ['verify' => false]);
        $this->refreshCookiePhpSessId($response);
        $this->refreshCookieMySapSs02($response);
        return $response;
    }
}

class CampusDualHelper {
    public static $COOKIE_LOGIN = 'sap-login-XSRF_ERP';
    public static $COOKIE_SAPUSERCONTEXT = 'sap-usercontext';
    public static $COOKIE_PHPSESSID = 'PHPSESSID';
    public static $COOKIE_MYSAPSSO2 = 'MYSAPSSO2';
     
    public static $USER_AGENT = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.98 Safari/537.36';
    
    public static $PATTERN_HASH = '/hash\s*=\s*["\']([0-9a-f]{16,64})[\'"]/i';
    
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
    
    public static function createLoginData(Requests_Response $response, $snumber, $pass) : array {
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
        
        $data['sap-user'] = (string) $snumber;
        $data['sap-password'] = $pass;
        return $data;
//'SAPEVENTQUEUE' => Form_Submit~E002Id~E004SL__FORM~E003~E002ClientAction~E004submit~E005ActionUrl~E004~E005ResponseData~E004full~E005PrepareScript~E004~E003~E002~E003,
    }
}