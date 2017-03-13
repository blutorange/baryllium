<?php

namespace UnitTest;

use Gettext\Translator;
use \Doctrine\ORM\EntityManager;

/**
 * Base class for all tests testing an entity class for the database.
 */
abstract class AbstractEntityTest extends \AbstractDbTest {

    private $sessionHandler;
    private $context;

    public function setUp() {
        error_log("Session is " . session_status());
        $this->context = new \Context();
        $this->sessionHandler = new \PortalSessionHandler($this->context);
    }
    
    protected function getContext() : \Context {
        return $this->context;
    }
    
    protected function getSessionHandler() : \PortalSessionHandler {
        return $this->sessionHandler;
    }
    
    protected function getTranslator() : Translator {
        return $this->getSessionHandler()->getTranslator();
    }

    protected function getEm(): EntityManager {
        return $this->getContext()->getEm();
    }

    protected static function assertValidate($entity, int $numberOfErrs) {
        $errMsg = array();
        $res = $entity->validate($errMsg, $this->getTranslator());
        self::assertEquals(sizeof($errMsg), $numberOfErrs);
        self::assertTrue($res && sizeof($errMsg) === 0 || !$res && sizeof($errMsg) > 0);
    }
}