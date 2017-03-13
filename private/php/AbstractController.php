<?php

namespace Controller;

use \Ui\PlaceholderTranslator;
use \Ui\Message;
use \League\Plates\Engine;
use \Doctrine\ORM\EntityManager;

/**
 * Description of AbstractController
 *
 * @author madgaksha
 */
abstract class AbstractController {

    protected $context;
    protected $data;
    protected $sessionHandler;
    
    /** @var array Warning or info messages to be displayed. */
    protected $messages;

    public function __construct(Context $context = null) {
        $this->messages = [];
        $this->context = $context ?? $GLOBALS['context'];
        $this->sessionHandler = new \PortalSessionHandler($this->context);
        session_set_save_handler($this->sessionHandler, true);        
    }

    public function getSessionHandler(): \PortalSessionHandler {
        return $this->sessionHandler;
    }
    
    public function getTranslator(): PlaceholderTranslator {
        return $this->getSessionHandler()->getTranslator();
    }
    
    public function getLang() : string {
        return $this->getSessionHandler()->getLang();
    }

    public function getContext(): \Context {
        return $this->context;
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
    protected function renderTemplate(string $templateName, array $data = NULL) {
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
            echo $this->getEngine()->render($templateName);
        }
        else {
            echo $this->getEngine()->render($templateName, $data);
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
     * @param array An array with messages to be added.
     * @see AbstractController::addMessage()
     */
    protected function addMessages(array $messages) {
        if (isset($messages)) {
            $this->messages = array_merge($this->messages, $messages);
        }
    }

    protected function getParam(string $name) {
        return $this->getData()[$name];
    }

    public final function process() {
        try {
            $this->processReq();
        } catch (\Throwable $e) {
            error_log('Failed to process request to ' . $_SERVER['PHP_SELF'] . ':' . $e);
            echo $this->getContext()->getEngine()->render("unhandledError", ['message' => $e->getMessage(), 'detail' => $e->getTraceAsString()]);
        } finally {
            try {
                $this->getContext()->getEm()->flush();
                $this->getContext()->closeEm();
            } catch (\Throwable $e) {
                error_log('Failed to close entity manager: ' . $e);
                echo $this->getContext()->getEngine()->render("unhandledError", ['message' => $e->getMessage(), 'detail' => $e->getTraceAsString()]);
            }            
        }
    }
}
