<?php

namespace Controller;

/**
 * Description of AbstractController
 *
 * @author madgaksha
 */
abstract class AbstractController {
    protected $engine;
    protected $em;
    protected $data;
    protected $session;
    public function __construct() {
        $this->engine = $GLOBALS['context']->getEngine();
        $this->em = $GLOBALS['context']->getEm();
        $this->session = new \Session();
    }
    public function getSession() : \Session {
        return $this->session;
    }
    public function getEngine() : \League\Plates\Engine {
        return $this->engine;
    }
    
    public function getEm() : \Doctrine\ORM\EntityManager {
        return $this->em;
    }
    
    public function getData() : array {
        return $this->data;
    }

    public abstract function doGet();
    public abstract function doPost();
    public final function process() {
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
}
