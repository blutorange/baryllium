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
use Doctrine\ORM\EntityManager;
use League\Plates\Engine;
use PortalSessionHandler;
use Throwable;
use Ui\Message;
use Ui\PlaceholderTranslator;

/**
 * Description of AbstractController
 *
 * @author madgaksha
 */
abstract class AbstractController {

    /** @var Context */
    protected $context;
    protected $data;
    protected $sessionHandler;
    protected $outputBody;
    
    /** @var array Warning or info messages to be displayed. */
    protected $messages;
    
    private $get;

    public function __construct(Context $context = null) {
        $this->outputBody = '';
        $this->messages = [];
        $this->context = $context ?? $GLOBALS['context'];
        $this->sessionHandler = new PortalSessionHandler($this->context);
    }
    
    public function getSessionHandler(): PortalSessionHandler {
        return $this->sessionHandler;
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

    public abstract function doGet();

    public abstract function doPost();

    private final function processReq() {
        $this->get = $_GET;
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $this->data = $_POST;
                $this->doPost();
                break;
            case 'GET':
                $this->data = $_GET;
                $this->doGet();
                break;
            default:
                echo "Unknown method " . $_SERVER['REQUEST_METHOD'];
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
        $locale = 'de';
        $selfUrl = '';
        $messages = [];
        $translator = $this->getSessionHandler()->getTranslator();
        if (empty($data) || !array_key_exists('messages', $data)) {
            $messages = $this->messages;
        }
        else {
            $messages = $data['messages'];
        }
        if (empty($data) || !array_key_exists('locale', $data)) {
            $locale = $this->getLang();
        }
        else {
            $locale = $data['locale'];
        }
        if (empty($data) || !array_key_exists('selfUrl', $data)) {
            $selfUrl = array_key_exists('PHP_SELF', $_SERVER) ? $_SERVER['PHP_SELF'] : '';
            if (array_key_exists('QUERY_STRING', $_SERVER)) {
                $selfUrl = $selfUrl . '?' . filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_UNSAFE_RAW);
            }
        }
        else {
            $selfUrl = $data['selfUrl'];
        }
        $this->getEngine()->addData([
            'i18n' => $translator,
            'locale' => $locale,
            'messages' => $messages,
            'selfUrl' => $selfUrl
        ]);
        if (!isset($data)) {
            $this->outputBody .= $this->getEngine()->render($templateName);
        }
        else {
            $this->outputBody .= $this->getEngine()->render($templateName, $data);
        }
    }

    /**
     * This will display the added messages on the rendered view page.
     * @param Message Message to be shown.
     */
    protected function addMessage(Message $message) {
        if (isset($message)) {
            array_push($this->messages, $message);
        }
    }
    
    /**
     * This will display the added messages on the rendered view page.
     * @param array An array consisting of \Ui\Message, the messages to be added.
     * @see AbstractController::addMessage()
     */
    protected function addMessages(array $messages) {
        if ($messages !== null && sizeof($messages) > 0) {
            $this->messages = array_merge($this->messages, $messages);
        }
    }

    /**
     * @param string $name Key of the parameter to retrieve.
     * @return string Value of the parameter, or null when there is no such parameter.
     */
    protected function getParam(string $name) {
        if (!array_key_exists($name, $this->getData())) {
            return null;
        }
        return $this->getData()[$name];
    }
    /**
     * @param string $name Key of the parameter to retrieve.
     * @return string Value of the parameter, or null when there is no such parameter.
     */
    protected function getQueryParam(string $name) {
        if (!array_key_exists($name, $this->get)) {
            return null;
        }
        return $this->get[$name];
    }
    
    
    protected function getParamInteger(string $name) {
        $val = $this->getParam($name);
        if ($val === null) {
            return null;
        }
        $res = filter_var($val, FILTER_VALIDATE_INT);
        if ($res === false) {
            return null;
        }
        return intval($val, 10);
    }
    
    protected function redirect(string $url) {
        header('Location: '.$url);
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
    
    public final function process($useSession = true) {
        $renderedError = false;
        try {
            if ($useSession) {
                $this->getSessionHandler()->initSession();
            }
            $this->processReq();
            echo $this->outputBody;
        } catch (\Throwable $e) {
            error_log('Failed to process request to ' . $_SERVER['PHP_SELF'] . ':' . $e);
            try {
                if ($this->getContext()->isEmInitialized() && $this->getEm()->isOpen()) {
                    $this->getEm()->rollback();
                }
            } catch (\Throwable $e2) {
                error_log('Failed to rollback transaction: ' . $e2);
            }            
            $this->renderUnhandledError($e);
            $renderedError = true;
        } finally {
            try {
                if ($this->getContext()->isEmInitialized() && $this->getEm()->isOpen()) {
                    $this->getEm()->flush();
                    $this->getContext()->closeEm();
                }
            } catch (\Throwable $e) {
                error_log('Failed to close entity manager: ' . $e);
                $suf = " in " . $e->getFile() . " on line " . $e->getLine();
                if (!$renderedError) {
                    $this->renderUnhandledError($e);
                }
            }            
        }
    }
    private final function renderUnhandledError($e) {
        $suf = " in " . $e->getFile() . " on line " . $e->getLine();
        try {
            $isProd = !($this->getContext()->getMode() === Context::$MODE_DEVELOPMENT || $this->getContext()->getMode() === Context::$MODE_TESTING);
        }
        catch (Throwable $t) {
            $isProd = true;
        }
        $message = $isProd ? get_class($e) : $e->getMessage() . $suf;
        $detail = $isProd ? "This unhandled error was most likely caused by some bug in the application. You may want to contact the site admin." : $e->getTraceAsString();
        $out;
        try {
            $out = $this->getContext()->getEngine()->render("unhandledError", ['message' => $message, 'detail' => $detail]);
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
}