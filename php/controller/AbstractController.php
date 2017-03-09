<?php

namespace Controller;

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

    public function __construct() {
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
