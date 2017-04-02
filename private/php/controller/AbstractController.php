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

namespace Moose\Controller;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use League\Plates\Engine;
use Moose\Context\Context;
use Moose\Context\EntityManagerProviderInterface;
use Moose\Context\PortalSessionHandler;
use Moose\Context\TemplateEngineProviderInterface;
use Moose\Context\TranslatorProviderInterface;
use Moose\Util\CmnCnst;
use Moose\Util\PlaceholderTranslator;
use Moose\ViewModel\Message;
use Moose\ViewModel\MessageInterface;
use Moose\Web\HttpRequest;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponse;
use Moose\Web\HttpResponseInterface;
use Throwable;

/**
 * Description of AbstractController
 *
 * @author madgaksha
 */
abstract class AbstractController implements TranslatorProviderInterface,
        EntityManagerProviderInterface, TemplateEngineProviderInterface {

    const REQUIRE_LOGIN_SADMIN = 0;
    const REQUIRE_LOGIN_USER = 1;
    const REQUIRE_LOGIN_WHENPOSSIBLE = 2;
    const REQUIRE_LOGIN_NEVER = 3;
   
    /** @var HttpResponseInterface */
    private $response;
    
    /** @var HttpResponseInterface */
    private $request;

    public function __construct() {
        $this->response = new HttpResponse();
        $this->response->setKeepAlive(true);
        $this->request = HttpRequest::createFromGlobals();
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
        return Context::getInstance();
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

    public function getEm(int $i = 0): EntityManagerInterface {
        return $this->getContext()->getEm($i);
    }

    private final function processRequest() {
        switch ($this->getRequest()->getHttpMethod()) {
            case 'POST':
                $this->doPost($this->getResponse(), $this->getRequest());
                break;
            case 'GET':
                $this->doGet($this->getResponse(), $this->getRequest());
                break;
            default:
                $this->doOther($this->getResponse(), $this->getRequest(), $this->getRequest()->getMethod());
                break;
        }
    }
   
    public final function process() {
        $renderedError = false;
        try {
            if ($this->getRequiresLogin() !== self::REQUIRE_LOGIN_NEVER) {
                $this->getSessionHandler()->initSession();
            }
            $this->processInternal();
        } catch (DBALException $driverException) {
            \error_log("Failed during database transaction: $driverException");
            $this->rollback();
            $this->handleUnhandledError($driverException, true);
            $renderedError = true;
        } catch (Throwable $e) {
            \error_log("Failed to handle request: $e");
            $this->rollback();
            $this->handleUnhandledError($e);
            $renderedError = true;
        } finally {
            $this->cleanup(!$renderedError);
        }
        $this->sendResponse();
    }
    
    private function sendResponse() {
        try {
            $this->getResponse()->send();
        }
        catch (Throwable $sendingException) {
            $this->response = new HttpResponse();
            $this->handleUnhandledError($sendingException);
            try {
                $this->response->send();
            }
            catch (Throwable $anotherSendingException) {
                \error_log("Failed to send response: $anotherSendingException");
                \http_response_code(500);
                echo \json_encode(['error' => [
                    'message' => 'Internal server error.',
                    'details' => 'Moo, moo, moose: ' . \get_class($anotherSendingException),
                    'severity' => MessageInterface::TYPE_DANGER
                ]]);
            }
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
            catch (PermissionsException $ignored) {
                $this->makeAccessDeniedResponse();
            }
        }
    }
    
    private function makeLoginResponse(bool $needsSiteAdmin, bool $needsLocalAdmin) {
        $response = new HttpResponse();
        $loginPage = $this->getContext()->getServerPath(CmnCnst::PATH_LOGIN_PAGE);
        $redirectUrl = $_SERVER['PHP_SELF'];
        if (\array_key_exists('QUERY_STRING', $_SERVER)) {
            $redirectUrl .= '?' . $_SERVER['QUERY_STRING'];
        }
        $url = $loginPage . "?" . \http_build_query([
            CmnCnst::URL_PARAM_REDIRECT_URL => $redirectUrl
        ]);
        $response->setRedirect($url);
        $this->response = $response;
        return $response;
    }
    
    private function makeAccessDeniedResponse() {
        $response = new HttpResponse();
        $response->addMessage(Message::dangerI18n('accessdenied.message', 'accessdenied.detail', $this->getTranslator()));
        $response->appendTemplate('t_access_denied', $this->getEngine(), $this->getTranslator(), $this->getLang());
        $this->response = $response;
        return $response;
    }
    
    private function cleanup(bool $renderError) {
        $this->getSessionHandler()->closeSession();
        try {
            $this->getContext()->closeEm();
        } catch (Throwable $e) {
            \error_log('Failed to close entity manager: ' . $e);
            if ($renderError) {
                $this->handleUnhandledError($e);
            }
        }
    }

    private final function handleUnhandledError(Throwable $e, bool $isDbError = false) {
        try {
            $isProductionEnvironment = !($this->getContext()->isMode(Context::MODE_DEVELOPMENT) || $this->getContext()->isMode(Context::MODE_TESTING));
        }
        catch (Throwable $t) {
            $isProductionEnvironment = true;
        }
        if (!$isProductionEnvironment) {
            \error_log($e);
        }
        $this->renderUnhandledError($e, $isProductionEnvironment, $isDbError);
    }
    
    /**
     * Overridden by the AbstractRestServlet, but otherwise there should be
     * not reason to do so. Customize what happens in case of an error.
     * @param Throwable $e
     * @param string $template
     * @param string $title
     * @param string $messsageDetail
     */
    protected function renderUnhandledError(Throwable $e, bool $isProductionEnvironment, bool $isDbError) {
        $messsageDetail = $isDbError ? 'error.database.message' : 'error.unexpected.message';
        $title = $isDbError ? 'error.database.title' : 'error.unexpected.title';
        $suf = " in " . $e->getFile() . " on line " . $e->getLine();
        $message = $isProductionEnvironment ? $this->getTranslator()->gettext($messsageDetail) : $e->getMessage() . $suf;
        $detail = $isProductionEnvironment ? \get_class($e) : $e->getTraceAsString();
        try {
            $out = $this->getContext()->getEngine()->render(CmnCnst::TEMPLATE_UNHANDLED_ERROR, [
                'message' => $message,
                'detail' => $detail,
                'title' => $title,
                'i18n' => $this->getSessionHandler()->getTranslator()
            ]);
        }
        catch (Throwable $e) {
            \error_log('Failed to render error template ' . $e);
            $m = \htmlspecialchars($message . "\n\n" . $detail);
            $out = "<html><head><title>Unhandled error</title><meta charset=\"UTF-8\"></head><body><h1>Failed to render template, check your configuration file.</h1><pre>$m</pre></body></html>";
        }
        $this->getResponse()->setContent($out);
    }

    private function rollback() {
        try {
            $this->getContext()->rollbackEm();
        }
        catch (Throwable $e) {
            \error_log('Failed to rollback transaction: ' . $e);
        }
    }

    protected function getResponse() : HttpResponseInterface {
        return $this->response;
    }

    protected function getRequest() : HttpRequestInterface {
        return $this->request;
    }

    protected abstract function doGet(HttpResponseInterface $response, HttpRequestInterface $request);

    protected abstract function doPost(HttpResponseInterface $response, HttpRequestInterface $request);
    
    /**
     * Override this to handle other methods.
     * @param HttpResponseInterface $response
     */
    protected function doOther(HttpResponseInterface $response, HttpRequestInterface $request, string $method) {
        $response->setStatusCode(HttpResponse::HTTP_BAD_REQUEST);
        $response->setContent("Unknown method $method.");
    }
    
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