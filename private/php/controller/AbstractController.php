<?php

/* The 3-Clause BSD License
 * 
 * SPDX short identifier: BSD-3-Clause
 *
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

namespace Controller;

use Context;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\EntityManager;
use League\Plates\Engine;
use PortalSessionHandler;
use Throwable;
use Ui\Message;
use Ui\PlaceholderTranslator;
use Util\CmnCnst;

/**
 * Description of AbstractController
 *
 * @author madgaksha
 */
abstract class AbstractController {

    const REQUIRE_LOGIN_SADMIN = 0;
    const REQUIRE_LOGIN_USER = 1;
    const REQUIRE_LOGIN_WHENPOSSIBLE = 2;
    const REQUIRE_LOGIN_NEVER = 3;

    /** @var Context */
    private $context;
    
    private $data;
   
    /** @var HttpResponseInterface */
    private $response;
    
    /** @var array Warning or info messages to be displayed. */
    private $messages;

    public function __construct(Context $context = null) {
        $this->response = new HttpResponse();
        $this->messages = [];
        $this->context = $context ?? $GLOBALS['context'];
    }
    
    public function getSessionHandler(): PortalSessionHandler {
        return $this->getContext()->getSessionHandler();
    }
    
    public function getTranslator(): PlaceholderTranslator {
        return $this->getSessionHandler()->getTranslator();
    }
    
    public function getLang() : string {
        return $this->getSessionHandler()->getLang();
    }

    public function getContext(): Context {
        return $this->context;
    }
    
    public function getFileData(string $name) {
        $file = @$_FILES[$name];
        return $file !== null ? file_get_contents($file['tmp_name']) : null;
    }

    /**
     * @param string $controllerPath Name of the controller, relative to the /public/controller directory.
     * @return string The path to the controller php on the server.
     */
    public function getController(string $controllerPath) : string {
        return $this->getContext()->getServerPath('public/controller/' . $controllerPath);
    }

    /**
     * @param string $servletPath Name of the servlet, relative to the /public/servlet directory.
     * @return string The path to the servlet php on the server.
     */    
    public function getServlet(string $servletPath) {
        return $this->getContext()->getServerPath('public/servlet/' . $servletPath);
    }

    public function getEngine(): Engine {
        return $this->getContext()->getEngine();
    }

    public function getEm(): EntityManager {
        return $this->getContext()->getEm();
    }

    public function getData(): array {
        return $this->data;
    }

    private final function processRequest() {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $this->data = array_merge(isset($_GET) ? $_GET : [], isset($_POST) ? $_POST : []);
                $this->doPost($this->getResponse());
                break;
            case 'GET':
                $this->data = $_GET;
                $this->doGet($this->getResponse());
                break;
            default:
                $this->getResponse()->setStatusCode(HttpResponse::HTTP_BAD_REQUEST);
                $this->getResponse()->setContent("Unknown method " . $_SERVER['REQUEST_METHOD']);
                break;
        }
    }
    
    /**
     * Renders a template. Automatically adds global messages to be shown as
     * well as the current language and translator. To override with your own
     * messages or locale, simple* add an entry for the key <pre>messages</pre>
     * or <pre>locale</pre> in the data array.
     * @param string Name of the template to render.
     * @param array Additional data to be passed to the template.
     */
    protected function renderTemplate(string $templateName, array $data = null) {
        $this->getResponse()->appendTemplate($this->getEngine(), $this->getTranslator(), $this->getLang(), $templateName, $data);
    }
   
    /**
     * This will display the added messages on the rendered view page.
     * @param array An array consisting of \Ui\Message, the messages to be added.
     * @see AbstractController::addMessage()
     */
    protected function addMessages(array & $messages) {
        $this->getResponse()->addMessages($messages);
    }

    /**
     * @param string $name Key of the parameter to retrieve.
     * @return string Value of the parameter, or null when there is no such parameter.
     */
    protected function getParam(string $name, $default = null) {
        if (!array_key_exists($name, $this->getData())) {
            return $default;
        }
        return $this->getData()[$name];
    }
  
    protected function getParamInteger(string $name, $default = null) {
        $val = $this->getParam($name);
        if ($val === null) {
            return $default;
        }
        $res = filter_var($val, FILTER_VALIDATE_INT);
        if ($res === false) {
            return $default;
        }
        return intval($val, 10);
    }
       
    /**
     * @param string $name Parameter whose value to retrieve.
     * @return bool Whether the parameters is set to a truthy or falsey value. False when there is no such parameters.
     */
    protected function getParamBool(string $name) : bool {
        $val = $this->getParam($name);
        if ($val === null) {
            return false;
        }
        if (strcasecmp($val, "true") || strcasecmp($val, "on") || $val === '1') {
            return true;
        }
        return false;
    }
    
    public final function process() {
        $renderedError = false;
        try {
            if ($this->getRequiresLogin() !== self::REQUIRE_LOGIN_NEVER) {
                $this->getSessionHandler()->initSession();
            }
            $this->processInternal();
            $this->getResponse()->send();
        } catch (DriverException $driverException) {
            $this->rollback();
            $this->renderUnhandledError($driverException, 'unhandledError', 'error.database.title', 'error.database.message');
            $renderedError = true;
        } catch (\Throwable $e) {
            $this->rollback();
            $this->renderUnhandledError($e, 'unhandledError', 'error.unexpected.title', 'error.unexpected.message');
            $renderedError = true;
        } finally {
            $this->cleanup(!$renderedError);       
        }
    }
    
    private function processInternal() {
        if ($this->getRequiresLogin() === self::REQUIRE_LOGIN_SADMIN &&
                !$this->getSessionHandler()->getUser()->getIsSiteAdmin()) {
            $this->makeLoginResponse(true, false);
        }
        else if ($this->getRequiresLogin() === self::REQUIRE_LOGIN_USER &&
                !$this->getSessionHandler()->getUser()->isValid()) {
            $this->makeLoginResponse(false, false);
        }
        else {
            try {
                $this->processRequest();
            }
            catch (PermissionsException $e) {
                $this->makeAccessDeniedResponse();
            }
        }
    }
    
    private function makeLoginResponse(bool $needsSiteAdmin, bool $needsLocalAdmin) {
        $response = new HttpResponse();
        $loginPage = $this->getContext()->getServerPath(CmnCnst::PATH_LOGIN_PAGE);
        $redirectUrl = $_SERVER['PHP_SELF'];
        if (array_key_exists('QUERY_STRING', $_SERVER)) {
            $redirectUrl .= '?' . $_SERVER['QUERY_STRING'];
        }
        $url = $loginPage . "?" . http_build_query([
            CmnCnst::URL_PARAM_REDIRECT_URL => $redirectUrl
        ]);
        $response->setRedirect($url);
        $this->response = $response;
        return $response;
    }
    
    private function makeAccessDeniedResponse() {
        $response = new HttpResponse();
        $response->addMessage(Message::dangerI18n('accessdenied.message', 'accessdenied.detail', $this->getTranslator()));
        $response->appendTemplate($this->getEngine(), $this->getTranslator(), $this->getLang(), 't_access_denied');
        $this->response = $response;
        return $response;
    }
    
    private function cleanup(bool $renderError) {
        $this->getSessionHandler()->closeSession();
        try {
            if ($this->getContext()->isEmInitialized() && $this->getEm()->isOpen()) {
                $this->getEm()->flush();
                $this->getContext()->closeEm();
            }
        } catch (\Throwable $e) {
            error_log('Failed to close entity manager: ' . $e);
            if ($renderError) {
                $this->renderUnhandledError($e, 'unhandledError', 'error.unexpected.title', 'error.unexpected.message');
            }
        }
    }

    private final function renderUnhandledError($e, string $template, string $title, string $messsageDetail) {
        $suf = " in " . $e->getFile() . " on line " . $e->getLine();
        try {
            $isProd = !($this->getContext()->isMode(Context::$MODE_DEVELOPMENT) || $this->getContext()->isMode(Context::$MODE_TESTING));
        }
        catch (Throwable $t) {
            $isProd = true;
        }
        $message = $isProd ? $this->getTranslator()->gettext($messsageDetail) : $e->getMessage() . $suf;
        $detail = $isProd ? get_class($e) : $e->getTraceAsString();
        $out;
        try {
            $out = $this->getContext()->getEngine()->render($template, [
                'message' => $message,
                'detail' => $detail,
                'title' => $title,
                'i18n' => $this->getSessionHandler()->getTranslator()
            ]);
        }
        catch (\Throwable $e) {
            error_log('Failed to render error template ' . $e);
            $m = htmlspecialchars($message . "\n\n" . $detail);
            $out = "<html><head><title>Unhandled error</title><meta charset=\"UTF-8\"></head><body><h1>Failed to render template, check your configuration file.</h1><pre>$m</pre></body></html>";
        }
        if (!$isProd) {
            error_log($e);
        }
        echo $out;
    }

    private function rollback() {
        try {
            if ($this->getContext()->isEmInitialized() && $this->getEm()->isOpen()) {
                $this->getEm()->rollback();
            }
        }
        catch (\Throwable $e) {
            error_log('Failed to rollback transaction: ' . $e);
        }
    }

    protected function getResponse() : HttpResponseInterface {
        return $this->response;
    }

    protected abstract function doGet(HttpResponseInterface $response);

    protected abstract function doPost(HttpResponseInterface $response);
    
    /**
     * Override for public pages.
     * @return int Whether these pages requires a user who is signed in. Should
     * be one of <code>AbstractCoontroller::REQUIRE_LOGIN_ALWAYS</code>,
     * <code>AbstractCoontroller::REQUIRE_LOGIN_WHENPOSSIBLE</code>,
     * or <code>AbstractCoontroller::REQUIRE_LOGIN_NEVER</code>.
     */
    protected function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_USER;
    }
}
