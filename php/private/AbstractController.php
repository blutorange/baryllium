<?php

namespace Controller;

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

    public function __construct() {
        $this->messages = [];
        $this->context = $GLOBALS['context'];
        $this->sessionHandler = new \PortalSessionHandler();
        session_set_save_handler($this->sessionHandler, true);
    }

    public function getSessionHandler(): \PortalSessionHandler {
        return $this->sessionHandler;
    }

    public function getContext(): \Context {
        return $this->context;
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
     * A message for a template within the portal context. Automatically adds
     * the messages to be shown. To override with your own messages, simple
     * add an entry for the key <pre>messages</pre> in the data array.
     * @param string Name of the template to render.
     * @param array Additional data to be passed to the template.
     */
    protected function renderPortal(string $templateName, array $data = NULL) {
        if (!array_key_exists('messages', $data)) {
            $this->getEngine()->addData(['messages' => $this->messages], 'portal');
        }
        else {
            $this->getEngine()->addData(['messages' => $data['messages']], 'portal');
        }
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
            error_log($e);
            echo $this->getContext()->getEngine()->render("unhandledError", ['message' => $e->getMessage(), 'detail' => $e->getTraceAsString()]);
        } finally {
            $this->getContext()->closeEm();
        }
    }
}
